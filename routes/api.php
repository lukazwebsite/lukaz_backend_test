<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BkashController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\NoticeController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ShopByController;
use App\Http\Controllers\Api\TeamMemberController;
use App\Http\Controllers\Api\GetDataController;
use App\Http\Controllers\Api\OutlateController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\VideoFeatureController;
use App\Http\Controllers\Api\SslCommerzPaymentController;
use App\Http\Controllers\Api\InternationalOrderController;


// Public Routes
Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);
Route::post('/forget_password', [UserController::class, 'forgetOtp']);
Route::post('/password_update', [UserController::class, 'otpUpdate']);

Route::get('/countries', [GetDataController::class, 'getCountries']);
Route::get('/countries/{id}/districts', [GetDataController::class, 'getDistrictsByCountry']);
Route::get('/districts/{id}', [GetDataController::class, 'getDistrictById']);
Route::get('/police_stations/{id}', [GetDataController::class, 'getThanaById']);


Route::get('/notices', [NoticeController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/featured', [CategoryController::class, 'featured']);
Route::get('/banners', [BannerController::class, 'index']);
Route::get('/video/feature', [VideoFeatureController::class, 'index']);
Route::get('/shop-by', [ShopByController::class, 'index']);
Route::get('/team-members', [TeamMemberController::class, 'index']);
Route::get('/brand', [GetDataController::class, 'getBrand']);

Route::post('get-discount', [OrderController::class, 'getDiscountForPromoCode']);
Route::post('/orders/place', [OrderController::class, 'placeOrder']);
Route::post('international/orders/place', [InternationalOrderController::class, 'store']);

// SSLCOMMERZ
Route::post('sslcommerz/payment', [SslCommerzPaymentController::class, 'sslCommerzPaymentRequest']);
Route::post('sslcommerz/success', [SslCommerzPaymentController::class, 'success']);
Route::post('sslcommerz/fail', [SslCommerzPaymentController::class, 'fail']);
Route::post('sslcommerz/cancel', [SslCommerzPaymentController::class, 'cancel']);
Route::post('sslcommerz/ipn', [SslCommerzPaymentController::class, 'ipn']);

// Bkash
Route::post('bkash/payment', [OrderController::class, 'bKashPaymentRequest']);
Route::get("bkash/execute", [BkashController::class, 'execute']);

// Product
Route::get('/products', [ProductController::class, 'index']);
Route::post('/products/search', [ProductController::class, 'search']);
Route::get('/products/filter', [ProductController::class, 'filter']);
Route::get('/products/{slug}', [ProductController::class, 'show']);


Route::get('customer/reviews', [ReviewController::class, 'index']);

//Contracts
Route::post('contracts', [ContractController::class, 'store']);

//Subscribe
Route::post('subscribe', [SubscriptionController::class, 'store']);

Route::get('categories/with/childs', [CategoryController::class, 'withChilds']);
Route::get('categories/{slug}/with/products', [CategoryController::class, 'withProducts']);

Route::get('brands/{slug}/with/products', [BrandController::class, 'withProducts']);
Route::get('brands', [BrandController::class, 'index']);

Route::get('outlates', [OutlateController::class, 'index']);

Route::post('order/traces', [OrderController::class, 'traces']);



// Protected Routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Product Review
    Route::post('review', [ReviewController::class, 'store']);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/dashboard', [OrderController::class, 'index']);
    Route::post('/auth/orders/place', [OrderController::class, 'placeOrder']);
    Route::get('/order/{orderNo}', [OrderController::class, 'show']);

    Route::post('orders/tracking', [OrderController::class, 'orderTracking']);
    Route::get('orders/tracking', [OrderController::class, 'orderTracking']);

    Route::get('/account/details', [OrderController::class, 'accountDetails']);
});
