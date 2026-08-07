<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocationNameService
{
    /**
     * Reverse geocode tọa độ → tên ngắn (vd: "Bình Đức, An Giang").
     * Chỉ cache khi có tên; miss chỉ cache ngắn để tránh spam API.
     */
    public function resolve(?float $latitude, ?float $longitude): ?string
    {
        if ($latitude === null || $longitude === null) {
            return null;
        }

        if ($latitude == 0.0 && $longitude == 0.0) {
            return null;
        }

        // ~11m — gom cache
        $cacheKey = sprintf('geo:v1:%.4f:%.4f', $latitude, $longitude);
        $hit = Cache::get($cacheKey);
        if (is_string($hit) && $hit !== '') {
            return $hit;
        }

        // Miss gần đây — đừng gọi provider liên tục
        if (Cache::has($cacheKey . ':miss')) {
            return null;
        }

        $name = $this->fetchFromProviders($latitude, $longitude);

        if ($name) {
            Cache::put($cacheKey, $name, now()->addDays(30));
            return $name;
        }

        Cache::put($cacheKey . ':miss', 1, now()->addMinutes(10));

        return null;
    }

    private function fetchFromProviders(float $latitude, float $longitude): ?string
    {
        // 1) BigDataCloud
        try {
            $response = Http::timeout(4)
                ->withHeaders(['User-Agent' => 'HPFoods-Workflow/1.0'])
                ->get('https://api.bigdatacloud.net/data/reverse-geocode-client', [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'localityLanguage' => 'vi',
                ]);

            if ($response->successful()) {
                $name = $this->formatBigDataCloud($response->json() ?? []);
                if ($name) {
                    return $name;
                }
            }
        } catch (\Throwable $e) {
            Log::debug('LocationName BigDataCloud failed', ['error' => $e->getMessage()]);
        }

        // 2) Nominatim OSM
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'HPFoods-Workflow/1.0 (checkin; contact@hpfoods.com.vn)',
                    'Accept-Language' => 'vi',
                ])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'format' => 'json',
                    'addressdetails' => 1,
                    'zoom' => 14,
                ]);

            if ($response->successful()) {
                $name = $this->formatNominatim($response->json() ?? []);
                if ($name) {
                    return $name;
                }
            }
        } catch (\Throwable $e) {
            Log::debug('LocationName Nominatim failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    private function formatBigDataCloud(array $data): ?string
    {
        $locality = $data['locality'] ?? $data['city'] ?? null;
        $province = $data['principalSubdivision'] ?? null;

        $parts = array_values(array_unique(array_filter([$locality, $province])));

        return $parts ? implode(', ', $parts) : null;
    }

    private function formatNominatim(array $data): ?string
    {
        $address = $data['address'] ?? [];
        $locality = $address['suburb']
            ?? $address['village']
            ?? $address['town']
            ?? $address['city_district']
            ?? $address['quarter']
            ?? $address['neighbourhood']
            ?? $address['city']
            ?? null;

        if (is_string($locality)) {
            $locality = preg_replace('/^(Phường|Xã|Thị trấn)\s+/u', '', $locality);
        }

        $province = $address['state'] ?? $address['city'] ?? null;
        if (is_string($province)) {
            $province = preg_replace('/^(Tỉnh|Thành phố)\s+/u', '', $province);
        }

        $parts = array_values(array_unique(array_filter([$locality, $province])));

        return $parts ? implode(', ', $parts) : null;
    }
}
