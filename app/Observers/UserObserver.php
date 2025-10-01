<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserObserver
{
    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Đồng bộ avatar khi có thay đổi (không throw exception)
        if ($user->wasChanged('avatar')) {
            try {
                $this->syncAvatar($user);
            } catch (\Exception $e) {
                // Không để observer làm fail việc lưu user
                \Log::error("UserObserver sync failed but user saved: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Đồng bộ avatar sang checkin và rentalcar (safe mode)
     */
    private function syncAvatar(User $user): void
    {
        $avatarPath = $user->avatar ? "/storage/avatars/{$user->avatar}" : null;
        
        // Đồng bộ sang checkin_new (nếu database và cột tồn tại)
        if ($this->databaseAndColumnExists('checkin_new', 'users', 'avatar')) {
            try {
                DB::connection('mysql')->update("
                    UPDATE checkin_new.users 
                    SET avatar = ? 
                    WHERE email = ?
                ", [$avatarPath, $user->email]);
                
                \Log::info("Synced avatar to checkin for user: {$user->email}");
            } catch (\Exception $e) {
                \Log::warning("Failed to sync avatar to checkin: " . $e->getMessage());
            }
        }
        
        // Đồng bộ sang rental_car_management (nếu database và cột tồn tại)
        if ($this->databaseAndColumnExists('rental_car_management', 'users', 'avatar')) {
            try {
                DB::connection('mysql')->update("
                    UPDATE rental_car_management.users 
                    SET avatar = ? 
                    WHERE email = ?
                ", [$avatarPath, $user->email]);
                
                \Log::info("Synced avatar to rentalcar for user: {$user->email}");
            } catch (\Exception $e) {
                \Log::warning("Failed to sync avatar to rentalcar: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Kiểm tra database và cột có tồn tại không
     */
    private function databaseAndColumnExists($database, $table, $column): bool
    {
        try {
            $result = DB::connection('mysql')->select("
                SELECT COUNT(*) as count
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = ? 
                AND TABLE_NAME = ? 
                AND COLUMN_NAME = ?
            ", [$database, $table, $column]);
            
            return $result[0]->count > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}

