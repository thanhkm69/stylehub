<?php


use App\Http\Controllers\AttributeController;
use App\Http\Controllers\AttributeValueController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductPublicController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GithubController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\ProductVariantValueController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\FlashSaleController;
use App\Http\Controllers\FlashSaleItemController;
use App\Http\Controllers\ComboController;
use App\Http\Controllers\ComboItemController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\GHNController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\BlogCategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\BlogPublicController;
use App\Http\Controllers\VNPayController;
use App\Http\Controllers\MoMoController;
use App\Http\Controllers\BannerController;
use Illuminate\Support\Facades\Route;

Route::post('verify', [AuthController::class, 'sendVerifyEmailOtp']);
Route::post('contacts', [ContactController::class, 'store']);
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

// Public routes for public data
Route::get('coupons/active', [CouponController::class, 'getActive']);

Route::get('home', [ProductPublicController::class, 'home']);
Route::get('shop', [ProductPublicController::class, 'index']);
Route::get('shop/{product:slug}', [ProductPublicController::class, 'show']);

// Public - GHN
Route::prefix('ghn')->group(function () {
    Route::get('provinces', [GHNController::class, 'provinces']);
    Route::get('districts', [GHNController::class, 'districts']);
    Route::get('wards', [GHNController::class, 'wards']);
    Route::post('shipping-fee', [GHNController::class, 'shippingFee']);
});
Route::get('blog-categories/active', [BlogPublicController::class, 'categories']);
Route::get('blogs', [BlogPublicController::class, 'index']);
Route::get('blogs/{slug}', [BlogPublicController::class, 'show']);

// Public - MoMo callback
Route::prefix('momo')->group(function () {
    Route::get('return', [MoMoController::class, 'return']);
    Route::post('notify', [MoMoController::class, 'notify']);
});

