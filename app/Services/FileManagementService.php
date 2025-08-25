<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class FileManagementService
{
    /**
     * Upload file với tên gốc
     */
    public function uploadFile(UploadedFile $file, string $directory, bool $keepOriginalName = true): array
    {
        $originalName = $file->getClientOriginalName();
        
        if ($keepOriginalName) {
            // Tạo tên file an toàn (tránh trùng lặp)
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
            $safeName = $nameWithoutExt;
            $counter = 1;
            
            // Kiểm tra xem file đã tồn tại chưa
            while (file_exists(public_path("storage/{$directory}/" . $safeName . '.' . $extension))) {
                $safeName = $nameWithoutExt . '_' . $counter;
                $counter++;
            }
            
            $fileName = $safeName . '.' . $extension;
        } else {
            $fileName = time() . '_' . $originalName;
        }
        
        $filePath = $file->storeAs("public/{$directory}", $fileName);
        
        // Đảm bảo thư mục public/storage tồn tại
        $publicPath = public_path("storage/{$directory}");
        if (!file_exists($publicPath)) {
            mkdir($publicPath, 0755, true);
        }
        
        // Copy file từ storage sang public storage
        $sourcePath = storage_path("app/public/{$directory}/" . $fileName);
        $destPath = $publicPath . '/' . $fileName;
        if (file_exists($sourcePath)) {
            copy($sourcePath, $destPath);
        }
        
        // Tạo meta data cho file
        $meta = $this->generateFileMeta($file, $sourcePath);
        
        return [
            'original_name' => $originalName,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_url' => asset("storage/{$directory}/" . $fileName),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'file_extension' => pathinfo($originalName, PATHINFO_EXTENSION),
            'meta' => $meta
        ];
    }
    
    /**
     * Tạo meta data cho file
     */
    private function generateFileMeta(UploadedFile $file, string $filePath): array
    {
        $meta = [];
        
        if (str_starts_with($file->getMimeType(), 'image/')) {
            $imageInfo = getimagesize($filePath);
            if ($imageInfo) {
                $meta['dimensions'] = [
                    'width' => $imageInfo[0],
                    'height' => $imageInfo[1]
                ];
                
                // Tạo thumbnail cho hình ảnh
                $this->createThumbnail($filePath, $meta);
            }
        }
        
        if (str_starts_with($file->getMimeType(), 'video/')) {
            // Có thể thêm thông tin video như duration, resolution
            $meta['type'] = 'video';
        }
        
        return $meta;
    }
    
    /**
     * Tạo thumbnail cho hình ảnh
     */
    private function createThumbnail(string $filePath, array &$meta): void
    {
        try {
            $thumbnailPath = str_replace('.', '_thumb.', $filePath);
            $thumbnailName = basename($thumbnailPath);
            
            // Tạo thumbnail 300x300
            $image = Image::make($filePath);
            $image->fit(300, 300, function ($constraint) {
                $constraint->upsize();
            });
            $image->save($thumbnailPath, 80);
            
            $meta['thumbnail'] = [
                'path' => $thumbnailPath,
                'name' => $thumbnailName,
                'url' => asset('storage/thumbnails/' . $thumbnailName)
            ];
        } catch (\Exception $e) {
            // Log error nếu không tạo được thumbnail
            \Log::error('Failed to create thumbnail: ' . $e->getMessage());
        }
    }
    
    /**
     * Xóa file
     */
    public function deleteFile(string $filePath, string $fileName): bool
    {
        try {
            // Xóa file chính
            if (file_exists(public_path($filePath . '/' . $fileName))) {
                unlink(public_path($filePath . '/' . $fileName));
            }
            
            // Xóa thumbnail nếu có
            $thumbnailPath = str_replace('.', '_thumb.', public_path($filePath . '/' . $fileName));
            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to delete file: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate file
     */
    public function validateFile(UploadedFile $file, array $allowedTypes = [], int $maxSize = 1073741824): array
    {
        $errors = [];
        
        // Kiểm tra kích thước
        if ($file->getSize() > $maxSize) {
            $errors[] = "File {$file->getClientOriginalName()} vượt quá kích thước cho phép.";
        }
        
        // Kiểm tra loại file
        if (!empty($allowedTypes) && !in_array($file->getMimeType(), $allowedTypes)) {
            $errors[] = "File {$file->getClientOriginalName()} không được hỗ trợ.";
        }
        
        return $errors;
    }
    
    /**
     * Compress hình ảnh
     */
    public function compressImage(string $filePath, int $quality = 80): bool
    {
        try {
            $image = Image::make($filePath);
            $image->save($filePath, $quality);
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to compress image: ' . $e->getMessage());
            return false;
        }
    }
}
