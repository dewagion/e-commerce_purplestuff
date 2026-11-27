<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\OrderPaymentExpiryService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->shareStorefrontCategories();
        $this->expirePendingOrdersOnSafeRequest();
    }

    protected function shareStorefrontCategories(): void
    {
        $categories = [[
            'slug' => 'all',
            'name' => 'All Products',
            'icon' => 'bag',
            'bg' => 'bg-purple-100',
        ]];

        try {
            if (Schema::hasTable('categories')) {
                $dbCategories = Category::query()
                    ->orderBy('name')
                    ->get(['name', 'slug', 'icon', 'bg'])
                    ->map(fn (Category $category) => [
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'icon' => $category->icon,
                        'bg' => $category->bg,
                    ])
                    ->values()
                    ->all();

                $categories = array_merge($categories, $dbCategories);
            }
        } catch (Throwable $exception) {
            // Database may not be ready during early setup.
        }

        View::share('categories', $categories);
    }

    protected function expirePendingOrdersOnSafeRequest(): void
    {
        try {
            if ($this->app->runningInConsole()) {
                return;
            }

            $request = request();

            if (!$request || !$request->isMethod('GET')) {
                return;
            }

            app(OrderPaymentExpiryService::class)->expireDuePendingOrders(25);
        } catch (Throwable $exception) {
            // Keep the website usable even if the database/mail configuration is not ready.
        }
    }
}
