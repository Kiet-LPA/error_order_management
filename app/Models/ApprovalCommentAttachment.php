<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalCommentAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_comment_id',
        'original_name',
        'file_name',
        'file_path',
        'file_url',
        'mime_type',
        'file_size',
        'file_extension',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array',
        'file_size' => 'integer',
    ];

    // Relationships
    public function approvalComment(): BelongsTo
    {
        return $this->belongsTo(ApprovalComment::class);
    }

    // Methods
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    public function isDocument(): bool
    {
        $documentTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        ];
        
        return in_array($this->mime_type, $documentTypes);
    }

    public function isCompressed(): bool
    {
        $compressedTypes = [
            'application/zip',
            'application/x-rar-compressed',
            'application/x-7z-compressed'
        ];
        
        return in_array($this->mime_type, $compressedTypes);
    }

    public function getFormattedSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 1) . ' ' . $units[$i];
    }

    public function getIconClass(): string
    {
        if ($this->isImage()) {
            return 'bi-image text-primary';
        }
        
        if ($this->isVideo()) {
            return 'bi-play-circle text-danger';
        }
        
        if ($this->isDocument()) {
            $iconMap = [
                'application/pdf' => 'bi-file-pdf text-danger',
                'application/msword' => 'bi-file-word text-primary',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'bi-file-word text-primary',
                'application/vnd.ms-excel' => 'bi-file-excel text-success',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'bi-file-excel text-success',
                'application/vnd.ms-powerpoint' => 'bi-file-ppt text-warning',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'bi-file-ppt text-warning'
            ];
            
            return $iconMap[$this->mime_type] ?? 'bi-file-earmark text-muted';
        }
        
        if ($this->isCompressed()) {
            return 'bi-file-zip text-secondary';
        }
        
        return 'bi-file-earmark text-muted';
    }
}
