<?php

use App\Models\Cart;
use App\Models\Category;
use App\Models\Combo;
use App\Models\ComboItem;
use App\Models\Product;
use App\Services\ComboPricingService;

function pricedCartItem(int $id, Product $product, int $quantity): Cart
{
    $item = new Cart([
        'product_id' => $product->id,
        'quantity' => $quantity,
    ]);
    $item->id = $id;
    $item->setAttribute('pricing', [
        'price' => (float) $product->price,
        'original_price' => (float) $product->price,
        'flash_sale' => null,
    ]);

    return $item;
}

test('it applies an eligible combo for every complete set in the cart', function () {
    if (! in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('PDO SQLite is required to run database-backed combo pricing tests.');
    }

    $this->artisan('migrate:fresh');

    $category = Category::create(['name' => 'Ao', 'slug' => 'ao']);
    $shirt = Product::create([
        'category_id' => $category->id,
        'thumbnail' => 'shirt.jpg',
        'name' => 'Ao so mi',
        'slug' => 'ao-so-mi',
        'price' => 100000,
    ]);
    $pants = Product::create([
        'category_id' => $category->id,
        'thumbnail' => 'pants.jpg',
        'name' => 'Quan',
        'slug' => 'quan',
        'price' => 50000,
    ]);
    $combo = Combo::create([
        'name' => 'Combo cong so',
        'discount_type' => 'percentage',
        'discount_value' => 20,
        'status' => true,
    ]);
    ComboItem::create(['combo_id' => $combo->id, 'product_id' => $shirt->id, 'quantity' => 1]);
    ComboItem::create(['combo_id' => $combo->id, 'product_id' => $pants->id, 'quantity' => 1]);

    $pricing = app(ComboPricingService::class)->calculate(collect([
        pricedCartItem(1, $shirt, 2),
        pricedCartItem(2, $pants, 2),
    ]));

    expect($pricing['combo_discount'])->toBe(60000.0)
        ->and($pricing['applied_combo']['id'])->toBe($combo->id)
        ->and($pricing['applied_combos'])->toHaveCount(1)
        ->and($pricing['applied_combo']['quantity'])->toBe(2);
});

test('it does not apply a combo when the cart is missing a required item', function () {
    if (! in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('PDO SQLite is required to run database-backed combo pricing tests.');
    }

    $this->artisan('migrate:fresh');

    $category = Category::create(['name' => 'Ao', 'slug' => 'ao']);
    $shirt = Product::create([
        'category_id' => $category->id,
        'thumbnail' => 'shirt.jpg',
        'name' => 'Ao so mi',
        'slug' => 'ao-so-mi',
        'price' => 100000,
    ]);
    $pants = Product::create([
        'category_id' => $category->id,
        'thumbnail' => 'pants.jpg',
        'name' => 'Quan',
        'slug' => 'quan',
        'price' => 50000,
    ]);
    $combo = Combo::create([
        'name' => 'Combo cong so',
        'discount_type' => 'fixed_price',
        'discount_value' => 30000,
        'status' => true,
    ]);
    ComboItem::create(['combo_id' => $combo->id, 'product_id' => $shirt->id, 'quantity' => 1]);
    ComboItem::create(['combo_id' => $combo->id, 'product_id' => $pants->id, 'quantity' => 1]);

    $pricing = app(ComboPricingService::class)->calculate(collect([
        pricedCartItem(1, $shirt, 1),
    ]));

    expect($pricing['combo_discount'])->toBe(0.0)
        ->and($pricing['applied_combos'])->toBeEmpty()
        ->and($pricing['applied_combo'])->toBeNull();
});

test('it applies multiple active combos when enough separate items are eligible', function () {
    if (! in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('PDO SQLite is required to run database-backed combo pricing tests.');
    }

    $this->artisan('migrate:fresh');

    $category = Category::create(['name' => 'All', 'slug' => 'all']);
    $shirt = Product::create([
        'category_id' => $category->id,
        'thumbnail' => 'shirt.jpg',
        'name' => 'Shirt',
        'slug' => 'shirt',
        'price' => 100000,
    ]);
    $pants = Product::create([
        'category_id' => $category->id,
        'thumbnail' => 'pants.jpg',
        'name' => 'Pants',
        'slug' => 'pants',
        'price' => 100000,
    ]);
    $jacket = Product::create([
        'category_id' => $category->id,
        'thumbnail' => 'jacket.jpg',
        'name' => 'Jacket',
        'slug' => 'jacket',
        'price' => 200000,
    ]);
    $scarf = Product::create([
        'category_id' => $category->id,
        'thumbnail' => 'scarf.jpg',
        'name' => 'Scarf',
        'slug' => 'scarf',
        'price' => 30000,
    ]);
    $fixedCombo = Combo::create([
        'name' => 'Office Combo',
        'discount_type' => 'fixed_price',
        'discount_value' => 25000,
        'status' => true,
    ]);
    $secondCombo = Combo::create([
        'name' => 'Winter Combo',
        'discount_type' => 'fixed_price',
        'discount_value' => 30000,
        'status' => true,
    ]);
    ComboItem::create(['combo_id' => $fixedCombo->id, 'product_id' => $shirt->id, 'quantity' => 1]);
    ComboItem::create(['combo_id' => $fixedCombo->id, 'product_id' => $pants->id, 'quantity' => 1]);
    ComboItem::create(['combo_id' => $secondCombo->id, 'product_id' => $jacket->id, 'quantity' => 1]);
    ComboItem::create(['combo_id' => $secondCombo->id, 'product_id' => $scarf->id, 'quantity' => 1]);

    $pricing = app(ComboPricingService::class)->calculate(collect([
        pricedCartItem(1, $shirt, 1),
        pricedCartItem(2, $pants, 1),
        pricedCartItem(3, $jacket, 1),
        pricedCartItem(4, $scarf, 1),
    ]));

    expect($pricing['combo_discount'])->toBe(55000.0)
        ->and($pricing['applied_combos'])->toHaveCount(2)
        ->and($pricing['applied_combos'][0]['items'][0]['product_slug'])->not->toBeNull();
});
