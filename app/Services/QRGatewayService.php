<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QRGatewayService
{
    private $apiKey;
    private $baseUrl = 'https://api.qrgateway.com';

    public function __construct()
    {
        $this->apiKey = 'f4QvdX2lsvgUUwn0YcyHs5WS';
    }

    /**
     * Tạo QR code mới
     */
    public function createQRCode($data)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/qr-codes', [
                'name' => $data['name'] ?? 'Tracking Code',
                'data' => $data['tracking_code'],
                'size' => $data['size'] ?? 300,
                'format' => $data['format'] ?? 'png',
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('QR Gateway API Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('QR Gateway Service Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Đọc QR code từ file ảnh
     */
    public function readQRCode($imagePath)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->attach('file', file_get_contents($imagePath), 'qr_code.png')
              ->post($this->baseUrl . '/qr-codes/read');

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('QR Gateway Read Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('QR Gateway Read Service Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Tạo tracking code ngẫu nhiên
     */
    public function generateTrackingCode()
    {
        return 'TRK' . date('Ymd') . strtoupper(substr(md5(uniqid()), 0, 8));
    }
}
