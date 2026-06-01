<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    if (! in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('PDO SQLite is required to run database-backed review tests.');
    }

    $this->artisan('migrate:fresh');
    Storage::fake('public');
});

test('public reviews listing defaults to active status and supports filters', function () {
    $category = Category::create(['name' => 'Fashion', 'slug' => 'fashion']);
    $product1 = Product::create([
        'category_id' => $category->id,
        'thumbnail' => 'prod1.jpg',
        'name' => 'Dress 1',
        'slug' => 'dress-1',
        'price' => 150000,
    ]);
    $product2 = Product::create([
        'category_id' => $category->id,
        'thumbnail' => 'prod2.jpg',
        'name' => 'Dress 2',
        'slug' => 'dress-2',
        'price' => 250000,
    ]);

    $user = User::factory()->create();

    // 1. Active review on Product 1
    Review::create([
        'user_id' => $user->id,
        'product_id' => $product1->id,
        'rating' => 5,
        'comment' => 'Excellent quality!',
        'status' => 1,
    ]);

    // 2. Inactive review on Product 1
    Review::create([
        'user_id' => $user->id,
        'product_id' => $product1->id,
        'rating' => 2,
        'comment' => 'Poor quality!',
        'status' => 0,
    ]);

    // 3. Active review on Product 2
    Review::create([
        'user_id' => $user->id,
        'product_id' => $product2->id,
        'rating' => 4,
        'comment' => 'Very good dress',
        'status' => 1,
    ]);

    // Test default public list (should only show active reviews: count = 2)
    $response = $this->getJson('/api/reviews');
    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonCount(2, 'data');

    // Test filter by product_id
    $responseProduct = $this->getJson('/api/reviews?product_id='.$product1->id);
    $responseProduct->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.comment', 'Excellent quality!');

    // Test filter by rating
    $responseRating = $this->getJson('/api/reviews?rating=4');
    $responseRating->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.comment', 'Very good dress');
});

test('public review detail displays correct nested fields', function () {
    $category = Category::create(['name' => 'Fashion', 'slug' => 'fashion']);
    $product = Product::create([
        'category_id' => $category->id,
        'thumbnail' => 'prod.jpg',
        'name' => 'Dress',
        'slug' => 'dress',
        'price' => 150000,
    ]);
    $user = User::factory()->create(['name' => 'Jane Doe']);

    $review = Review::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'rating' => 5,
        'comment' => 'Fabulous',
        'status' => 1,
    ]);

    $response = $this->getJson('/api/reviews/'.$review->id);
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Lấy chi tiết đánh giá thành công',
            'data' => [
                'id' => $review->id,
                'rating' => 5,
                'comment' => 'Fabulous',
                'user' => [
                    'id' => $user->id,
                    'name' => 'Jane Doe',
                ],
                'product' => [
                    'id' => $product->id,
                    'name' => 'Dress',
                    'slug' => 'dress',
                    'thumbnail' => 'prod.jpg',
                ],
            ],
        ]);
});

test('user cannot review a product they have not purchased or if order not delivered', function () {
    $category = Category::create(['name' => 'Fashion', 'slug' => 'fashion']);
    $product = Product::create([
        'category_id' => $category->id,
        'thumbnail' => 'prod.jpg',
        'name' => 'Dress',
        'slug' => 'dress',
        'price' => 150000,
    ]);

    $user = User::factory()->create();
    $order = Order::create([
        'user_id' => $user->id,
        'order_code' => 'ORD-123',
        'status' => 'pending', // not delivered
        'shipping_info' => [],
    ]);
    OrderDetail::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => 'Dress',
        'price' => 150000,
        'quantity' => 1,
        'subtotal' => 150000,
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/reviews', [
        'product_id' => $product->id,
        'order_id' => $order->id,
        'rating' => 5,
        'comment' => 'Awesome product!',
    ]);

    // Should return 403 because status is pending
    $response->assertStatus(403)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Bạn chỉ có thể đánh giá sản phẩm đã mua và đã được giao thành công.');
});

test('user can review product from delivered order successfully and upload images', function () {
    $category = Category::create(['name' => 'Fashion', 'slug' => 'fashion']);
    $product = Product::create([
        'category_id' => $category->id,
        'thumbnail' => 'prod.jpg',
        'name' => 'Dress',
        'slug' => 'dress',
        'price' => 150000,
    ]);

    $user = User::factory()->create();
    $order = Order::create([
        'user_id' => $user->id,
        'order_code' => 'ORD-123',
        'status' => 'delivered', // delivered!
        'shipping_info' => [],
    ]);
    OrderDetail::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => 'Dress',
        'price' => 150000,
        'quantity' => 1,
        'subtotal' => 150000,
    ]);

    Sanctum::actingAs($user);

    $image1 = UploadedFile::fake()->image('review1.jpg');
    $image2 = UploadedFile::fake()->image('review2.png');

    $response = $this->postJson('/api/reviews', [
        'product_id' => $product->id,
        'order_id' => $order->id,
        'rating' => 5,
        'comment' => 'Perfect quality!',
        'images' => [$image1, $image2],
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Đánh giá sản phẩm thành công');

    $review = Review::first();
    expect($review->rating)->toBe(5)
        ->and($review->comment)->toBe('Perfect quality!')
        ->and($review->images)->toHaveCount(2);

    // Verify storage
    Storage::disk('public')->assertExists($review->images[0]);
    Storage::disk('public')->assertExists($review->images[1]);
});