Route::middleware('auth:sanctum')->group(function () {
  Route::post('logout', [AuthController::class, 'logout']);
  Route::get('get-user', [AuthController::class, 'getUser']);

  Route::post('change-password', [AuthController::class, 'changePassword']);
  Route::get('profile', [ProfileController::class, 'me']);
  Route::put('profile', [ProfileController::class, 'updateSelf']);

  Route::get('wishlist', [\App\Http\Controllers\WishlistController::class, 'index']);
  Route::get('wishlist/ids', [\App\Http\Controllers\WishlistController::class, 'ids']);
  Route::post('wishlist', [\App\Http\Controllers\WishlistController::class, 'store']);
  Route::delete('wishlist/{wishlist}', [\App\Http\Controllers\WishlistController::class, 'destroy']);
  Route::post('wishlist/toggle', [\App\Http\Controllers\WishlistController::class, 'toggle']);

  // Cart Routes
  Route::get('cart', [CartController::class, 'index']);
  Route::post('cart', [CartController::class, 'store']);
  Route::post('cart/{cart}', [CartController::class, 'update']);
  Route::delete('cart/clear', [CartController::class, 'clear']);
  Route::delete('cart/{cart}', [CartController::class, 'destroy']);

  // Address Routes
  Route::controller(AddressController::class)->group(function () {
    Route::get('addresses', 'index');
    Route::post('addresses', 'store');
    Route::get('addresses/{address}', 'show');
    Route::post('addresses/{address}', 'update');
    Route::delete('addresses/{address}', 'destroy');
    Route::post('addresses/{address}/set-default', 'setDefault');
  });

  // Checkout Routes
  Route::get('checkout', [App\Http\Controllers\CheckoutController::class, 'index']);
  Route::post('checkout/preview', [App\Http\Controllers\CheckoutController::class, 'preview']);
  Route::post('checkout/process', [App\Http\Controllers\CheckoutController::class, 'process']);

  // User Order Routes
  Route::get('orders', [OrderController::class, 'index']);
  Route::get('orders/code/{code}', [OrderController::class, 'showByCode']);
  Route::get('orders/{order}', [OrderController::class, 'show']);

  // VNPay Payment Routes
  Route::controller(VNPayController::class)->group(function () {
      Route::post('vnpay/create-payment', 'createPayment');
      Route::get('vnpay/status/{order}', 'status');
  });

  // MoMo Payment Routes
  Route::controller(MoMoController::class)->group(function () {
      Route::post('momo/create-payment', 'createPayment');
      Route::get('momo/status/{order}', 'status');
  });

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
      Route::get('categories/{category}', 'show');
      Route::post('categories/{category}', 'update');
      Route::delete('categories/{category}', 'destroy');
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

    Route::controller(FlashSaleController::class)->group(function () {
      Route::get('flash-sales', 'index');
      Route::post('flash-sales', 'store');
      Route::get('flash-sales/{flashSale}', 'show');
      Route::post('flash-sales/{flashSale}', 'update');
      Route::delete('flash-sales/{flashSale}', 'destroy');
    });

    Route::controller(FlashSaleItemController::class)->group(function () {
      Route::get('flash-sale-items', 'index');
      Route::post('flash-sale-items', 'store');
      Route::get('flash-sale-items/{flashSaleItem}', 'show');
      Route::put('flash-sale-items/{flashSaleItem}', 'update');
      Route::delete('flash-sale-items/{flashSaleItem}', 'destroy');
    });

    Route::controller(ComboController::class)->group(function () {
      Route::get('combos', 'index');
      Route::post('combos', 'store');
      Route::get('combos/{combo}', 'show');
      Route::post('combos/{combo}', 'update');
      Route::delete('combos/{combo}', 'destroy');
    });

    Route::controller(ComboItemController::class)->group(function () {
      Route::get('combo-items', 'index');
      Route::post('combo-items', 'store');
      Route::get('combo-items/{comboItem}', 'show');
      Route::put('combo-items/{comboItem}', 'update');
      Route::delete('combo-items/{comboItem}', 'destroy');
    });

    Route::controller(ProfileController::class)->group(function () {
      Route::get('profiles', 'index');
      Route::post('profiles', 'store');
      Route::get('profiles/{profile}', 'show');
      Route::put('profiles/{profile}', 'update');
      Route::delete('profiles/{profile}', 'destroy');
    });

    Route::controller(ContactController::class)->group(function () {
      Route::get('contacts', 'index');
      Route::get('contacts/{contact}', 'show');
      Route::put('contacts/{contact}', 'update');
      Route::delete('contacts/{contact}', 'destroy');
      Route::controller(ContactController::class)->group(function () {
        Route::get('contacts', 'index');
        Route::get('contacts/{contact}', 'show');
        Route::put('contacts/{contact}', 'update');
        Route::delete('contacts/{contact}', 'destroy');
      });

      // Order Management
      Route::prefix('admin')->group(function () {
        Route::controller(OrderController::class)->group(function () {
          Route::get('orders', 'index');
          Route::post('orders', 'store');
          Route::get('orders/{order}', 'show');
          Route::post('orders/{order}', 'update');
          Route::delete('orders/{order}', 'destroy');
        });
      });
    });

    Route::controller(BlogCategoryController::class)->group(function () {
      Route::get('blog-categories', 'index');
      Route::post('blog-categories', 'store');
      Route::get('blog-categories/{blogCategory}', 'show');
      Route::put('blog-categories/{blogCategory}', 'update');
      Route::delete('blog-categories/{blogCategory}', 'destroy');
    });

    Route::controller(PostController::class)->group(function () {
      Route::get('posts', 'index');
      Route::post('posts', 'store');
      Route::get('posts/{post}', 'show');
      Route::post('posts/{post}', 'update');
      Route::delete('posts/{post}', 'destroy');
    });

    Route::controller(BannerController::class)->group(function () {
      Route::get('banners', 'index');
      Route::post('banners', 'store');
      Route::get('banners/{banner}', 'show');
      Route::post('banners/{banner}', 'update');
      Route::delete('banners/{banner}', 'destroy');
    });
  });
});