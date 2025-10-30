<?php
/**
 * Script để debug và fix vấn đề avatar
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

echo "=== DEBUG AVATAR ISSUE ===\n\n";

// Lấy một vài users để test
$users = User::whereNotNull('avatar')->take(5)->get();

foreach ($users as $user) {
    echo "User ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Avatar field: {$user->avatar}\n";
    
    // Kiểm tra các đường dẫn có thể
    $possiblePaths = [
        'avatars/' . $user->avatar,
        $user->avatar,
        'avatars/' . basename($user->avatar),
    ];
    
    echo "Checking possible paths:\n";
    foreach ($possiblePaths as $path) {
        $exists = Storage::disk('public')->exists($path);
        echo "  - storage/app/public/{$path}: " . ($exists ? "EXISTS" : "NOT FOUND") . "\n";
        
        if ($exists) {
            $size = Storage::disk('public')->size($path);
            echo "    Size: " . number_format($size) . " bytes\n";
            
            if ($size > 500000) {
                echo "    WARNING: File too large (>500KB), will use default avatar\n";
            }
        }
    }
    
    // Clear cache cho user này
    $cacheKey = 'avatar_url_' . $user->id;
    Cache::forget($cacheKey);
    
    // Lấy avatar URL mới
    $avatarUrl = $user->avatar_url;
    echo "Avatar URL: {$avatarUrl}\n";
    
    // Kiểm tra xem có phải default avatar không
    if (strpos($avatarUrl, 'data:image/svg+xml') === 0) {
        echo "STATUS: Using DEFAULT avatar (SVG)\n";
    } else {
        echo "STATUS: Using REAL avatar file\n";
    }
    
    echo "---\n\n";
}

// Clear tất cả avatar cache
echo "Clearing all avatar caches...\n";
$users = User::all();
foreach ($users as $user) {
    $cacheKey = 'avatar_url_' . $user->id;
    Cache::forget($cacheKey);
}

echo "Done! All avatar caches cleared.\n";
