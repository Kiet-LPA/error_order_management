<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Zxing\QrReader;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRGatewayService
{
    /**
     * Tạo QR code mới
     */
    public function createQRCode($data)
    {
        try {
            $trackingCode = $data['tracking_code'] ?? $this->generateTrackingCode();
            $size = $data['size'] ?? 300;
            
            // Tạo QR code sử dụng thư viện local
            $qrCode = QrCode::size($size)
                           ->format('png')
                           ->generate($trackingCode);
            
            return [
                'success' => true,
                'data' => $trackingCode,
                'qr_code' => $qrCode
            ];
        } catch (\Exception $e) {
            Log::error('QR Code Generation Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Đọc QR code từ file ảnh
     */
    public function readQRCode($imagePath)
    {
        try {
            // Kiểm tra file có tồn tại không
            if (!file_exists($imagePath)) {
                Log::error('QR Code file not found: ' . $imagePath);
                return null;
            }

            // Đọc QR code sử dụng ZXing
            $qrcode = new QrReader($imagePath);
            $text = $qrcode->text();

            if ($text) {
                Log::info('QR Code read successfully: ' . $text);
                return [
                    'success' => true,
                    'data' => $text
                ];
            } else {
                Log::error('No QR code found in image: ' . $imagePath);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('QR Code Read Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Đọc QR code từ UploadedFile object
     */
    public function readQRCodeFromUpload($uploadedFile)
    {
        try {
            // Lưu file tạm thời
            $tempPath = $uploadedFile->getRealPath();
            
            if (!$tempPath || !file_exists($tempPath)) {
                Log::error('Uploaded file not found or invalid');
                return null;
            }

            // Đọc QR code sử dụng ZXing
            $qrcode = new QrReader($tempPath);
            $text = $qrcode->text();

            if ($text) {
                Log::info('QR Code read successfully: ' . $text);
                return [
                    'success' => true,
                    'data' => $text
                ];
            } else {
                Log::error('No QR code found in uploaded image');
                return null;
            }
        } catch (\Exception $e) {
            Log::error('QR Code Read Error: ' . $e->getMessage());
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
