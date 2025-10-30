<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class DebugAvatarCommand extends Command
{
    protected $signature = 'debug:avatar {--clear-cache : Clear all avatar caches}';
    protected $description = 'Debug avatar display issues';

    public function handle()
    {
        $this->info('=== DEBUG AVATAR ISSUE ===');
        $this->newLine();

        // Lấy một vài users để test
        $users = User::whereNotNull('avatar')->take(5)->get();

        if ($users->isEmpty()) {
            $this->warn('No users with avatar found in database.');
            return;
        }

        foreach ($users as $user) {
            $this->info("User ID: {$user->id}");
            $this->info("Name: {$user->name}");
            $this->info("Avatar field: {$user->avatar}");
            
            // Kiểm tra các đường dẫn có thể
            $possiblePaths = [
                'avatars/' . $user->avatar,
                $user->avatar,
                'avatars/' . basename($user->avatar),
            ];
            
            $this->info("Checking possible paths:");
            foreach ($possiblePaths as $path) {
                $exists = Storage::disk('public')->exists($path);
                $status = $exists ? "EXISTS" : "NOT FOUND";
                $this->line("  - storage/app/public/{$path}: {$status}");
                
                if ($exists) {
                    $size = Storage::disk('public')->size($path);
                    $this->line("    Size: " . number_format($size) . " bytes");
                    
                    if ($size > 500000) {
                        $this->warn("    WARNING: File too large (>500KB), will use default avatar");
                    }
                }
            }
            
            // Clear cache cho user này nếu được yêu cầu
            if ($this->option('clear-cache')) {
                $cacheKey = 'avatar_url_' . $user->id;
                Cache::forget($cacheKey);
                $this->info("Cleared cache for user {$user->id}");
            }
            
            // Lấy avatar URL mới
            $avatarUrl = $user->avatar_url;
            $this->info("Avatar URL: " . substr($avatarUrl, 0, 100) . (strlen($avatarUrl) > 100 ? '...' : ''));
            
            // Kiểm tra xem có phải default avatar không
            if (strpos($avatarUrl, 'data:image/svg+xml') === 0) {
                $this->warn("STATUS: Using DEFAULT avatar (SVG)");
            } else {
                $this->info("STATUS: Using REAL avatar file");
            }
            
            $this->line("---");
            $this->newLine();
        }

        // Clear tất cả avatar cache nếu được yêu cầu
        if ($this->option('clear-cache')) {
            $this->info('Clearing all avatar caches...');
            $users = User::all();
            $cleared = 0;
            foreach ($users as $user) {
                $cacheKey = 'avatar_url_' . $user->id;
                if (Cache::has($cacheKey)) {
                    Cache::forget($cacheKey);
                    $cleared++;
                }
            }
            $this->info("Done! Cleared {$cleared} avatar caches.");
        }
    }
}
