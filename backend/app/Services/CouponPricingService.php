<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CouponPricingService
{
    /**
     * @return array{coupon: array<string, mixed>|null, coupon_id: int|null, discount_amount: float}
     */
    public function calculate(?string $couponCode, float $eligibleSubtotal, bool $lockForUpdate = false): array
    {
        $normalizedCode = Str::upper(trim((string) $couponCode));

        if ($normalizedCode === '') {
            return [
                'coupon' => null,
                'coupon_id' => null,
                'discount_amount' => 0.0,
            ];
        }

        $couponQuery = Coupon::query()
            ->whereRaw('UPPER(code) = ?', [$normalizedCode]);

        if ($lockForUpdate) {
            $couponQuery->lockForUpdate();
        }

        $coupon = $couponQuery->first();

        if (! $coupon) {
            $this->invalidCoupon('Mã giảm giá không tồn tại.');
        }

        if (! $coupon->status) {
            $this->invalidCoupon('Mã giảm giá hiện không hoạt động.');
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            $this->invalidCoupon('Mã giảm giá chưa đến thời gian sử dụng.');
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            $this->invalidCoupon('Mã giảm giá đã hết hạn.');
        }

        if ($eligibleSubtotal < (float) $coupon->min_order_value) {
            $minimumValue = number_format((float) $coupon->min_order_value, 0, ',', '.');
            $this->invalidCoupon("Đơn hàng phải đạt tối thiểu {$minimumValue} đ để sử dụng mã này.");
        }

        if ($coupon->usage_limit !== null) {
            $usedCount = Order::query()
                ->where('coupon_id', $coupon->id)
                ->where('status', '!=', 'cancelled')
                ->count();

            if ($usedCount >= $coupon->usage_limit) {
                $this->invalidCoupon('Mã giảm giá đã hết lượt sử dụng.');
            }
        }

        $discountAmount = $this->discountAmount($coupon, $eligibleSubtotal);

        return [
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'name' => $coupon->name,
                'discount_type' => $coupon->discount_type,
                'discount_value' => (float) $coupon->discount_value,
                'discount_amount' => $discountAmount,
            ],
            'coupon_id' => $coupon->id,
            'discount_amount' => $discountAmount,
        ];
    }

    private function discountAmount(Coupon $coupon, float $eligibleSubtotal): float
    {
        if ($coupon->discount_type === 'percentage') {
            $discountAmount = $eligibleSubtotal * min((float) $coupon->discount_value, 100) / 100;

            if ($coupon->max_discount_amount !== null) {
                $discountAmount = min($discountAmount, (float) $coupon->max_discount_amount);
            }

            return (float) min($discountAmount, $eligibleSubtotal);
        }

        return (float) min((float) $coupon->discount_value, $eligibleSubtotal);
    }

    private function invalidCoupon(string $message): never
    {
        throw ValidationException::withMessages([
            'coupon_code' => $message,
        ]);
    }
}
