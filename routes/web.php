<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrassShowroomController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/search', [ShopController::class, 'search'])->name('shop.search');
Route::get('/search-items', [ShopController::class, 'searchPage'])->name('shop.search-items');
Route::get('/product-category/{slug}', [ShopController::class, 'category'])->name('shop.category');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove/{key}', [CartController::class, 'remove'])->name('cart.remove');

Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
Route::post('/wishlist/remove/{productId}', [WishlistController::class, 'remove'])->name('wishlist.remove');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/order-received/{order}', [CheckoutController::class, 'received'])->name('checkout.received');

Route::get('/about-us', [PageController::class, 'about'])->name('pages.about');
Route::get('/contact-us', [PageController::class, 'contact'])->name('pages.contact');
Route::post('/contact-us', [PageController::class, 'contactSubmit'])->name('pages.contact.submit');
Route::get('/news-articles', [PageController::class, 'news'])->name('pages.news');
Route::get('/news-articles/{slug}', [PageController::class, 'newsShow'])->name('pages.news.show');

// Standalone 3D showroom (no site header/footer — full-viewport experience
// with its own topbar). Published config is managed via the authenticated
// MCP layer in routes/ai.php; this route only ever renders the public view.
Route::get('/brass', [BrassShowroomController::class, 'show'])->name('brass-showroom.show');
Route::get('/brass/preview', [BrassShowroomController::class, 'preview'])
    ->middleware(['signed', 'throttle:30,1'])
    ->name('brass-showroom.preview');

// Serves the active (or a staged) frontend JS/CSS release; defaults to the
// bundled public/vendor files until the owner activates a release via MCP.
Route::get('/brass/assets/{release}/{asset}', [BrassShowroomController::class, 'frontendAsset'])
    ->where(['release' => 'bundled|[0-9a-fA-F-]{36}', 'asset' => 'javascript|stylesheet'])
    ->middleware('throttle:120,1')
    ->name('brass-showroom.frontend-asset');

Route::get('/brass/frontend-preview/{release}', [BrassShowroomController::class, 'frontendPreview'])
    ->whereUuid('release')
    ->middleware(['signed', 'throttle:30,1'])
    ->name('brass-showroom.frontend-preview');

Route::get('/brass/frontend-preview/{release}/assets/{asset}', [BrassShowroomController::class, 'frontendPreviewAsset'])
    ->whereUuid('release')
    ->whereIn('asset', ['javascript', 'stylesheet'])
    ->middleware(['signed', 'throttle:120,1'])
    ->name('brass-showroom.frontend-preview-asset');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth.customer')->prefix('my-account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
    Route::get('/orders/{id}', [AccountController::class, 'orderShow'])->name('orders.show');
    Route::get('/address', [AccountController::class, 'editAddress'])->name('address');
    Route::post('/address', [AccountController::class, 'updateAddress'])->name('address.update');
});
