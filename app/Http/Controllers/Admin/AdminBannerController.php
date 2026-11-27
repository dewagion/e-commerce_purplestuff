<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminBannerController extends Controller
{
    public function index()
    {
        return view('admin.banners.index', $this->bannerViewData(new Banner(), false));
    }

    public function store(Request $request)
    {
        if ($request->input('dashboard_section') === 'category_grid') {
            $data = $this->validateQuickCategoryGrid($request);
            $createdCount = $this->storeQuickCategoryGridBanners($request, $data);

            return redirect()
                ->route('admin.banners.index')
                ->with('success', $createdCount . ' kartu promo berhasil ditambahkan ke dashboard.');
        }

        $data = $this->validateBanner($request);
        $targetProductIds = $this->normalizeProductIds($data['target_product_ids'] ?? []);
        $target = $this->buildTargetData($data, $targetProductIds);
        $imagePath = $this->storeUploadedImage($request->file('image_file'));

        Banner::create([
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?: null,
            'badge_text' => $data['badge_text'] ?: null,
            'image' => $imagePath,
            'dashboard_section' => $data['dashboard_section'] ?? 'hero',
            'cta_text' => $data['cta_text'] ?: 'Shop Now',
            'cta_link' => $target['cta_link'],
            'target_type' => $target['target_type'],
            'target_category_slug' => $target['target_category_slug'],
            'target_artist_slug' => $target['target_artist_slug'],
            'target_product_ids' => $targetProductIds,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil ditambahkan dan sudah terhubung ke dashboard.');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.index', $this->bannerViewData($banner, true));
    }

    public function update(Request $request, Banner $banner)
    {
        $selectedSection = $request->input('dashboard_section', $banner->resolved_dashboard_section ?? 'hero');

        if ($selectedSection === 'category_grid') {
            return $this->updateCategoryGridBanner($request, $banner);
        }

        $data = $this->validateBanner($request, true);
        $targetProductIds = $this->normalizeProductIds($data['target_product_ids'] ?? []);
        $target = $this->buildTargetData($data, $targetProductIds);

        $imagePath = $banner->image;
        $newUpload = $this->storeUploadedImage($request->file('image_file'));

        if ($newUpload) {
            $imagePath = $newUpload;
        }

        $banner->update([
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?: null,
            'badge_text' => $data['badge_text'] ?: null,
            'image' => $imagePath,
            'dashboard_section' => $data['dashboard_section'] ?? 'hero',
            'cta_text' => $data['cta_text'] ?: 'Shop Now',
            'cta_link' => $target['cta_link'],
            'target_type' => $target['target_type'],
            'target_category_slug' => $target['target_category_slug'],
            'target_artist_slug' => $target['target_artist_slug'],
            'target_product_ids' => $targetProductIds,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil diperbarui.');
    }

    protected function updateCategoryGridBanner(Request $request, Banner $banner)
    {
        $data = $this->validateCategoryGridBanner($request, true);
        $target = $this->buildTargetData([
            'target_type' => 'category',
            'target_category_slug' => $data['target_category_slug'],
        ], []);

        $imagePath = $banner->image;
        $newUpload = $this->storeUploadedImage($request->file('image_file'));

        if ($newUpload) {
            $imagePath = $newUpload;
        }

        $banner->update([
            'title' => $data['title'],
            'subtitle' => null,
            'badge_text' => null,
            'image' => $imagePath,
            'dashboard_section' => 'category_grid',
            'cta_text' => $data['cta_text'] ?: 'Shop Now',
            'cta_link' => $target['cta_link'],
            'target_type' => 'category',
            'target_category_slug' => $target['target_category_slug'],
            'target_artist_slug' => null,
            'target_product_ids' => [],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Kartu banner 3 kolom berhasil diperbarui.');
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil dihapus.');
    }

    protected function bannerViewData(Banner $banner, bool $isEdit): array
    {
        return [
            'banners' => Banner::orderBy('sort_order')->orderByDesc('id')->paginate(10),
            'banner' => $banner,
            'isEdit' => $isEdit,
            'products' => Product::with(['artist', 'category'])->orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'artists' => Artist::orderBy('name')->get(),
            'dashboardSectionOptions' => Banner::dashboardSectionOptions(),
        ];
    }

    protected function validateBanner(Request $request, bool $isEdit = false): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'badge_text' => ['nullable', 'string', 'max:100'],
            'dashboard_section' => ['nullable', Rule::in(['hero', 'category_grid', 'single_feature'])],
            'cta_text' => ['nullable', 'string', 'max:50'],
            'cta_link' => ['nullable', 'string', 'max:255'],
            'target_type' => ['nullable', Rule::in(['all', 'category', 'artist', 'products', 'custom', 'manual'])],
            'target_category_slug' => ['nullable', 'string', 'max:255'],
            'target_artist_slug' => ['nullable', 'string', 'max:255'],
            'target_product_ids' => ['nullable', 'array'],
            'target_product_ids.*' => ['string', 'exists:products,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'image_file' => [$isEdit ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
    }

    protected function validateCategoryGridBanner(Request $request, bool $isEdit = false): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'dashboard_section' => ['required', Rule::in(['category_grid'])],
            'target_category_slug' => ['required', 'string', 'exists:categories,slug'],
            'cta_text' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'image_file' => [$isEdit ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
    }

    protected function validateQuickCategoryGrid(Request $request): array
    {
        $data = $request->validate([
            'dashboard_section' => ['required', Rule::in(['category_grid'])],
            'cta_text' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'quick_cards' => ['required', 'array', 'size:3'],
            'quick_cards.*.title' => ['nullable', 'string', 'max:150'],
            'quick_cards.*.target_category_slug' => ['nullable', 'string', 'exists:categories,slug'],
            'quick_cards.*.image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $cards = [];
        $errors = [];

        for ($index = 0; $index < 3; $index++) {
            $card = $data['quick_cards'][$index] ?? [];
            $title = trim((string) ($card['title'] ?? ''));
            $categorySlug = trim((string) ($card['target_category_slug'] ?? ''));
            $imageFile = $request->file("quick_cards.$index.image_file");

            if ($title === '') {
                $errors["quick_cards.$index.title"] = 'Judul kartu ' . ($index + 1) . ' wajib diisi.';
            }

            if ($categorySlug === '') {
                $errors["quick_cards.$index.target_category_slug"] = 'Kategori kartu ' . ($index + 1) . ' wajib dipilih.';
            }

            if (!$imageFile) {
                $errors["quick_cards.$index.image_file"] = 'Gambar kartu ' . ($index + 1) . ' wajib dipilih.';
            }

            if ($title !== '' && $categorySlug !== '' && $imageFile) {
                $cards[] = [
                    'slot_index' => $index,
                    'title' => $title,
                    'target_category_slug' => $categorySlug,
                ];
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        $data['quick_cards'] = $cards;

        return $data;
    }

    protected function storeQuickCategoryGridBanners(Request $request, array $data): int
    {
        $sortOrder = (int) ($data['sort_order'] ?? 0);
        $ctaText = trim((string) ($data['cta_text'] ?? '')) ?: 'Shop Now';
        $isActive = (bool) ($data['is_active'] ?? false);
        $createdCount = 0;

        foreach ($data['quick_cards'] as $position => $card) {
            $imagePath = $this->storeUploadedImage($request->file('quick_cards.' . $card['slot_index'] . '.image_file'));
            $target = $this->buildTargetData([
                'target_type' => 'category',
                'target_category_slug' => $card['target_category_slug'],
            ], []);

            Banner::create([
                'title' => $card['title'],
                'subtitle' => null,
                'badge_text' => null,
                'image' => $imagePath,
                'dashboard_section' => 'category_grid',
                'cta_text' => $ctaText,
                'cta_link' => $target['cta_link'],
                'target_type' => $target['target_type'],
                'target_category_slug' => $target['target_category_slug'],
                'target_artist_slug' => $target['target_artist_slug'],
                'target_product_ids' => [],
                'sort_order' => $sortOrder + $position,
                'is_active' => $isActive,
            ]);

            $createdCount++;
        }

        return $createdCount;
    }

    protected function buildTargetData(array $data, array $targetProductIds): array
    {
        $targetType = $data['target_type'] ?? 'products';

        if ($targetType === 'manual') {
            $targetType = 'products';
        }

        if (($targetType === 'all' || $targetType === '') && !empty($targetProductIds)) {
            $targetType = 'products';
        }

        $targetCategorySlug = null;
        $targetArtistSlug = null;
        $ctaLink = route('products.index');

        if ($targetType === 'category') {
            $targetCategorySlug = trim((string) ($data['target_category_slug'] ?? ''));
            $ctaLink = $targetCategorySlug !== ''
                ? route('products.index', ['category' => $targetCategorySlug])
                : route('products.index');
        } elseif ($targetType === 'artist') {
            $targetArtistSlug = trim((string) ($data['target_artist_slug'] ?? ''));
            $ctaLink = $targetArtistSlug !== ''
                ? route('artist.show', ['artist' => $targetArtistSlug])
                : route('products.index');
        } elseif ($targetType === 'custom') {
            $ctaLink = $this->normalizeCustomLink($data['cta_link'] ?? null);
        } elseif ($targetType === 'products') {
            $ctaLink = !empty($targetProductIds)
                ? route('products.index', ['ids' => implode(',', $targetProductIds)])
                : route('products.index');
        } else {
            $targetType = 'all';
            $ctaLink = route('products.index');
        }

        return [
            'target_type' => $targetType,
            'target_category_slug' => $targetCategorySlug,
            'target_artist_slug' => $targetArtistSlug,
            'cta_link' => $ctaLink,
        ];
    }

    protected function normalizeProductIds(array $ids): array
    {
        return collect($ids)
            ->map(fn ($id) => trim((string) $id))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function normalizeCustomLink(?string $link): string
    {
        $link = trim((string) $link);

        if ($link === '') {
            return route('products.index');
        }

        if (Str::startsWith($link, ['http://', 'https://', '/'])) {
            return $link;
        }

        return '/' . ltrim($link, '/');
    }

    protected function storeUploadedImage(?UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }

        $directory = public_path('images/banners');

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return '/images/banners/' . $filename;
    }
}
