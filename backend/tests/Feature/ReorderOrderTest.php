<?php

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    if (! in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('PDO SQLite is required to run database-backed reorder tests.');
    }

    $this->artisan('migrate:fresh');
});

test('customer can reorder a delivered order within current variant stock', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['User']);

    $category = Category::create(['name' => 'Fashion', 'slug' => 'fashion']);
    $product = Product::create([
        'category_id' => $category->id,
        'thumbnail' => 'dress.jpg',
        'name' => 'Dress',
        'slug' => 'dress',
        'price' => 150000,
    ]);
    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'sku' => 'DRESS-S',
        'price' => 150000,
        'stock' => 3,
        'image' => 'dress-s.jpg',
        'status' => true,
    ]);
    $order = Order::create([
        'user_id' => $user->id,
        'order_code' => 'ORD-DELIVERED',
        'status' => 'delivered',
        'shipping_info' => [],
    ]);
    $order->orderDetails()->create([
        'product_id' => $product->id,
        'variant_id' => $variant->id,
        'product_name' => $product->name,
        'price' => 150000,
        'quantity' => 5,
        'subtotal' => 750000,
    ]);
    Cart::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'quantity' => 1,
    ]);

    $response = $this->postJson("/api/orders/{$order->id}/reorder");

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.added_quantity', 2)
        ->assertJsonCount(1, 'data.adjusted_items');

    $this->assertDatabaseHas('carts', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'quantity' => 3,
    ]);
});

test('customer can reorder a cancelled order', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['User']);

    $category = Category::create(['name' => 'Basics', 'slug' => 'basics']);
    $product = Product::create([
        'category_id' => $category->id,
        'thumbnail' => 'shirt.jpg',
        'name' => 'Shirt',
        'slug' => 'shirt',
        'price' => 100000,
    ]);
    $order = Order::create([
        'user_id' => $user->id,
        'order_code' => 'ORD-CANCELLED',
        'status' => 'cancelled',
        'shipping_info' => [],
    ]);
    $order->orderDetails()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'price' => 100000,
        'quantity' => 2,
        'subtotal' => 200000,
    ]);

    $this->postJson("/api/orders/{$order->id}/reorder")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.added_quantity', 2);

    $this->assertDatabaseHas('carts', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 2,
    ]);
});

test('customer can only reorder their own completed or cancelled order', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    Sanctum::actingAs($user, ['User']);

    $pendingOrder = Order::create([
        'user_id' => $user->id,
        'order_code' => 'ORD-PENDING',
        'status' => 'pending',
        'shipping_info' => [],
    ]);
    $otherOrder = Order::create([
        'user_id' => $otherUser->id,
        'order_code' => 'ORD-OTHER',
        'status' => 'cancelled',
        'shipping_info' => [],
    ]);

    $this->postJson("/api/orders/{$pendingOrder->id}/reorder")
        ->assertUnprocessable()
        ->assertJsonPath('success', false);

    $this->postJson("/api/orders/{$otherOrder->id}/reorder")
        ->assertForbidden()
        ->assertJsonPath('success', false);
});

test('customer order history and detail exclude other customers orders', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    Sanctum::actingAs($user, ['User']);

    $ownOrder = Order::create([
        'user_id' => $user->id,
        'order_code' => 'ORD-OWN',
        'status' => 'delivered',
        'shipping_info' => [],
    ]);
    $otherOrder = Order::create([
        'user_id' => $otherUser->id,
        'order_code' => 'ORD-HIDDEN',
        'status' => 'delivered',
        'shipping_info' => [],
    ]);

    $this->getJson('/api/orders')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $ownOrder->id);

    $this->getJson("/api/orders/{$otherOrder->id}")
        ->assertForbidden()
        ->assertJsonPath('success', false);
});

test('admin personal order history remains personal while admin order management lists all orders', function () {
    $admin = User::factory()->create(['role' => 'Admin']);
    $customer = User::factory()->create();
    Sanctum::actingAs($admin, ['Admin']);

    $ownOrder = Order::create([
        'user_id' => $admin->id,
        'order_code' => 'ORD-ADMIN-OWN',
        'status' => 'delivered',
        'shipping_info' => [],
    ]);
    $customerOrder = Order::create([
        'user_id' => $customer->id,
        'order_code' => 'ORD-CUSTOMER',
        'status' => 'delivered',
        'shipping_info' => [],
    ]);

    $this->getJson('/api/orders')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $ownOrder->id);

    $this->getJson("/api/orders/{$customerOrder->id}")
        ->assertForbidden();

    $this->getJson('/api/admin/orders')
        ->assertOk()
        ->assertJsonCount(2, 'data');

    $this->getJson("/api/admin/orders/{$customerOrder->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $customerOrder->id);
});
