<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        
        // Load relationships
        $user->load(['department', 'departments', 'contracts']);
        
        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information (avatar + info + password).
     */
    public function update(Request $request): RedirectResponse
    {
        try {
            $user = $request->user();
            
            \Log::info('Profile update started for user: ' . $user->id);
            \Log::info('Request data: ', $request->all());
            \Log::info('Has file avatar: ' . ($request->hasFile('avatar') ? 'YES' : 'NO'));
            if ($request->hasFile('avatar')) {
                \Log::info('Avatar file details: ' . $request->file('avatar')->getClientOriginalName());
            }
        
        // Validate fields
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ];
        
        // Chỉ validate password nếu user muốn đổi mật khẩu
        if ($request->filled('password')) {
            $rules['current_password'] = ['required', 'string', 'current_password'];
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }
        
        $validated = $request->validate($rules);
        \Log::info('Validation passed');
        
        $messages = [];
        
        // 1. Update Avatar
        if ($request->hasFile('avatar')) {
            \Log::info('Avatar upload started for user: ' . $user->id);
            
            // Delete old avatar if exists
            if ($user->avatar) {
                // Handle both old and new path formats
                if (strpos($user->avatar, 'avatars/') === 0) {
                    Storage::disk('public')->delete($user->avatar);
                } else {
                    Storage::disk('public')->delete('avatars/' . $user->avatar);
                }
                \Log::info('Old avatar deleted: ' . $user->avatar);
            }
            
            // Store new avatar
            $file = $request->file('avatar');
            $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            $file->storeAs('avatars', $filename, 'public');
            
            $user->avatar = $filename;
            $messages[] = 'Ảnh đại diện';
            \Log::info('New avatar saved: ' . $filename);
        }
        
        // Remove avatar if requested
        if ($request->has('remove_avatar') && $request->remove_avatar) {
            if ($user->avatar) {
                // Handle both old and new path formats
                if (strpos($user->avatar, 'avatars/') === 0) {
                    Storage::disk('public')->delete($user->avatar);
                } else {
                    Storage::disk('public')->delete('avatars/' . $user->avatar);
                }
            }
            $user->avatar = null;
            $messages[] = 'Xóa ảnh đại diện';
        }
        
        // 2. Update Profile Information
        $user->name = $validated['name'];
        
        if (isset($validated['email']) && $validated['email'] && $user->email !== $validated['email']) {
            $user->email = $validated['email'];
            $user->email_verified_at = null;
            $messages[] = 'Email';
        } else {
            $messages[] = 'Thông tin';
        }
        
        // 3. Update Password (if provided)
        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
            $messages[] = 'Mật khẩu';
        }
        
        $user->save();
        
            $message = count($messages) > 0 
                ? 'Đã cập nhật: ' . implode(', ', $messages) 
                : 'Cập nhật thành công';

            return Redirect::route('profile.edit')->with('status', 'profile-updated')->with('message', $message);
            
        } catch (\Exception $e) {
            \Log::error('Profile update error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return Redirect::route('profile.edit')
                ->withErrors(['error' => 'Có lỗi xảy ra khi cập nhật hồ sơ: ' . $e->getMessage()])
                ->withInput();
        }
    }


}
