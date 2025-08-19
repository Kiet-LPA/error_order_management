<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $req)
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            // Admin thấy tasks theo từng phòng ban
            $departments = \App\Models\Department::with(['users', 'tasks.assignee', 'tasks.creator'])->get();
            
            $departmentTasks = [];
            foreach ($departments as $department) {
                $query = Task::with(['assignee', 'creator'])
                            ->where(function($q) use ($department) {
                                $q->whereHas('assignee', function($subQ) use ($department) {
                                    $subQ->where('department_id', $department->id);
                                })
                                ->orWhereHas('creator', function($subQ) use ($department) {
                                    $subQ->where('department_id', $department->id);
                                });
                            });
                
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
                
                // Sắp xếp theo thời gian
                if ($req->filled('sort')) {
                    if ($req->sort === 'newest') {
                        $query->latest();
                    } elseif ($req->sort === 'oldest') {
                        $query->oldest();
                    }
                } else {
                    $query->latest(); // Mặc định sắp xếp mới nhất
                }
                
                $departmentTasks[$department->id] = $query->get();
            }
            
            $stats = [
                'doing'   => Task::where('status','in_progress')->count(),
                'completed' => Task::where('status','completed')->count(),
                'rejected' => Task::where('status','rejected')->count(),
                'overdue' => Task::where('status','overdue')->count(),
                'finished' => Task::where('status','finished')->count(),
            ];
            
            return view('welcome', compact('departments', 'departmentTasks', 'stats'));
            
        } elseif ($user->isManager()) {
            // Manager chỉ thấy tasks của phòng ban mình
            $query = Task::with(['assignee','creator'])
                        ->where(function($q) use ($user) {
                            $q->whereHas('assignee', function($subQ) use ($user) {
                                $subQ->where('department_id', $user->department_id);
                            })
                            ->orWhereHas('creator', function($subQ) use ($user) {
                                $subQ->where('department_id', $user->department_id);
                            });
                        });
            
            $stats = [
                'doing'   => Task::whereHas('assignee', function($q) use ($user) {
                                $q->where('department_id', $user->department_id);
                            })->where('status','in_progress')->count(),
                'completed' => Task::whereHas('assignee', function($q) use ($user) {
                                $q->where('department_id', $user->department_id);
                            })->where('status','completed')->count(),
                'rejected' => Task::whereHas('assignee', function($q) use ($user) {
                                $q->where('department_id', $user->department_id);
                            })->where('status','rejected')->count(),
                'overdue' => Task::whereHas('assignee', function($q) use ($user) {
                                $q->where('department_id', $user->department_id);
                            })->where('status','overdue')->count(),
                'finished' => Task::whereHas('assignee', function($q) use ($user) {
                                $q->where('department_id', $user->department_id);
                            })->where('status','finished')->count(),
            ];
            
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
            
            // Sắp xếp theo thời gian
            if ($req->filled('sort')) {
                if ($req->sort === 'newest') {
                    $query->latest();
                } elseif ($req->sort === 'oldest') {
                    $query->oldest();
                }
            } else {
                $query->latest(); // Mặc định sắp xếp mới nhất
            }

            $tasks = $query->paginate(10);
            return view('welcome', compact('tasks','stats'));
            
        } else {
            // Employee chỉ thấy tasks của mình
            $query = Task::with(['assignee','creator'])
                        ->where(function($q) use ($user) {
                            $q->where('assignee_id', $user->id)
                              ->orWhere('creator_id', $user->id);
                        });
            
            $stats = [
                'doing'   => Task::where('assignee_id',$user->id)->where('status','in_progress')->count(),
                'completed' => Task::where('assignee_id',$user->id)->where('status','completed')->count(),
                'rejected' => Task::where('assignee_id',$user->id)->where('status','rejected')->count(),
                'overdue' => Task::where('assignee_id',$user->id)->where('status','overdue')->count(),
                'finished' => Task::where('assignee_id',$user->id)->where('status','finished')->count(),
            ];
            
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
            
            // Sắp xếp theo thời gian
            if ($req->filled('sort')) {
                if ($req->sort === 'newest') {
                    $query->latest();
                } elseif ($req->sort === 'oldest') {
                    $query->oldest();
                }
            } else {
                $query->latest(); // Mặc định sắp xếp mới nhất
            }

            $tasks = $query->paginate(10);
            return view('welcome', compact('tasks','stats'));
        }
    }
}
