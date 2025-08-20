<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QRGatewayService
{
    public function generateQRCode($data, $size = 200)
    {
        try {
            $options = new QROptions([
                'version'             => 7,
                'outputType'          => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'            => QRCode::ECC_L,
                'scale'               => 5,
                'imageBase64'         => false,
                'imageTransparent'    => false,
            ]);

            $qrcode = new QRCode($options);
            $qrImage = $qrcode->render($data);
            
            return $qrImage;
        } catch (\Exception $e) {
            Log::error('QR Code Generation Error: ' . $e->getMessage());
            return null;
        }
    }

    public function generateTrackingCode()
    {
        return 'TRK-' . strtoupper(substr(md5(uniqid()), 0, 8));
    }

    // Phương thức test để kiểm tra service
    public function testService()
    {
        try {
            Log::info('Testing QR Gateway Service');
            
            // Test tạo QR code
            $qrCode = $this->generateQRCode('TEST-123');
            if ($qrCode) {
                Log::info('QR Code generation: SUCCESS');
            } else {
                Log::error('QR Code generation: FAILED');
            }
            
            return [
                'qr_generation' => !empty($qrCode),
                'service_status' => 'OK'
            ];
            
        } catch (\Exception $e) {
            Log::error('Service test failed: ' . $e->getMessage());
            return [
                'qr_generation' => false,
                'service_status' => 'ERROR: ' . $e->getMessage()
            ];
        }
    }
}