test('user cannot review duplicate product in same order', function () {
    $category = Category::create(['name' => 'Fashion', 'slug' => 'fashion']);
    $product = Product::create([
        'category_id' => $category->id,
        'thumbnail' => 'prod.jpg',
        'name' => 'Dress',
        'slug' => 'dress',
        'price' => 150000,
    ]);

    $user = User::factory()->create();
    $order = Order::create([
        'user_id' => $user->id,
        'order_code' => 'ORD-123',
        'status' => 'delivered',
        'shipping_info' => [],
    ]);
    OrderDetail::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => 'Dress',
        'price' => 150000,
        'quantity' => 1,
        'subtotal' => 150000,
    ]);

    // Create existing review
    Review::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'order_id' => $order->id,
        'rating' => 4,
        'comment' => 'Old review',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/reviews', [
        'product_id' => $product->id,
        'order_id' => $order->id,
        'rating' => 5,
        'comment' => 'Duplicate attempt',
    ]);

    $response->assertStatus(400)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Bạn đã đánh giá sản phẩm này cho đơn hàng này rồi.');
});

test('user can update their own review', function () {
    $category = Category::create(['name' => 'Fashion', 'slug' => 'fashion']);
    $product = Product::create([
        'category_id' => $category->id,
        'thumbnail' => 'prod.jpg',
        'name' => 'Dress',
        'slug' => 'dress',
        'price' => 150000,
    ]);

    $user = User::factory()->create();
    $review = Review::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'rating' => 3,
        'comment' => 'Average dress',
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson('/api/reviews/'.$review->id, [
        'rating' => 5,
        'comment' => 'Actually it is great!',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Cập nhật đánh giá thành công');

    $review->refresh();
    expect($review->rating)->toBe(5)
        ->and($review->comment)->toBe('Actually it is great!');
});

test('user cannot update another user review', function () {
    $category = Category::create(['name' => 'Fashion', 'slug' => 'fashion']);
    $product = Product::create([
        'category_id' => $category->id,
        'thumbnail' => 'prod.jpg',
        'name' => 'Dress',
        'slug' => 'dress',
        'price' => 150000,
    ]);

    $owner = User::factory()->create();
    $other = User::factory()->create();

    $review = Review::create([
        'user_id' => $owner->id,
        'product_id' => $product->id,
        'rating' => 3,
        'comment' => 'Average dress',
    ]);

    Sanctum::actingAs($other);

    $response = $this->postJson('/api/reviews/'.$review->id, [
        'rating' => 5,
    ]);

    $response->assertStatus(403)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Bạn không có quyền thực hiện hành động này.');
});

test('user can delete their own review but not others unless admin', function () {
    $category = Category::create(['name' => 'Fashion', 'slug' => 'fashion']);
    $product = Product::create([
        'category_id' => $category->id,
        'thumbnail' => 'prod.jpg',
        'name' => 'Dress',
        'slug' => 'dress',
        'price' => 150000,
    ]);

    $owner = User::factory()->create();
    $other = User::factory()->create();
    $admin = User::factory()->create(['role' => 'Admin']);

    $review1 = Review::create([
        'user_id' => $owner->id,
        'product_id' => $product->id,
        'rating' => 4,
        'comment' => 'Nice dress',
    ]);

    $review2 = Review::create([
        'user_id' => $owner->id,
        'product_id' => $product->id,
        'rating' => 5,
        'comment' => 'Perfect dress',
    ]);

    // 1. Other user tries to delete review1 -> Forbidden
    Sanctum::actingAs($other);
    $response = $this->deleteJson('/api/reviews/'.$review1->id);
    $response->assertStatus(403);

    // 2. Owner deletes review1 -> Success
    Sanctum::actingAs($owner);
    $response = $this->deleteJson('/api/reviews/'.$review1->id);
    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Xóa đánh giá thành công');

    expect(Review::find($review1->id))->toBeNull();

    // 3. Admin deletes review2 -> Success
    Sanctum::actingAs($admin, ['Admin']);
    $response = $this->deleteJson('/api/reviews/'.$review2->id);
    $response->assertStatus(200);

    expect(Review::find($review2->id))->toBeNull();
});

test('admin review management handles search, filtering, and status toggle', function () {
    $category = Category::create(['name' => 'Fashion', 'slug' => 'fashion']);
    $product = Product::create([
        'category_id' => $category->id,
        'thumbnail' => 'prod.jpg',
        'name' => 'Exclusive Shirt',
        'slug' => 'exclusive-shirt',
        'price' => 150000,
    ]);

    $user = User::factory()->create(['name' => 'Kimi']);
    $admin = User::factory()->create(['role' => 'Admin']);

    $review = Review::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'rating' => 5,
        'comment' => 'Perfect shirt!',
        'status' => 1,
    ]);

    Sanctum::actingAs($admin, ['Admin']);

    // Admin index list
    $response = $this->getJson('/api/admin/reviews');
    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');

    // Search by user name
    $responseSearchUser = $this->getJson('/api/admin/reviews?search=Kimi');
    $responseSearchUser->assertStatus(200)->assertJsonCount(1, 'data');

    // Search by invalid name
    $responseSearchInvalid = $this->getJson('/api/admin/reviews?search=NonExistent');
    $responseSearchInvalid->assertStatus(200)->assertJsonCount(0, 'data');

    // Toggle status
    $responseToggle = $this->postJson("/api/admin/reviews/{$review->id}/toggle-status");
    $responseToggle->assertStatus(200)
        ->assertJsonPath('data.status', 0);

    $review->refresh();
    expect($review->status)->toBe(0);
});
