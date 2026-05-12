<?php


use App\Http\Controllers\AttributeController;
use App\Http\Controllers\AttributeValueController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GithubController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\ProductVariantValueController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CouponController;
use Illuminate\Support\Facades\Route;

Route::post('verify', [AuthController::class, 'sendVerifyEmailOtp']);
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::get('github', [GithubController::class, 'redirect']);
Route::get('github/callback', [GithubController::class, 'callback']);

Route::get('google', [GoogleController::class, 'redirect']);
Route::get('google/callback', [GoogleController::class, 'callback']);

Route::get('facebook', [FacebookController::class, 'redirect']);
Route::get('facebook/callback', [FacebookController::class, 'callback']);

Route::post('forgot', [AuthController::class, 'sendPasswordResetOtp']);
Route::post('verify-otp', [AuthController::class, 'verifyPasswordResetOtp']);
Route::post('reset', [AuthController::class, 'resetPassword']);


Route::middleware('auth:sanctum')->group(function () {
  Route::post('logout', [AuthController::class, 'logout']);
  Route::get('get-user', [AuthController::class, 'getUser']);

  Route::post('change-password', [AuthController::class, 'changePassword']);

  Route::middleware('abilities:Admin')->group(function () {

    Route::controller(UserController::class)->group(function () {
      Route::get('users', 'index');
      Route::post('users', 'store');
      Route::get('users/{user}', 'show');
      Route::post('users/{user}', 'update');
      Route::delete('users/{user}', 'destroy');
    });

    Route::controller(AttributeController::class)->group(function () {
      Route::get('attributes', 'index');
      Route::post('attributes', 'store');
      Route::get('attributes/{attribute}', 'show');
      Route::post('attributes/{attribute}', 'update');
      Route::delete('attributes/{attribute}', 'destroy');
    });

    Route::controller(AttributeValueController::class)->group(function () {
      Route::get('attribute-values', 'index');
      Route::post('attribute-values', 'store');
      Route::get('attribute-values/{attributeValue}', 'show');
      Route::post('attribute-values/{attributeValue}', 'update');
      Route::delete('attribute-values/{attributeValue}', 'destroy');
    });

    Route::controller(CategoryController::class)->group(function () {
      Route::get('categories', 'index');
      Route::post('categories', 'store');
      Route::get('categories/{id}', 'show');
      Route::post('categories/{id}', 'update');
      Route::delete('categories/{id}', 'destroy');
    });

    Route::controller(ProductController::class)->group(function () {
      Route::get('products', 'index');
      Route::post('products', 'store');
      Route::get('products/{product}', 'show');
      Route::post('products/{product}', 'update');
      Route::delete('products/{product}', 'destroy');
    });

    Route::controller(ProductImageController::class)->group(function () {
      Route::get('product-images', 'index');
      Route::post('product-images', 'store');
      Route::get('product-images/{productImage}', 'show');
      Route::post('product-images/{productImage}', 'update');
      Route::delete('product-images/{productImage}', 'destroy');
    });

    Route::controller(ProductVariantController::class)->group(function () {
      Route::get('product-variants', 'index');
      Route::post('product-variants', 'store');
      Route::get('product-variants/{productVariant}', 'show');
      Route::post('product-variants/{productVariant}', 'update');
      Route::delete('product-variants/{productVariant}', 'destroy');
    });

    Route::controller(ProductVariantValueController::class)->group(function () {
      Route::get('product-variant-values', 'index');
      Route::post('product-variant-values', 'store');
      Route::get('product-variant-values/{productVariantValue}', 'show');
      Route::put('product-variant-values/{productVariantValue}', 'update');
      Route::delete('product-variant-values/{productVariantValue}', 'destroy');
    });

    Route::controller(CouponController::class)->group(function () {
      Route::get('coupons', 'index');
      Route::post('coupons', 'store');
      Route::get('coupons/{coupon}', 'show');
      Route::put('coupons/{coupon}', 'update');
      Route::delete('coupons/{coupon}', 'destroy');
    });
  });
});
