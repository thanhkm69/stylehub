<?php

use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CouponPricingService;
use App\Services\GHNService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    if (! in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('PDO SQLite is required to run database-backed coupon pricing tests.');
    }

    $this->artisan('migrate:fresh');
});

test('it applies a percentage coupon with maximum discount after other price reductions', function () {
    $coupon = Coupon::create([
        'code' => 'SAVE20',
        'name' => 'Save 20',
        'discount_type' => 'percentage',
        'discount_value' => 20,
        'max_discount_amount' => 30000,
        'min_order_value' => 100000,
        'status' => true,
    ]);

    $pricing = app(CouponPricingService::class)->calculate(' save20 ', 200000);

    expect($pricing['coupon_id'])->toBe($coupon->id)
        ->and($pricing['coupon']['code'])->toBe('SAVE20')
        ->and($pricing['discount_amount'])->toBe(30000.0);
});

test('it limits a fixed coupon to the eligible order amount', function () {
    Coupon::create([
        'code' => 'FIXED',
        'name' => 'Fixed discount',
        'discount_type' => 'fixed',
        'discount_value' => 120000,
        'min_order_value' => 0,
        'status' => true,
    ]);

    $pricing = app(CouponPricingService::class)->calculate('FIXED', 50000);

    expect($pricing['discount_amount'])->toBe(50000.0);
});

test('it rejects a coupon when the order does not meet its minimum value', function () {
    Coupon::create([
        'code' => 'MINIMUM',
        'name' => 'Minimum order',
        'discount_type' => 'fixed',
        'discount_value' => 10000,
        'min_order_value' => 150000,
        'status' => true,
    ]);

    app(CouponPricingService::class)->calculate('MINIMUM', 100000);
})->throws(ValidationException::class);

test('it rejects inactive or expired coupons', function (array $attributes) {
    Coupon::create(array_merge([
        'code' => 'INVALID',
        'name' => 'Invalid coupon',
        'discount_type' => 'fixed',
        'discount_value' => 10000,
        'min_order_value' => 0,
        'status' => true,
    ], $attributes));

    app(CouponPricingService::class)->calculate('INVALID', 100000);
})->with([
    'inactive coupon' => [['status' => false]],
    'expired coupon' => [['expires_at' => now()->subMinute()]],
])->throws(ValidationException::class);

test('it rejects a coupon after all usage slots have been consumed', function () {
    $coupon = Coupon::create([
        'code' => 'LIMITED',
        'name' => 'Limited coupon',
        'discount_type' => 'fixed',
        'discount_value' => 10000,
        'min_order_value' => 0,
        'usage_limit' => 1,
        'status' => true,
    ]);
    $user = User::factory()->create();

    Order::create([
        'user_id' => $user->id,
        'coupon_id' => $coupon->id,
        'order_code' => 'ORD-USED',
        'status' => 'pending',
        'payment_method' => 'cod',
        'payment_status' => 'unpaid',
        'shipping_info' => [],
    ]);

    app(CouponPricingService::class)->calculate('LIMITED', 100000);
})->throws(ValidationException::class);

test('it ignores cancelled orders when checking usage limits', function () {
    $coupon = Coupon::create([
        'code' => 'REUSE',
        'name' => 'Reusable coupon',
        'discount_type' => 'fixed',
        'discount_value' => 10000,
        'min_order_value' => 0,
        'usage_limit' => 1,
        'status' => true,
    ]);
    $user = User::factory()->create();

    Order::create([
        'user_id' => $user->id,
        'coupon_id' => $coupon->id,
        'order_code' => 'ORD-CANCELLED',
        'status' => 'cancelled',
        'payment_method' => 'cod',
        'payment_status' => 'unpaid',
        'shipping_info' => [],
    ]);

    $pricing = app(CouponPricingService::class)->calculate('REUSE', 100000);

    expect($pricing['discount_amount'])->toBe(10000.0);
});

test('checkout process stores the applied coupon and discounted total on the order', function () {
    $user = User::factory()->create();
    $category = Category::create(['name' => 'Shoes', 'slug' => 'shoes']);
    $product = Product::create([
        'category_id' => $category->id,
        'thumbnail' => 'shoes.jpg',
        'name' => 'Running Shoes',
        'slug' => 'running-shoes',
        'price' => 200000,
    ]);
    $address = Address::create([
        'user_id' => $user->id,
        'name' => 'Customer',
        'phone' => '0900000000',
        'province_id' => 1,
        'province_name' => 'Ho Chi Minh',
        'district_id' => 1,
        'district_name' => 'District 1',
        'ward_code' => '00001',
        'ward_name' => 'Ward 1',
        'address' => '1 Street',
        'is_default' => true,
    ]);
    Cart::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);
    $coupon = Coupon::create([
        'code' => 'ORDER10',
        'name' => 'Order discount',
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'min_order_value' => 0,
        'status' => true,
    ]);

    $this->mock(GHNService::class, function ($mock) {
        $mock->shouldReceive('calculateShippingFee')
            ->once()
            ->andReturn(['total' => 30000]);
    });
    Mail::fake();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/checkout/process', [
        'address_id' => $address->id,
        'payment_method' => 'cod',
        'customer_name' => 'Customer',
        'customer_phone' => '0900000000',
        'customer_email' => 'customer@example.com',
        'coupon_code' => 'order10',
    ]);

    $response->assertSuccessful();
    $order = Order::query()->sole();

    expect($order->coupon_id)->toBe($coupon->id)
        ->and((float) $order->discount_amount)->toBe(20000.0)
        ->and((float) $order->total_amount)->toBe(210000.0);
});
