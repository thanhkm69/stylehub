<?php

namespace App\Services;

use App\Models\Combo;
use Illuminate\Support\Collection;

class ComboPricingService
{
    public function calculate(Collection $cartItems): array
    {
        $combos = Combo::query()
            ->where('status', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->whereHas('items')
            ->with(['items.product', 'items.productVariant'])
            ->orderBy('display')
            ->get();

        $initialQuantities = $this->itemQuantities($cartItems);
        $candidates = $combos
            ->map(function ($combo) use ($cartItems, $initialQuantities) {
                $remainingQuantities = $initialQuantities;

                return $this->calculateApplication($combo, $cartItems, $remainingQuantities);
            })
            ->filter()
            ->sortByDesc('discount_amount');
        $remainingQuantities = $initialQuantities;
        $appliedCombos = [];

        foreach ($candidates as $candidate) {
            $combo = $combos->firstWhere('id', $candidate['id']);
            $application = $this->calculateApplication($combo, $cartItems, $remainingQuantities);

            if ($application) {
                $appliedCombos[] = $application;
            }
        }

        return [
            'combo_discount' => (float) array_sum(array_column($appliedCombos, 'discount_amount')),
            'applied_combos' => $appliedCombos,
            'applied_combo' => $appliedCombos[0] ?? null,
        ];
    }

    private function calculateApplication(Combo $combo, Collection $cartItems, array &$remainingQuantities): ?array
    {
        $startingQuantities = $remainingQuantities;
        $matchedSets = [];
        $requirements = $combo->items
            ->sortBy(fn ($item) => [$item->product_variant_id ? 0 : 1, $item->id])
            ->values();

        if ($requirements->isEmpty()) {
            return null;
        }

        while (true) {
            $quantitiesBeforeSet = $remainingQuantities;
            $matchedSet = [
                'subtotal' => 0,
            ];

            foreach ($requirements as $requiredItem) {
                $requiredQuantity = max((int) $requiredItem->quantity, 1);
                $matchingItems = $cartItems
                    ->filter(function ($cartItem) use ($requiredItem, $remainingQuantities) {
                        if (($remainingQuantities[$cartItem->id] ?? 0) <= 0) {
                            return false;
                        }

                        if ($cartItem->product_id !== $requiredItem->product_id) {
                            return false;
                        }

                        return ! $requiredItem->product_variant_id
                            || $cartItem->product_variant_id === $requiredItem->product_variant_id;
                    })
                    ->sortByDesc(fn ($cartItem) => (float) $cartItem->getAttribute('pricing')['price']);

                foreach ($matchingItems as $cartItem) {
                    $takenQuantity = min($requiredQuantity, $remainingQuantities[$cartItem->id]);
                    $lineSubtotal = $takenQuantity * (float) $cartItem->getAttribute('pricing')['price'];
                    $matchedSet['subtotal'] += $lineSubtotal;
                    $remainingQuantities[$cartItem->id] -= $takenQuantity;
                    $requiredQuantity -= $takenQuantity;

                    if ($requiredQuantity === 0) {
                        break;
                    }
                }

                if ($requiredQuantity > 0) {
                    $remainingQuantities = $quantitiesBeforeSet;
                    break 2;
                }
            }

            $matchedSets[] = $matchedSet;
        }

        if ($matchedSets === []) {
            return null;
        }

        $discountAmount = $this->discountAmount($combo, $matchedSets);
        if ($discountAmount <= 0) {
            $remainingQuantities = $startingQuantities;

            return null;
        }

        return [
            'id' => $combo->id,
            'name' => $combo->name,
            'thumbnail' => $combo->thumbnail,
            'discount_type' => $combo->discount_type,
            'discount_value' => (float) $combo->discount_value,
            'quantity' => count($matchedSets),
            'matched_subtotal' => (float) array_sum(array_column($matchedSets, 'subtotal')),
            'discount_amount' => (float) $discountAmount,
            'items' => $requirements->map(function ($item) use ($matchedSets) {
                return [
                    'id' => $item->id,
                    'quantity' => (int) $item->quantity * count($matchedSets),
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_name' => $item->product?->name,
                    'product_slug' => $item->product?->slug,
                    'thumbnail' => $item->productVariant?->image ?? $item->product?->thumbnail,
                    'variant_sku' => $item->productVariant?->sku,
                ];
            })->values(),
        ];
    }

    private function itemQuantities(Collection $cartItems): array
    {
        return $cartItems->mapWithKeys(fn ($item) => [$item->id => (int) $item->quantity])->all();
    }

    private function discountAmount(Combo $combo, array $matchedSets): float
    {
        $setSubtotals = array_column($matchedSets, 'subtotal');
        $discountValue = (float) $combo->discount_value;

        if ($combo->discount_type === 'percentage') {
            return array_sum($setSubtotals) * min($discountValue, 100) / 100;
        }

        return min($discountValue * count($setSubtotals), array_sum($setSubtotals));
    }
}
