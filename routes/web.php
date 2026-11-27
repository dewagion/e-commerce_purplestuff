<?php

use App\Http\Controllers\Admin\AdminArtistController;
use App\Http\Controllers\Admin\AdminBannerController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminPushSubscriptionController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FirebaseServiceWorkerController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

// Route lama tetap dipertahankan agar browser yang pernah mendaftarkan service worker root tidak error 404.
Route::get('/firebase-messaging-sw.js', [FirebaseServiceWorkerController::class, 'legacy'])
    ->name('firebase.messaging.sw');

// Route baru untuk Firebase admin. Scope dibatasi ke /admin/ supaya tidak mengontrol halaman pelanggan.
Route::get('/admin/firebase-messaging-sw.js', [FirebaseServiceWorkerController::class, 'admin'])
    ->name('admin.firebase.messaging.sw');

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::redirect('/dashboard', '/')->name('dashboard.view');
Route::get('/products', [ProductsController::class, 'index'])->name('products.index');
Route::get('/artist/{artist}', [ArtistController::class, 'show'])->name('artist.show');

Route::get('/cart/add', [CartController::class, 'addRedirect']);
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
Route::post('/cart/qty', [CartController::class, 'qty'])->name('cart.qty');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::get('/shipping', [PageController::class, 'shipping'])->name('pages.shipping');
Route::get('/returns', [PageController::class, 'returns'])->name('pages.returns');
Route::get('/faq', [PageController::class, 'faq'])->name('pages.faq');
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
Route::post('/admin/logout', [LoginController::class, 'destroyAdmin'])
    ->middleware('auth:admin')
    ->name('admin.logout');

Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::get('/forgot-password', [PasswordController::class, 'request'])->name('password.request');
Route::post('/forgot-password', [PasswordController::class, 'email'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordController::class, 'reset'])->name('password.reset');
Route::post('/reset-password', [PasswordController::class, 'update'])->name('password.update');

Route::middleware('auth')->group(function () {
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::post('/checkout/selected', [CheckoutController::class, 'selected'])->name('checkout.selected');
    Route::get('/checkout/selected', [CheckoutController::class, 'show'])->name('checkout.selected.show');
    Route::post('/checkout/shipping-options', [CheckoutController::class, 'shippingOptions'])->name('checkout.shipping-options');
    Route::post('/checkout/confirm', [CheckoutController::class, 'confirm'])->name('checkout.confirm');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}/payment', [CheckoutController::class, 'payment'])->name('orders.payment');
    Route::get('/orders/{order}/payment-status', [CheckoutController::class, 'paymentStatus'])->name('orders.payment-status');
    Route::get('/orders/{order}/payment-proof', [CheckoutController::class, 'paymentProof'])->name('orders.payment-proof');
    Route::get('/orders/{order}/shipping-receipt', [CheckoutController::class, 'shippingReceipt'])->name('orders.shipping-receipt');
    Route::post('/orders/{order}/pay', [CheckoutController::class, 'pay'])->name('orders.pay');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{product}/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
});

Route::middleware(['auth:admin', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::patch('/profile', [ProfileController::class, 'updateAdmin'])->name('profile.update');
        Route::get('/recap/pdf', [AdminDashboardController::class, 'exportRecapPdf'])->name('recap.pdf');

        Route::get('/notifications/feed', [AdminNotificationController::class, 'feed'])->name('notifications.feed');
        Route::post('/notifications/read-all', [AdminNotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::delete('/notifications/delete-all', [AdminNotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
        Route::delete('/notifications/{notification}', [AdminNotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::post('/notifications/{notification}/read', [AdminNotificationController::class, 'read'])->name('notifications.read');

        Route::post('/push/subscribe', [AdminPushSubscriptionController::class, 'subscribe'])->name('push.subscribe');
        Route::post('/push/unsubscribe', [AdminPushSubscriptionController::class, 'unsubscribe'])->name('push.unsubscribe');

        Route::resource('products', AdminProductController::class)->except('show');

        Route::get('/artists', [AdminArtistController::class, 'index'])->name('artists.index');
        Route::post('/artists', [AdminArtistController::class, 'store'])->name('artists.store');
        Route::get('/artists/{artist}/edit', [AdminArtistController::class, 'edit'])->name('artists.edit');
        Route::put('/artists/{artist}', [AdminArtistController::class, 'update'])->name('artists.update');
        Route::delete('/artists/{artist}', [AdminArtistController::class, 'destroy'])->name('artists.destroy');

        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [AdminCategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/payment-proof', [AdminOrderController::class, 'paymentProof'])->name('orders.payment-proof');
        Route::get('/orders/{order}/shipping-receipt', [AdminOrderController::class, 'shippingReceipt'])->name('orders.shipping-receipt');
        Route::patch('/orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');

        Route::get('/banners', [AdminBannerController::class, 'index'])->name('banners.index');
        Route::post('/banners', [AdminBannerController::class, 'store'])->name('banners.store');
        Route::get('/banners/{banner}/edit', [AdminBannerController::class, 'edit'])->name('banners.edit');
        Route::put('/banners/{banner}', [AdminBannerController::class, 'update'])->name('banners.update');
        Route::delete('/banners/{banner}', [AdminBannerController::class, 'destroy'])->name('banners.destroy');
    });
