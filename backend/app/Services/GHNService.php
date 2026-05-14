<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GHNService
{
    protected string $baseUrl;
    protected string $token;
    protected int $shopId;

    public function __construct()
    {
        $this->baseUrl = config('ghn.base_url');
        $this->token = config('ghn.token');
        $this->shopId = config('ghn.shop_id');
    }

    /**
     * Get list of provinces
     */
    public function getProvinces()
    {
        return Cache::remember('ghn_provinces', 86400, function () {
            $response = Http::withHeaders(['Token' => $this->token])
                ->get($this->baseUrl . 'master-data/province');

            if ($response->successful() && $response->json('code') === 200) {
                return $response->json('data');
            }

            Log::error('GHN getProvinces failed', ['response' => $response->json()]);
            return [];
        });
    }

    /**
     * Get list of districts by province id
     */
    public function getDistricts(int $provinceId)
    {
        return Cache::remember("ghn_districts_{$provinceId}", 86400, function () use ($provinceId) {
            $response = Http::withHeaders(['Token' => $this->token])
                ->get($this->baseUrl . 'master-data/district', [
                    'province_id' => $provinceId
                ]);

            if ($response->successful() && $response->json('code') === 200) {
                return $response->json('data');
            }

            Log::error('GHN getDistricts failed', ['province_id' => $provinceId, 'response' => $response->json()]);
            return [];
        });
    }

    /**
     * Get list of wards by district id
     */
    public function getWards(int $districtId)
    {
        return Cache::remember("ghn_wards_{$districtId}", 86400, function () use ($districtId) {
            $response = Http::withHeaders(['Token' => $this->token])
                ->get($this->baseUrl . 'master-data/ward', [
                    'district_id' => $districtId
                ]);

            if ($response->successful() && $response->json('code') === 200) {
                return $response->json('data');
            }

            Log::error('GHN getWards failed', ['district_id' => $districtId, 'response' => $response->json()]);
            return [];
        });
    }

    /**
     * Calculate shipping fee
     */
    public function calculateShippingFee(array $params)
    {
        $response = Http::withHeaders([
            'Token' => $this->token,
            'ShopId' => $this->shopId
        ])->post($this->baseUrl . 'v2/shipping-order/fee', array_merge([
            'service_type_id' => 2, // E-commerce service
            'from_district_id' => config('ghn.from_district_id'),
            'from_ward_code' => config('ghn.from_ward_code'),
        ], $params));

        if ($response->successful() && $response->json('code') === 200) {
            return $response->json('data');
        }

        Log::error('GHN calculateShippingFee failed', ['params' => $params, 'response' => $response->json()]);
        return null;
    }
}
