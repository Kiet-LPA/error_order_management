<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskApproval;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaskApprovalController extends Controller
{
    /**
     * Show pending approvals for a manager
     */
    public function index()
    {
        $user = auth()->user();
        
        if (!$user->isManager()) {
            abort(403, 'Chỉ Manager mới có thể xem danh sách phê duyệt');
        }

        $pendingApprovals = TaskApproval::where('manager_id', $user->id)
            ->where('status', 'pending')
            ->with(['task', 'department'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('task-approvals.index', compact('pendingApprovals'));
    }

    /**
     * Show approval details
     */
    public function show(TaskApproval $approval)
    {
        $user = auth()->user();
        
        if ($approval->manager_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Bạn không có quyền xem phê duyệt này');
        }

        return view('task-approvals.show', compact('approval'));
    }

    /**
     * Approve a task
     */
    public function approve(Request $request, TaskApproval $approval): JsonResponse
    {
        $user = auth()->user();
        
        if ($approval->manager_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền phê duyệt task này'
            ], 403);
        }

        if ($approval->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Phê duyệt này đã được xử lý'
            ], 400);
        }

        $approval->update([
            'status' => 'approved',
            'comment' => $request->input('comment'),
            'approved_at' => now()
        ]);

        // Check if task is fully approved
        $task = $approval->task;
        if ($task->isFullyApproved()) {
            $task->update(['status' => 'in_progress']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã phê duyệt task thành công'
        ]);
    }

    /**
     * Reject a task
     */
    public function reject(Request $request, TaskApproval $approval): JsonResponse
    {
        $user = auth()->user();
        
        if ($approval->manager_id !== $user->id && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền từ chối task này'
            ], 403);
        }

        if ($approval->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Phê duyệt này đã được xử lý'
            ], 400);
        }

        $approval->update([
            'status' => 'rejected',
            'comment' => $request->input('comment')
        ]);

        // Update task status to rejected
        $task = $approval->task;
        $task->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Đã từ chối task thành công'
        ]);
    }

    /**
     * Create approval requests for a task
     */
    public static function createApprovalRequests(Task $task)
    {
        if (!$task->is_multi_department) {
            return; // Single department tasks don't need approval
        }

        $departments = $task->departments;
        
        foreach ($departments as $department) {
            // Find managers in this department
            $managers = User::where('department_id', $department->id)
                           ->where('role', 'manager')
                           ->get();

            foreach ($managers as $manager) {
                // Create approval request
                TaskApproval::create([
                    'task_id' => $task->id,
                    'department_id' => $department->id,
                    'manager_id' => $manager->id,
                    'status' => 'pending'
                ]);
            }
        }

        // Set task status to pending approval
        $task->update(['status' => 'pending_approval']);
    }
}
