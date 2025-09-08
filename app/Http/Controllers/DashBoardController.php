<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $req)
    {
        $user = auth()->user();
        
        // Giao diện chung cho tất cả users
        $query = Task::with(['assignee', 'creator', 'assignees', 'departments', 'department', 'followers']);
        
        // Filter theo quyền của user
        if ($user->isManager()) {
            // Manager chỉ thấy tasks của phòng ban mình + multi-department tasks có tham gia + tasks được forward
            $query->where(function($q) use ($user) {
                $q->where('department_id', $user->department_id)
                  ->orWhereHas('departments', function($subQ) use ($user) {
                      $subQ->where('department_id', $user->department_id);
                  })
                  ->orWhereHas('assignees', function($subQ) use ($user) {
                      $subQ->where('user_id', $user->id);
                  })
                  ->orWhere('forwarded_to', $user->id)  // Task được forward cho Manager này
                  ->orWhere('creator_id', $user->id);   // Task do Manager này tạo
            });
        } elseif ($user->isEmployee()) {
            // Employee chỉ thấy tasks của mình + tasks được forward
            $query->where(function($q) use ($user) {
                $q->where('assignee_id', $user->id)
                  ->orWhere('creator_id', $user->id)
                  ->orWhereHas('assignees', function($subQ) use ($user) {
                      $subQ->where('user_id', $user->id);
                  })
                  ->orWhere('forwarded_to', $user->id)  // Task được forward cho Employee này
                  ->orWhereHas('followers', function($subQ) use ($user) {
                      $subQ->where('user_id', $user->id);  // Task mà Employee follow
                  });
            });
        }
        // Admin và Director thấy tất cả tasks (không cần filter)
        
        // Filter theo phòng ban (chỉ Admin, Director và Manager có thể filter)
        if (($user->isAdmin() || $user->isDirector() || $user->isManager()) && $req->filled('department_filter')) {
            $departmentId = $req->department_filter;
            $query->where(function($q) use ($departmentId) {
                $q->where('department_id', $departmentId)
                  ->orWhereHas('departments', function($subQ) use ($departmentId) {
                      $subQ->where('department_id', $departmentId);
                  });
            });
        }
        
        // Filter theo trạng thái (hỗ trợ nhiều trạng thái)
        if ($req->has('statuses') && is_array($req->statuses) && count($req->statuses) > 0) {
            $query->whereIn('status', $req->statuses);
        } elseif ($req->filled('status')) {
            $s = $req->status;
            if ($s === 'overdue') {
                $query->where('status','overdue');
            } else {
                $query->where('status',$s);
            }
        }
        
        // Filter theo khoảng thời gian
        if ($req->filled('date_from')) {
            $query->whereDate('created_at', '>=', $req->date_from);
        }
        if ($req->filled('date_to')) {
            $query->whereDate('created_at', '<=', $req->date_to);
        }
        
        // Sắp xếp theo thời gian (mặc định mới nhất trước)
        if ($req->filled('sort')) {
            if ($req->sort === 'newest') {
                $query->latest();
            } elseif ($req->sort === 'oldest') {
                $query->oldest();
            }
        } else {
            $query->latest(); // Mặc định sắp xếp mới nhất
        }

        $tasks = $query->paginate(15);
        
        // Thống kê theo quyền của user
        $statsQuery = Task::query();
        
        if ($user->isManager()) {
            $statsQuery->where(function($q) use ($user) {
                $q->where('department_id', $user->department_id)
                  ->orWhereHas('departments', function($subQ) use ($user) {
                      $subQ->where('department_id', $user->department_id);
                  });
            });
        } elseif ($user->isEmployee()) {
            $statsQuery->where(function($q) use ($user) {
                $q->where('assignee_id', $user->id)
                  ->orWhereHas('assignees', function($subQ) use ($user) {
                      $subQ->where('user_id', $user->id);
                  });
            });
        }
        // Admin và Director thấy stats của tất cả tasks
        
        $stats = [
            'doing'   => (clone $statsQuery)->where('status','in_progress')->count(),
            'completed' => (clone $statsQuery)->where('status','completed')->count(),
            'rejected' => (clone $statsQuery)->where('status','rejected')->count(),
            'overdue' => (clone $statsQuery)->where('status','overdue')->count(),
            'finished' => (clone $statsQuery)->where('status','finished')->count(),
        ];
        
        // Lấy danh sách phòng ban cho filter (chỉ Admin, Director và Manager)
        $departments = collect();
        if ($user->isAdmin() || $user->isDirector()) {
            $departments = Department::orderBy('name')->get();
        } elseif ($user->isManager()) {
            $departments = Department::where('id', $user->department_id)->get();
        }

        // Lấy tasks mà user đang follow (bao gồm cả tasks được assign làm follower)
        $followedTasks = Task::whereHas('followers', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['assignees', 'departments', 'creator'])
            ->where('status', '!=', 'finished')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('welcome', compact('tasks', 'stats', 'departments', 'followedTasks'));
    }
}
