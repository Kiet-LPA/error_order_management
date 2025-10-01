<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SyncUserAvatars extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:avatars 
                          {--force : Force sync all users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Đồng bộ avatar từ hệ thống chính sang checkin và rentalcar';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Bắt đầu đồng bộ avatars...');
        
        $users = User::all();
        $synced = 0;
        $failed = 0;
        
        foreach ($users as $user) {
            try {
                // Đồng bộ sang checkin_new
                $this->syncToCheckin($user);
                
                // Đồng bộ sang rentalcar
                $this->syncToRentalcar($user);
                
                $synced++;
                $this->info("✓ Đồng bộ thành công: {$user->name} ({$user->email})");
            } catch (\Exception $e) {
                $failed++;
                $this->error("✗ Lỗi đồng bộ {$user->email}: " . $e->getMessage());
            }
        }
        
        $this->newLine();
        $this->info("Hoàn thành!");
        $this->info("- Thành công: {$synced}");
        $this->info("- Thất bại: {$failed}");
        
        return Command::SUCCESS;
    }
    
    /**
     * Đồng bộ user vào checkin database
     */
    private function syncToCheckin(User $user)
    {
        try {
            // Kiểm tra user đã tồn tại trong checkin chưa
            $existingUser = DB::connection('mysql')->select("
                SELECT id, avatar FROM checkin_new.users 
                WHERE email = ? LIMIT 1
            ", [$user->email]);
            
            $avatarPath = $user->avatar ? "/storage/avatars/{$user->avatar}" : null;
            
            if (!empty($existingUser)) {
                // Cập nhật avatar
                DB::connection('mysql')->update("
                    UPDATE checkin_new.users 
                    SET avatar = ? 
                    WHERE email = ?
                ", [$avatarPath, $user->email]);
            }
        } catch (\Exception $e) {
            // Bỏ qua nếu database checkin_new không tồn tại
            $this->warn("  → Checkin DB không khả dụng: " . $e->getMessage());
        }
    }
    
    /**
     * Đồng bộ user vào rentalcar database
     */
    private function syncToRentalcar(User $user)
    {
        try {
            // Kiểm tra user đã tồn tại trong rentalcar chưa
            $existingUser = DB::connection('mysql')->select("
                SELECT id, avatar FROM rentalcar.users 
                WHERE email = ? LIMIT 1
            ", [$user->email]);
            
            $avatarPath = $user->avatar ? "/storage/avatars/{$user->avatar}" : null;
            
            if (!empty($existingUser)) {
                // Cập nhật avatar
                DB::connection('mysql')->update("
                    UPDATE rentalcar.users 
                    SET avatar = ? 
                    WHERE email = ?
                ", [$avatarPath, $user->email]);
            }
        } catch (\Exception $e) {
            // Bỏ qua nếu database rentalcar không tồn tại
            $this->warn("  → Rentalcar DB không khả dụng: " . $e->getMessage());
        }
    }
}

