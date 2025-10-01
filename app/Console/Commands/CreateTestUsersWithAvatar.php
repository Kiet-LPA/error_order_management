<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CreateTestUsersWithAvatar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:create-users-with-avatar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tạo users test có avatar để demo trong checkin và rentalcar';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Bắt đầu tạo users test có avatar...');
        $this->newLine();
        
        $usersData = [
            [
                'name' => 'Nguyễn Văn Test',
                'email' => 'testuser1@hpfoods.com',
                'username' => 'test_user1',
                'role' => 'employee',
                'avatar_letter' => 'N',
                'color' => '#667eea'
            ],
            [
                'name' => 'Trần Thị Demo',
                'email' => 'testuser2@hpfoods.com',
                'username' => 'test_user2',
                'role' => 'employee',
                'avatar_letter' => 'T',
                'color' => '#28a745'
            ],
            [
                'name' => 'Lê Văn Quản Lý',
                'email' => 'testmanager@hpfoods.com',
                'username' => 'test_manager',
                'role' => 'manager',
                'avatar_letter' => 'L',
                'color' => '#ffc107'
            ],
            [
                'name' => 'Phạm Văn Xe',
                'email' => 'testemp1@company.com',
                'username' => 'test_emp1',
                'role' => 'employee',
                'avatar_letter' => 'P',
                'color' => '#dc3545'
            ],
            [
                'name' => 'Hoàng Thị Thuê',
                'email' => 'testemp2@company.com',
                'username' => 'test_emp2',
                'role' => 'employee',
                'avatar_letter' => 'H',
                'color' => '#6f42c1'
            ],
            [
                'name' => 'Vũ Văn Quản Lý',
                'email' => 'testmgr@company.com',
                'username' => 'test_mgr',
                'role' => 'manager',
                'avatar_letter' => 'V',
                'color' => '#17a2b8'
            ],
        ];
        
        $created = 0;
        $updated = 0;
        
        foreach ($usersData as $userData) {
            try {
                // Tìm hoặc tạo user
                $user = User::where('email', $userData['email'])->first();
                
                if ($user) {
                    $this->warn("  → User {$userData['email']} đã tồn tại, đang cập nhật...");
                    $user->update([
                        'name' => $userData['name'],
                    ]);
                    $updated++;
                } else {
                    $this->info("  → Tạo user mới: {$userData['email']}");
                    $user = User::create([
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'password' => Hash::make('123456'),
                        'role' => $userData['role'],
                    ]);
                    $created++;
                }
                
                // Tạo avatar SVG
                $avatarFilename = $this->generateAvatar(
                    $userData['avatar_letter'],
                    $userData['color'],
                    $userData['username']
                );
                
                // Cập nhật avatar path
                $user->avatar = $avatarFilename;
                $user->save();
                
                $this->line("     ✅ Avatar: {$avatarFilename}");
                
            } catch (\Exception $e) {
                $this->error("  ✗ Lỗi tạo user {$userData['email']}: " . $e->getMessage());
            }
        }
        
        $this->newLine();
        $this->info("📊 Kết quả:");
        $this->info("  - Tạo mới: {$created} users");
        $this->info("  - Cập nhật: {$updated} users");
        
        $this->newLine();
        $this->info("🔄 Đang đồng bộ avatar sang checkin và rentalcar...");
        $this->call('sync:avatars');
        
        $this->newLine();
        $this->info("✅ Hoàn thành! Bạn có thể:");
        $this->info("   1. Đăng nhập vào các hệ thống với password: 123456");
        $this->info("   2. Xem avatar tại:");
        $this->info("      - Checkin: http://localhost/checkin/reports.php");
        $this->info("      - RentalCar: http://localhost/rentalcar/admin/rentals.php");
        
        return Command::SUCCESS;
    }
    
    /**
     * Tạo avatar SVG và lưu vào storage
     */
    private function generateAvatar($letter, $color, $username)
    {
        // Tạo nội dung SVG
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">
    <defs>
        <linearGradient id="grad-{$username}" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:{$color};stop-opacity:1" />
            <stop offset="100%" style="stop-color:{$color};stop-opacity:0.7" />
        </linearGradient>
    </defs>
    <rect width="200" height="200" fill="url(#grad-{$username})"/>
    <text x="50%" y="50%" dominant-baseline="central" text-anchor="middle" 
          font-family="Arial, sans-serif" font-size="80" fill="white" font-weight="bold">
        {$letter}
    </text>
</svg>
SVG;
        
        // Tạo thư mục avatars nếu chưa có
        if (!Storage::disk('public')->exists('avatars')) {
            Storage::disk('public')->makeDirectory('avatars');
        }
        
        // Lưu file SVG
        $filename = $username . '_avatar.svg';
        Storage::disk('public')->put('avatars/' . $filename, $svg);
        
        return $filename;
    }
}

