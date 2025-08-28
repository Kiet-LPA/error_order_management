<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaskFollowerController extends Controller
{
    /**
     * Thêm follower cho task
     */
    public function add(Request $request, Task $task): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = auth()->user();
        $targetUser = User::find($request->user_id);

        // Kiểm tra quyền (chỉ Admin/Manager)
        if (!$user->isAdmin() && !$user->isManager()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền thêm Task Follower'], 403);
        }

        // Kiểm tra role-based permission
        if ($user->isManager()) {
            if ($targetUser->role !== 'employee') {
                return response()->json(['success' => false, 'message' => 'Manager chỉ có thể thêm Employee làm follower'], 403);
            }
        }

        // Kiểm tra follower đã tồn tại
        if ($task->followers()->where('id', $request->user_id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Người dùng đã là follower của task này'], 400);
        }

        // Kiểm tra user không phải là người tham gia task
        if ($request->user_id == $task->creator_id || 
            $request->user_id == $task->assignee_id ||
            $task->assignees()->where('user_id', $request->user_id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Không thể thêm người tham gia task làm follower'], 400);
        }

        $task->followers()->attach($request->user_id);

        return response()->json(['success' => true, 'message' => 'Đã thêm Task Follower thành công']);
    }

    /**
     * Xóa follower khỏi task
     */
    public function remove(Request $request, Task $task): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = auth()->user();
        $targetUser = User::find($request->user_id);

        // Kiểm tra quyền (chỉ Admin/Manager)
        if (!$user->isAdmin() && !$user->isManager()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xóa Task Follower'], 403);
        }

        // Kiểm tra role-based permission
        if ($user->isManager()) {
            if ($targetUser->role !== 'employee') {
                return response()->json(['success' => false, 'message' => 'Manager chỉ có thể xóa Employee khỏi follower'], 403);
            }
        }

        $task->followers()->detach($request->user_id);

        return response()->json(['success' => true, 'message' => 'Đã xóa Task Follower thành công']);
    }

    /**
     * Lấy danh sách users có thể thêm làm follower
     */
    public function available(Task $task): JsonResponse
    {
        $user = auth()->user();
        
        // Kiểm tra quyền (chỉ Admin/Manager)
        if (!$user->isAdmin() && !$user->isManager()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xem danh sách này'], 403);
        }

        $availableUsers = $task->getAvailableFollowers();

        // Áp dụng role-based filtering
        if ($user->isManager()) {
            $filteredAvailableUsers = collect();
            foreach ($availableUsers as $departmentId => $users) {
                $filteredUsers = $users->where('role', 'employee');
                if ($filteredUsers->isNotEmpty()) {
                    $filteredAvailableUsers->put($departmentId, $filteredUsers);
                }
            }
            $availableUsers = $filteredAvailableUsers;
        }

        $usersByDepartment = [];
        foreach ($availableUsers as $departmentId => $users) {
            $departmentName = $users->first()->department ? $users->first()->department->name : 'Không có phòng ban';
            $usersByDepartment[] = [
                'department_id' => $departmentId,
                'department_name' => $departmentName,
                'users' => $users->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'role' => $user->role,
                        'department' => $user->department ? $user->department->name : 'N/A'
                    ];
                })
            ];
        }

        return response()->json(['success' => true, 'users_by_department' => $usersByDepartment]);
    }

    /**
     * Lấy danh sách followers hiện tại (cho AJAX reload)
     */
    public function list(Task $task)
    {
        return view('components.task-followers-list', ['followers' => $task->followers()->with('department')->get()]);
    }

    /**
     * User tự follow task
     */
    public function follow(Request $request, Task $task): JsonResponse
    {
        $user = auth()->user();

        // Kiểm tra đã follow chưa
        if ($task->followers()->where('id', $user->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Bạn đã theo dõi task này rồi'], 400);
        }

        // Kiểm tra user không phải là người tham gia task
        if ($user->id == $task->creator_id || 
            $user->id == $task->assignee_id ||
            $task->assignees()->where('user_id', $user->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Không thể theo dõi task mà bạn đang tham gia'], 400);
        }

        $task->followers()->attach($user->id);

        return response()->json(['success' => true, 'message' => 'Đã theo dõi task thành công']);
    }

    /**
     * User tự unfollow task
     */
    public function unfollow(Request $request, Task $task): JsonResponse
    {
        $user = auth()->user();

        // Kiểm tra đang follow chưa
        if (!$task->followers()->where('id', $user->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Bạn chưa theo dõi task này'], 400);
        }

        $task->followers()->detach($user->id);

        return response()->json(['success' => true, 'message' => 'Đã bỏ theo dõi task thành công']);
    }

    /**
     * Lấy users theo department (cho dynamic loading)
     */
    public function getUsersByDepartment(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            return response()->json(['success' => false, 'message' => 'Không có quyền'], 403);
        }

        $query = User::with('department')->orderBy('department_id')->orderBy('name');

        // Áp dụng role-based filtering
        if ($user->isManager()) {
            $query->where('role', 'employee');
        }

        $users = $query->get()->groupBy('department_id');

        $usersByDepartment = [];
        foreach ($users as $departmentId => $departmentUsers) {
            $departmentName = $departmentUsers->first()->department ? $departmentUsers->first()->department->name : 'Không có phòng ban';
            $usersByDepartment[] = [
                'department_id' => $departmentId,
                'department_name' => $departmentName,
                'users' => $departmentUsers->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'role' => $user->role,
                        'department' => $user->department ? $user->department->name : 'N/A'
                    ];
                })
            ];
        }

        return response()->json(['success' => true, 'users_by_department' => $usersByDepartment]);
    }

    /**
     * Lấy departments theo user (cho dynamic loading)
     */
    public function getDepartmentsByUser(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            return response()->json(['success' => false, 'message' => 'Không có quyền'], 403);
        }

        $query = \App\Models\Department::with(['users' => function($q) use ($user) {
            if ($user->isManager()) {
                $q->where('role', 'employee');
            }
        }])->orderBy('name');

        $departments = $query->get();

        return response()->json(['success' => true, 'departments' => $departments]);
    }
}
