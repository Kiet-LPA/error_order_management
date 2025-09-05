<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class Update403Messages extends Command
{
    protected $signature = 'update:403-messages';
    protected $description = 'Cập nhật tất cả thông báo lỗi 403 thành thông báo mới';

    public function handle()
    {
        $this->info('Bắt đầu cập nhật thông báo lỗi 403...');

        $newMessage = 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện';
        
        // Danh sách các file cần cập nhật
        $files = [
            'app/Http/Controllers/Admin/UserController.php',
            'app/Http/Controllers/TaskController.php',
            'app/Http/Controllers/TaskApprovalController.php',
            'app/Http/Controllers/SupportRequestController.php',
            'app/Http/Controllers/CommentController.php',
            'app/Http/Controllers/TaskFollowerController.php',
            'app/Http/Controllers/WorkReportController.php',
            'app/Http/Controllers/EmployeeController.php',
            'app/Http/Middleware/DepartmentPermissionMiddleware.php',
        ];

        $updatedCount = 0;

        foreach ($files as $file) {
            if (File::exists($file)) {
                $content = File::get($file);
                $originalContent = $content;

                // Thay thế các pattern phổ biến
                $patterns = [
                    "/abort\(403, '[^']*'\)/",
                    "/return response\(\)->json\(\[[^\]]*\], 403\)/",
                ];

                foreach ($patterns as $pattern) {
                    $content = preg_replace($pattern, "abort(403, '{$newMessage}')", $content);
                }

                if ($content !== $originalContent) {
                    File::put($file, $content);
                    $updatedCount++;
                    $this->line("Đã cập nhật: {$file}");
                }
            }
        }

        $this->info("Hoàn thành! Đã cập nhật {$updatedCount} file.");
        return 0;
    }
}