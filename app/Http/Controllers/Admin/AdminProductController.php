<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductOptionStock;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminProductController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $products = Product::with(['artist', 'category', 'optionStocks'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'search'));
    }

    public function create()
    {
        return view('admin.products.create', [
            'product' => new Product(),
            'artists' => Artist::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'imagesText' => '',
            'variantsText' => '',
            'sizesText' => '',
            'optionStocksText' => '',
            'optionStockRows' => [],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateProduct($request);

        DB::transaction(function () use ($request, $data) {
            $primaryUploaded = $this->storeUploadedImage($request->file('primary_image_file'));
            $galleryUploaded = $this->storeUploadedImages($request->file('gallery_image_files', []));

            $images = [];

            if ($primaryUploaded) {
                $images[] = $primaryUploaded;
            }

            foreach ($galleryUploaded as $path) {
                if (!in_array($path, $images, true)) {
                    $images[] = $path;
                }
            }

            $primaryImage = $primaryUploaded ?: ($images[0] ?? null);
            $variants = $this->lines($data['variants_text'] ?? '');
            $sizes = $this->lines($data['sizes_text'] ?? '');
            $optionPricingActive = $request->boolean('option_pricing_active');
            $optionMediaActive = $request->boolean('option_media_active');
            $optionStocks = $this->parseOptionStocks($request, $variants, $sizes, (int) $data['stock'], $optionPricingActive, $optionMediaActive);
            $totalStock = $this->totalOptionStock($optionStocks);

            $product = Product::create([
                'id' => $this->generateProductId($data['name']),
                'artist_id' => $data['artist_id'],
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'slug' => $this->generateUniqueSlug($data['name']),
                'sku' => $data['sku'] ?: null,
                'type' => $data['type'],
                'price' => $data['price'],
                'discount_price' => $this->discountPricePayload($request, $data),
                'discount_active' => $this->discountActivePayload($request, $data),
                'product_status' => $data['product_status'] ?? 'ready',
                'option_pricing_active' => $optionPricingActive,
                'option_media_active' => $optionMediaActive,
                'weight' => $data['weight'],
                'stock' => $totalStock,
                'desc' => $data['desc'] ?: null,
                'shipping' => $data['shipping'] ?: null,
                // Stok menjadi sumber utama status tersedia agar admin tidak bingung.
                'in_stock' => $this->isProductAvailableFromStock($totalStock),
                'primary_image' => $primaryImage,
            ]);

            $this->syncProductMeta($product, $images, $variants, $sizes);
            $this->syncOptionStocks($product, $optionStocks, $totalStock);
        });

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $product->load(['images', 'variants', 'sizes', 'optionStocks']);

        return view('admin.products.edit', [
            'product' => $product,
            'artists' => Artist::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'imagesText' => $product->images->pluck('path')->implode("\n"),
            'variantsText' => $product->variants->pluck('name')->implode("\n"),
            'sizesText' => $product->sizes->pluck('size')->implode("\n"),
            'optionStocksText' => $this->optionStocksText($product),
            'optionStockRows' => $this->optionStockRows($product),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateProduct($request);

        DB::transaction(function () use ($request, $data, $product) {
            $product->loadMissing(['images', 'variants', 'sizes', 'optionStocks']);

            $currentImages = $product->images
                ->pluck('path')
                ->filter()
                ->values()
                ->all();
            $currentPrimaryImage = $product->primary_image;
            $currentGalleryImages = array_values(array_filter(
                $currentImages,
                fn ($path) => $path !== $currentPrimaryImage
            ));
            $removedImagePaths = [];

            $primaryUploaded = $this->storeUploadedImage($request->file('primary_image_file'));
            $galleryUploaded = $this->storeUploadedImages($request->file('gallery_image_files', []));

            if ($primaryUploaded && $currentPrimaryImage && $currentPrimaryImage !== $primaryUploaded) {
                $removedImagePaths[] = $currentPrimaryImage;
            }

            $removeGalleryImages = collect($request->input('remove_gallery_images', []))
                ->map(fn ($path) => trim((string) $path))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($request->boolean('replace_gallery')) {
                $removedImagePaths = array_merge($removedImagePaths, $currentGalleryImages);
                $currentGalleryImages = [];
            } elseif (!empty($removeGalleryImages)) {
                $currentGalleryImages = array_values(array_filter($currentGalleryImages, function ($path) use ($removeGalleryImages, &$removedImagePaths) {
                    if (in_array($path, $removeGalleryImages, true)) {
                        $removedImagePaths[] = $path;
                        return false;
                    }

                    return true;
                }));
            }

            foreach ($galleryUploaded as $path) {
                if (!in_array($path, $currentGalleryImages, true)) {
                    $currentGalleryImages[] = $path;
                }
            }

            $primaryImage = $primaryUploaded
                ?: $currentPrimaryImage
                ?: ($currentGalleryImages[0] ?? null);

            $images = [];

            if ($primaryImage) {
                $images[] = $primaryImage;
            }

            foreach ($currentGalleryImages as $path) {
                if ($path && $path !== $primaryImage && !in_array($path, $images, true)) {
                    $images[] = $path;
                }
            }

            $images = array_values(array_unique(array_filter($images)));

            $variants = $this->lines($data['variants_text'] ?? '');
            $sizes = $this->lines($data['sizes_text'] ?? '');
            $optionPricingActive = $request->boolean('option_pricing_active');
            $optionMediaActive = $request->boolean('option_media_active');
            $oldOptionImagePaths = $product->optionStocks
                ->pluck('image_path')
                ->filter()
                ->values()
                ->all();
            $optionStocks = $this->parseOptionStocks($request, $variants, $sizes, (int) $data['stock'], $optionPricingActive, $optionMediaActive);
            $newOptionImagePaths = collect($optionStocks)
                ->pluck('image_path')
                ->filter()
                ->values()
                ->all();
            $removedImagePaths = array_merge($removedImagePaths, array_values(array_diff($oldOptionImagePaths, $newOptionImagePaths)));
            $totalStock = $this->totalOptionStock($optionStocks);

            $product->update([
                'artist_id' => $data['artist_id'],
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'slug' => $this->generateUniqueSlug($data['name'], $product->id),
                'sku' => $data['sku'] ?: null,
                'type' => $data['type'],
                'price' => $data['price'],
                'discount_price' => $this->discountPricePayload($request, $data),
                'discount_active' => $this->discountActivePayload($request, $data),
                'product_status' => $data['product_status'] ?? 'ready',
                'option_pricing_active' => $optionPricingActive,
                'option_media_active' => $optionMediaActive,
                'weight' => $data['weight'],
                'stock' => $totalStock,
                'desc' => $data['desc'] ?: null,
                'shipping' => $data['shipping'] ?: null,
                // Stok menjadi sumber utama status tersedia agar stok tidak tersimpan tetapi produk tetap dianggap habis.
                'in_stock' => $this->isProductAvailableFromStock($totalStock),
                'primary_image' => $primaryImage,
            ]);

            $this->syncProductMeta($product, $images, $variants, $sizes);
            $this->syncOptionStocks($product, $optionStocks, $totalStock);
            $this->deleteUnusedProductImageFilesAfterCommit($removedImagePaths);
        });

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
    }

    protected function validateProduct(Request $request): array
    {
        return $request->validate([
            'artist_id' => ['required', 'exists:artists,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'sku' => ['nullable', 'string', 'max:100'],
            'type' => ['required', Rule::in(['apparel', 'accessory'])],
            'price' => ['required', 'integer', 'min:0'],
            'discount_price' => ['nullable', 'integer', 'min:0'],
            'discount_active' => ['nullable', 'boolean'],
            'product_status' => ['required', Rule::in(['ready', 'pre_order', 'upcoming'])],
            'option_pricing_active' => ['nullable', 'boolean'],
            'option_media_active' => ['nullable', 'boolean'],
            'weight' => ['required', 'integer', 'min:1'],
            'stock' => ['required', 'integer', 'min:0', 'max:999999'],
            'desc' => ['nullable', 'string'],
            'shipping' => ['nullable', 'string', 'max:255'],

            'primary_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'gallery_image_files' => ['nullable', 'array'],
            'gallery_image_files.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'replace_gallery' => ['nullable', 'boolean'],
            'remove_gallery_images' => ['nullable', 'array'],
            'remove_gallery_images.*' => ['nullable', 'string', 'max:255'],

            'variants_text' => ['nullable', 'string'],
            'sizes_text' => ['nullable', 'string'],
            'option_stocks_text' => ['nullable', 'string'],
            'option_stock_rows' => ['nullable', 'array'],
            'option_stock_rows.*.variant' => ['nullable', 'string', 'max:150'],
            'option_stock_rows.*.size' => ['nullable', 'string', 'max:100'],
            'option_stock_rows.*.stock' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'option_stock_rows.*.price' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'option_stock_rows.*.discount_price' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'option_stock_rows.*.discount_active' => ['nullable', 'boolean'],
            'option_stock_rows.*.image_path' => ['nullable', 'string', 'max:255'],
            'option_stock_rows.*.image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'in_stock' => ['nullable', 'boolean'],
        ]);
    }


    protected function discountActivePayload(Request $request, array $data): bool
    {
        $price = max(0, (int) ($data['price'] ?? 0));
        $discountPrice = max(0, (int) ($data['discount_price'] ?? 0));

        return $request->boolean('discount_active') && $discountPrice > 0 && $discountPrice < $price;
    }

    protected function discountPricePayload(Request $request, array $data): ?int
    {
        if (!$this->discountActivePayload($request, $data)) {
            return null;
        }

        return max(0, (int) ($data['discount_price'] ?? 0));
    }

    protected function isProductAvailableFromStock(int $stock): bool
    {
        return $stock > 0;
    }

    protected function syncProductMeta(Product $product, array $images, array $variants, array $sizes): void
    {
        $product->images()->delete();
        foreach ($images as $index => $path) {
            $product->images()->create([
                'path' => $path,
                'sort_order' => $index,
            ]);
        }

        $product->variants()->delete();
        foreach ($variants as $index => $name) {
            $product->variants()->create([
                'name' => $name,
                'sort_order' => $index,
            ]);
        }

        $product->sizes()->delete();
        foreach ($sizes as $index => $size) {
            $product->sizes()->create([
                'size' => $size,
                'sort_order' => $index,
            ]);
        }
    }

    protected function syncOptionStocks(Product $product, array $optionStocks, int $totalStock): void
    {
        if (!$this->optionStockTableReady()) {
            return;
        }

        $product->optionStocks()->delete();

        if (empty($optionStocks)) {
            $product->optionStocks()->create([
                'variant' => '',
                'size' => '',
                'stock' => max(0, $totalStock),
                'price' => null,
                'discount_price' => null,
                'discount_active' => false,
                'image_path' => null,
            ]);
            return;
        }

        foreach ($optionStocks as $row) {
            $price = isset($row['price']) && (int) $row['price'] > 0 ? (int) $row['price'] : null;
            $discountActive = $price !== null
                && !empty($row['discount_active'])
                && isset($row['discount_price'])
                && (int) $row['discount_price'] > 0
                && (int) $row['discount_price'] < $price;

            $product->optionStocks()->create([
                'variant' => ProductOptionStock::normalizeOption($row['variant'] ?? null),
                'size' => ProductOptionStock::normalizeOption($row['size'] ?? null),
                'stock' => max(0, (int) ($row['stock'] ?? 0)),
                'price' => $price,
                'discount_price' => $discountActive ? (int) $row['discount_price'] : null,
                'discount_active' => $discountActive,
                'image_path' => $row['image_path'] ?? null,
            ]);
        }
    }

    protected function parseOptionStocks(Request $request, array $variants, array $sizes, int $fallbackStock, bool $optionPricingActive, bool $optionMediaActive): array
    {
        $rows = $this->parseOptionStockRows($request, $request->input('option_stock_rows', []), $optionPricingActive, $optionMediaActive);

        if (empty($rows)) {
            $rows = $this->parseOptionStocksText((string) $request->input('option_stocks_text', ''));
        }

        if (empty($rows)) {
            $rows = [[
                'variant' => '',
                'size' => '',
                'stock' => max(0, $fallbackStock),
                'price' => null,
                'discount_price' => null,
                'discount_active' => false,
                'image_path' => null,
            ]];
        }

        return $this->normalizeStockRowsForCurrentOptions($rows, $variants, $sizes);
    }

    protected function parseOptionStockRows(Request $request, array $rows, bool $optionPricingActive, bool $optionMediaActive): array
    {
        $parsed = [];

        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $variant = ProductOptionStock::normalizeOption($row['variant'] ?? null);
            $size = ProductOptionStock::normalizeOption($row['size'] ?? null);
            $stock = max(0, (int) ($row['stock'] ?? 0));
            $price = $optionPricingActive ? max(0, (int) ($row['price'] ?? 0)) : 0;
            $discountActive = false;
            $discountPrice = null;

            if ($optionPricingActive && $price > 0 && !empty($row['discount_active'])) {
                $candidateDiscount = max(0, (int) ($row['discount_price'] ?? 0));
                if ($candidateDiscount > 0 && $candidateDiscount < $price) {
                    $discountActive = true;
                    $discountPrice = $candidateDiscount;
                }
            }

            $imagePath = $optionMediaActive ? trim((string) ($row['image_path'] ?? '')) : '';
            $uploaded = $request->file('option_stock_rows.' . $index . '.image_file');
            if ($optionMediaActive && $uploaded instanceof UploadedFile) {
                $imagePath = $this->storeUploadedImage($uploaded) ?: $imagePath;
            }

            $key = $variant . '||' . $size;

            $parsed[$key] = [
                'variant' => $variant,
                'size' => $size,
                'stock' => $stock,
                'price' => $optionPricingActive && $price > 0 ? $price : null,
                'discount_price' => $discountPrice,
                'discount_active' => $discountActive,
                'image_path' => $optionMediaActive && $imagePath !== '' ? $imagePath : null,
            ];
        }

        return array_values($parsed);
    }

    protected function parseOptionStocksText(string $text): array
    {
        $rows = [];

        foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            $parts = array_map('trim', explode('|', $line));
            if (count($parts) < 3) {
                continue;
            }

            $stock = (int) preg_replace('/[^0-9]/', '', (string) array_pop($parts));
            $variant = ProductOptionStock::normalizeOption($parts[0] ?? null);
            $size = ProductOptionStock::normalizeOption($parts[1] ?? null);

            $key = $variant . '||' . $size;
            $rows[$key] = [
                'variant' => $variant,
                'size' => $size,
                'stock' => max(0, $stock),
                'price' => null,
                'discount_price' => null,
                'discount_active' => false,
                'image_path' => null,
            ];
        }

        return array_values($rows);
    }

    protected function normalizeStockRowsForCurrentOptions(array $rows, array $variants, array $sizes): array
    {
        $stockMap = [];

        foreach ($rows as $row) {
            $variant = ProductOptionStock::normalizeOption($row['variant'] ?? null);
            $size = ProductOptionStock::normalizeOption($row['size'] ?? null);
            $key = $variant . '||' . $size;
            $stockMap[$key] = [
                'stock' => max(0, (int) ($row['stock'] ?? 0)),
                'price' => isset($row['price']) && (int) $row['price'] > 0 ? (int) $row['price'] : null,
                'discount_price' => isset($row['discount_price']) && (int) $row['discount_price'] > 0 ? (int) $row['discount_price'] : null,
                'discount_active' => !empty($row['discount_active']),
                'image_path' => !empty($row['image_path']) ? (string) $row['image_path'] : null,
            ];
        }

        $variantList = count($variants) > 0 ? $variants : [''];
        $sizeList = count($sizes) > 0 ? $sizes : [''];
        $normalized = [];

        foreach ($variantList as $variant) {
            foreach ($sizeList as $size) {
                $variant = ProductOptionStock::normalizeOption($variant);
                $size = ProductOptionStock::normalizeOption($size);
                $key = $variant . '||' . $size;

                $stored = $stockMap[$key] ?? [];

                $normalized[$key] = [
                    'variant' => $variant,
                    'size' => $size,
                    'stock' => max(0, (int) ($stored['stock'] ?? 0)),
                    'price' => $stored['price'] ?? null,
                    'discount_price' => $stored['discount_price'] ?? null,
                    'discount_active' => !empty($stored['discount_active']),
                    'image_path' => $stored['image_path'] ?? null,
                ];
            }
        }

        return array_values($normalized);
    }

    protected function totalOptionStock(array $rows): int
    {
        return collect($rows)->sum(fn ($row) => max(0, (int) ($row['stock'] ?? 0)));
    }

    protected function optionStocksText(Product $product): string
    {
        return collect($this->optionStockRows($product))
            ->map(function (array $row) {
                return ($row['variant'] !== '' ? $row['variant'] : '-')
                    . ' | '
                    . ($row['size'] !== '' ? $row['size'] : '-')
                    . ' | '
                    . max(0, (int) $row['stock']);
            })
            ->implode("\n");
    }

    protected function optionStockRows(Product $product): array
    {
        if (!$this->optionStockTableReady()) {
            return [[
                'variant' => '',
                'size' => '',
                'stock' => max(0, (int) ($product->stock ?? 0)),
                'price' => null,
                'discount_price' => null,
                'discount_active' => false,
                'image_path' => null,
            ]];
        }

        $rows = $product->relationLoaded('optionStocks') ? $product->optionStocks : $product->optionStocks()->get();

        if ($rows->isEmpty()) {
            return [[
                'variant' => '',
                'size' => '',
                'stock' => max(0, (int) ($product->stock ?? 0)),
                'price' => null,
                'discount_price' => null,
                'discount_active' => false,
                'image_path' => null,
            ]];
        }

        return $rows
            ->map(function (ProductOptionStock $row) {
                return [
                    'variant' => ProductOptionStock::normalizeOption($row->variant),
                    'size' => ProductOptionStock::normalizeOption($row->size),
                    'stock' => max(0, (int) $row->stock),
                    'price' => $row->price !== null ? max(0, (int) $row->price) : null,
                    'discount_price' => $row->discount_price !== null ? max(0, (int) $row->discount_price) : null,
                    'discount_active' => (bool) $row->discount_active,
                    'image_path' => $row->image_path,
                ];
            })
            ->values()
            ->all();
    }

    protected function optionStockTableReady(): bool
    {
        try {
            return Schema::hasTable('product_option_stocks');
        } catch (\Throwable $exception) {
            return false;
        }
    }

    protected function lines(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();
    }

    protected function generateProductId(string $name): string
    {
        return 'prod-' . Str::slug(Str::limit($name, 30, ''), '-') . '-' . Str::lower(Str::random(5));
    }

    protected function generateUniqueSlug(string $name, ?string $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $baseSlug = $slug;
        $counter = 1;

        while (
            Product::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function deleteUnusedProductImageFilesAfterCommit(array $paths): void
    {
        $paths = collect($paths)
            ->map(fn ($path) => trim((string) $path))
            ->filter(fn ($path) => $this->isManagedProductImagePath($path))
            ->unique()
            ->values()
            ->all();

        if (empty($paths)) {
            return;
        }

        DB::afterCommit(function () use ($paths) {
            foreach ($paths as $path) {
                if ($this->productImagePathStillUsed($path)) {
                    continue;
                }

                $absolutePath = public_path(ltrim($path, '/'));

                if (is_file($absolutePath)) {
                    @unlink($absolutePath);
                }
            }
        });
    }

    protected function productImagePathStillUsed(string $path): bool
    {
        if (Product::where('primary_image', $path)->exists()) {
            return true;
        }

        if (ProductImage::where('path', $path)->exists()) {
            return true;
        }

        if ($this->optionStockTableReady() && ProductOptionStock::where('image_path', $path)->exists()) {
            return true;
        }

        return false;
    }

    protected function isManagedProductImagePath(string $path): bool
    {
        return Str::startsWith($path, '/images/products/');
    }

    protected function storeUploadedImage(?UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }

        $directory = public_path('images/products');

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return '/images/products/' . $filename;
    }

    protected function storeUploadedImages(array $files): array
    {
        $paths = [];
        $directory = public_path('images/products');

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
                $file->move($directory, $filename);
                $paths[] = '/images/products/' . $filename;
            }
        }

        return $paths;
    }
}
