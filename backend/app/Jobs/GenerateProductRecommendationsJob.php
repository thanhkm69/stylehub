<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class GenerateProductRecommendationsJob implements ShouldQueue
{
    use Queueable;

    protected $productId;

    /**
     * Create a new job instance.
     */
    public function __construct($productId)
    {
        $this->productId = $productId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Artisan::call('ai:generate-recommendations', [
                'product_id' => $this->productId
            ]);
            Log::info("Dispatched ai:generate-recommendations for Product #{$this->productId} inside background job.");
        } catch (\Exception $e) {
            Log::error("Failed to generate recommendations for Product #{$this->productId} in job: " . $e->getMessage());
        }
    }
}
