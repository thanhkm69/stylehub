<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (\App\Models\ProductRecommendation::all() as $rec) {
    echo "Product #{$rec->product_id} -> Rec #{$rec->recommended_product_id} ({$rec->type}) Created at: {$rec->created_at}" . PHP_EOL;
}
