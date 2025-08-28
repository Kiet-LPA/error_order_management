<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Lấy danh sách thông báo
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $notifications = NotificationService::getNotifications($user, 20);
        
        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => NotificationService::getUnreadCount($user)
        ]);
    }

    /**
     * Đánh dấu thông báo đã đọc
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $request->validate([
            'notification_id' => 'required|integer'
        ]);

        $user = auth()->user();
        $success = NotificationService::markAsRead($request->notification_id, $user);
        
        return response()->json([
            'success' => $success,
            'unread_count' => NotificationService::getUnreadCount($user)
        ]);
    }

    /**
     * Đánh dấu tất cả thông báo đã đọc
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = auth()->user();
        NotificationService::markAllAsRead($user);
        
        return response()->json([
            'success' => true,
            'unread_count' => 0
        ]);
    }

    /**
     * Lấy số thông báo chưa đọc
     */
    public function getUnreadCount(): JsonResponse
    {
        $user = auth()->user();
        
        return response()->json([
            'success' => true,
            'unread_count' => NotificationService::getUnreadCount($user)
        ]);
    }
}
