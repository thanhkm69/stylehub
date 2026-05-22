<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$product = \App\Models\Product::find(1);
echo "Current Product 1 Description: " . $product->description . PHP_EOL;

// Append a small space or random char to trigger a real save
$product->description = $product->description . ' ';
echo "Saving Product 1..." . PHP_EOL;
$res = $product->save();
echo "Save result: " . ($res ? 'SUCCESS' : 'FAILED') . PHP_EOL;

echo "Waiting 5 seconds for queue worker to process the job..." . PHP_EOL;
sleep(5);

// Check if recommendations exist and when they were updated
foreach (\App\Models\ProductRecommendation::where('product_id', 1)->get() as $rec) {
    echo "Rec: Product #{$rec->product_id} -> Rec #{$rec->recommended_product_id} ({$rec->type}) updated at {$rec->updated_at}" . PHP_EOL;
}
