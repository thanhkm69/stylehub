<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StyleHubSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Chỉ tạo DUY NHẤT 1 Danh mục
        $category = Category::create([
            'name' => 'Áo Thun',
            'slug' => 'ao-thun',
            'display' => 1,
            'status' => 1
        ]);

        // 2. Chỉ tạo DUY NHẤT 1 Sản phẩm với ảnh thật
        Product::create([
            'category_id' => $category->id,
            'name' => 'Áo Thun Nam',
            'slug' => 'ao-thun-nam-' . Str::random(5),
            'thumbnail' => 'Lug1Offic2Cadj4szk6Wt0EPYVkpE3BOXpIoj2XW.webp',
            'price' => 343831,
            'description' => 'Mô tả sản phẩm áo thun nam chất lượng cao.',
            'status' => 1,
            'views' => 0,
        ]);
    }
}
