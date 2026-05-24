<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductRecommendation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateAiRecommendations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:generate-recommendations {product_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate AI product recommendations for mix and match';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            $this->error('GEMINI_API_KEY is not set in .env');
            return;
        }

        $productId = $this->argument('product_id');

        $query = Product::with('category')->where('status', 1);
        if ($productId) {
            $query->where('id', $productId);
        }

        $targetProducts = $query->get();
        if ($targetProducts->isEmpty()) {
            $this->info('No products found.');
            return;
        }

        // Fetch catalog summary for context
        $catalog = Product::with('category')->where('status', 1)->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'category' => $p->category ? $p->category->name : 'Uncategorized',
            ];
        })->toArray();

        $catalogJson = json_encode($catalog, JSON_UNESCAPED_UNICODE);

        foreach ($targetProducts as $product) {
            $this->info("Generating recommendations for Product #{$product->id} - {$product->name}");

            $prompt = "You are a fashion stylist AI. We have a catalog of products (JSON): \n{$catalogJson}\n\n";
            $prompt .= "The user is currently viewing the following product:\n";
            $prompt .= "- ID: {$product->id}\n";
            $prompt .= "- Name: {$product->name}\n";
            $prompt .= "- Category: " . ($product->category ? $product->category->name : 'Uncategorized') . "\n";
            $prompt .= "- Description: " . strip_tags($product->description) . "\n\n";
            $prompt .= "Task: Select up to 4 'similar' products (same category/style) and up to 4 'outfit' products (complementary items, e.g. pants for a shirt) from the catalog. Do not include the viewed product ID. \n";
            $prompt .= "Return ONLY a valid JSON object in this format without markdown code blocks:\n";
            $prompt .= "{\n  \"similar_ids\": [1, 2, 3],\n  \"outfit_ids\": [4, 5, 6]\n}";

            try {
                $response = Http::timeout(60)
                    ->retry(3, 3000) // Retry 3 times, wait 3 seconds between retries
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                    ]
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    
                    $data = json_decode($text, true);
                    if ($data && (isset($data['similar_ids']) || isset($data['outfit_ids']))) {
                        // Clear old recommendations
                        ProductRecommendation::where('product_id', $product->id)->delete();

                        // Insert similar
                        if (isset($data['similar_ids']) && is_array($data['similar_ids'])) {
                            foreach ($data['similar_ids'] as $recId) {
                                if (Product::find($recId) && $recId != $product->id) {
                                    ProductRecommendation::create([
                                        'product_id' => $product->id,
                                        'recommended_product_id' => $recId,
                                        'type' => 'similar'
                                    ]);
                                }
                            }
                        }

                        // Insert outfit
                        if (isset($data['outfit_ids']) && is_array($data['outfit_ids'])) {
                            foreach ($data['outfit_ids'] as $recId) {
                                if (Product::find($recId) && $recId != $product->id) {
                                    ProductRecommendation::create([
                                        'product_id' => $product->id,
                                        'recommended_product_id' => $recId,
                                        'type' => 'outfit'
                                    ]);
                                }
                            }
                        }

                        // Clear cache so frontend sees recommendations immediately
                        \Illuminate\Support\Facades\Cache::forget('product_' . $product->slug);

                        $this->info("Successfully generated for Product #{$product->id}");
                    } else {
                        $this->error("Invalid JSON structure returned by AI for Product #{$product->id}");
                        Log::error("AI JSON Error", ['text' => $text]);
                    }
                } else {
                    $this->error("API Error: " . $response->body());
                }
            } catch (\Exception $e) {
                $this->error("Exception: " . $e->getMessage());
            }

            // Sleep to avoid rate limits
            sleep(2);
        }

        $this->info('Done generating recommendations.');
    }
}
