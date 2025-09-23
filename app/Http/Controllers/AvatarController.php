<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AvatarController extends Controller
{
    /**
     * Upload avatar for current user
     */
    public function upload(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user = Auth::user();
        
        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $avatarPath = $request->file('avatar')->store('avatars', 'public');
        
        // Update user avatar
        $user->update(['avatar' => $avatarPath]);

        return response()->json([
            'success' => true,
            'message' => 'Avatar đã được cập nhật thành công',
            'avatar_url' => $user->avatar_url
        ]);
    }

    /**
     * Upload avatar for specific user (Admin/Director only)
     */
    public function uploadForUser(Request $request, $userId)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $currentUser = Auth::user();
        $targetUser = User::findOrFail($userId);

        // Check permissions
        if (!$currentUser->isAdmin() && !$currentUser->isDirector() && $currentUser->id !== $targetUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền cập nhật avatar cho người dùng này'
            ], 403);
        }

        // Delete old avatar if exists
        if ($targetUser->avatar) {
            Storage::disk('public')->delete($targetUser->avatar);
        }

        // Store new avatar
        $avatarPath = $request->file('avatar')->store('avatars', 'public');
        
        // Update user avatar
        $targetUser->update(['avatar' => $avatarPath]);

        return response()->json([
            'success' => true,
            'message' => 'Avatar đã được cập nhật thành công',
            'avatar_url' => $targetUser->avatar_url
        ]);
    }

    /**
     * Remove avatar
     */
    public function remove(Request $request, $userId = null)
    {
        $currentUser = Auth::user();
        $targetUser = $userId ? User::findOrFail($userId) : $currentUser;

        // Check permissions
        if (!$currentUser->isAdmin() && !$currentUser->isDirector() && $currentUser->id !== $targetUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xóa avatar của người dùng này'
            ], 403);
        }

        // Delete avatar file if exists
        if ($targetUser->avatar) {
            Storage::disk('public')->delete($targetUser->avatar);
            $targetUser->update(['avatar' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Avatar đã được xóa thành công',
            'avatar_url' => $targetUser->avatar_url
        ]);
    }
}