<?php

namespace App\Services;

use App\Models\FlashSaleItem;
use App\Models\Product;
use App\Models\ProductVariant;

class FlashSalePricingService
{
    public function forSelection(Product $product, ?ProductVariant $variant = null): array
    {
        $regularPrice = (float) ($variant?->price ?? $product->price);
        $query = $this->activeItemsQuery($product);

        if ($variant) {
            $query->where(function ($itemQuery) use ($variant) {
                $itemQuery->whereNull('product_variant_id')
                    ->orWhere('product_variant_id', $variant->id);
            });
        } else {
            $query->whereNull('product_variant_id');
        }

        return $this->bestPricing($query->get()->all(), $regularPrice);
    }

    public function forListing(Product $product): array
    {
        $items = $this->activeItemsQuery($product)->get();
        $best = null;

        foreach ($items as $item) {
            $regularPrice = (float) $item->original_price;
            if ((float) $item->sale_price >= $regularPrice) {
                continue;
            }

            $pricing = $this->formatPricing($item, $regularPrice);
            $pricing['is_starting_price'] = $item->product_variant_id !== null;

            if (! $best || $pricing['price'] < $best['price']) {
                $best = $pricing;
            }
        }

        return $best ?? [
            'price' => (float) $product->price,
            'original_price' => (float) $product->price,
            'flash_sale' => null,
            'is_starting_price' => false,
        ];
    }

    private function activeItemsQuery(Product $product)
    {
        return FlashSaleItem::query()
            ->where('product_id', $product->id)
            ->where('status', 1)
            ->whereHas('flashSale', function ($query) {
                $query->where('status', 'active')
                    ->where('starts_at', '<=', now())
                    ->where('ends_at', '>=', now());
            })
            ->with('flashSale')
            ->orderBy('display');
    }

    private function bestPricing(array $items, float $regularPrice): array
    {
        $best = null;

        foreach ($items as $item) {
            if ((float) $item->sale_price >= $regularPrice) {
                continue;
            }

            $pricing = $this->formatPricing($item, $regularPrice);
            if (! $best || $pricing['price'] < $best['price']) {
                $best = $pricing;
            }
        }

        return $best ?? [
            'price' => $regularPrice,
            'original_price' => $regularPrice,
            'flash_sale' => null,
            'is_starting_price' => false,
        ];
    }

    private function formatPricing(FlashSaleItem $item, float $regularPrice): array
    {
        return [
            'price' => (float) $item->sale_price,
            'original_price' => $regularPrice,
            'flash_sale' => [
                'id' => $item->flashSale->id,
                'name' => $item->flashSale->name,
                'ends_at' => $item->flashSale->ends_at,
            ],
            'is_starting_price' => false,
        ];
    }
}
