<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Base query based on user role
        $baseQuery = Task::query();
        
        if ($user->isManager()) {
            // Manager chỉ thấy tasks của phòng ban mình
            $baseQuery->where(function($q) use ($user) {
                $q->whereHas('assignee', function($subQ) use ($user) {
                    $subQ->where('department_id', $user->department_id);
                })
                ->orWhereHas('creator', function($subQ) use ($user) {
                    $subQ->where('department_id', $user->department_id);
                });
            });
        } elseif (!$user->isAdmin()) {
            // Employee chỉ thấy tasks của mình
            $baseQuery->where(function($q) use ($user) {
                $q->where('assignee_id', $user->id)
                  ->orWhere('creator_id', $user->id);
            });
        }
        // Admin thấy tất cả tasks

        // Summary statistics
        $summary = [
            'total'   => $baseQuery->count(),
            'finished' => $baseQuery->where('status', 'finished')->count(),
            'doing'   => $baseQuery->where('status', 'in_progress')->count(),
            'completed' => $baseQuery->where('status', 'completed')->count(),
            'rejected' => $baseQuery->where('status', 'rejected')->count(),
            'overdue' => $baseQuery->where('status', 'overdue')->count(),
        ];

        // Weekly progress (last 4 weeks)
        $weekly = $this->getWeeklyProgress($baseQuery);

        // Department analysis
        $byDept = $this->getDepartmentAnalysis($user);

        // Top employees
        $topEmployees = $this->getTopEmployees($user);

        // Department report
        $deptReport = $this->getDepartmentReport($user);

        return view('reports.index', compact('summary', 'weekly', 'byDept', 'topEmployees', 'deptReport'));
    }

    private function getWeeklyProgress($baseQuery)
    {
        $weeks = [];
        $values = [];
        
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();
            
            $weekTasks = (clone $baseQuery)
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->where('status', 'finished')
                ->count();
            
            $weeks[] = "Tuần " . (4 - $i);
            $values[] = $weekTasks;
        }

        return [
            'labels' => $weeks,
            'values' => $values,
        ];
    }

    private function getDepartmentAnalysis($user)
    {
        $query = Department::query();
        
        if ($user->isManager()) {
            $query->where('id', $user->department_id);
        }
        
        return $query->withCount(['tasks' => function($q) use ($user) {
            if ($user->isManager()) {
                $q->where(function($subQ) use ($user) {
                    $subQ->whereHas('assignee', function($subSubQ) use ($user) {
                        $subSubQ->where('department_id', $user->department_id);
                    })
                    ->orWhereHas('creator', function($subSubQ) use ($user) {
                        $subSubQ->where('department_id', $user->department_id);
                    });
                });
            } elseif (!$user->isAdmin()) {
                $q->where(function($subQ) use ($user) {
                    $subQ->where('assignee_id', $user->id)
                         ->orWhere('creator_id', $user->id);
                });
            }
        }])->pluck('tasks_count', 'name');
    }

    private function getTopEmployees($user)
    {
        $query = User::query();
        
        if ($user->isManager()) {
            $query->where('department_id', $user->department_id);
        }
        
        $employees = $query->withCount(['assignedTasks as finished_tasks' => function($q) {
            $q->where('status', 'finished');
        }])
        ->withCount(['assignedTasks as total_tasks' => function($q) {
            $q->whereIn('status', ['in_progress', 'completed', 'finished']);
        }])
        ->where('role', 'employee')
        ->orderBy('finished_tasks', 'desc')
        ->limit(3)
        ->get();

        return $employees->map(function($employee) {
            $efficiency = $employee->total_tasks > 0 ? 
                round(($employee->finished_tasks / $employee->total_tasks) * 100) : 0;
            
            $effClass = $efficiency >= 80 ? 'eff-green' : 
                       ($efficiency >= 60 ? 'eff-yellow' : 'eff-red');
            
            return [
                'name' => $employee->name,
                'initials' => strtoupper(substr($employee->name, 0, 2)),
                'color' => $this->getRandomColor(),
                'done' => $employee->finished_tasks,
                'eff' => $efficiency,
                'effClass' => $effClass,
            ];
        })->toArray();
    }

    private function getDepartmentReport($user)
    {
        $query = Department::query();
        
        if ($user->isManager()) {
            $query->where('id', $user->department_id);
        }
        
        $departments = $query->get();

        return $departments->map(function($dept) use ($user) {
            // Simple query: get all tasks for this department
            $deptTasks = Task::where(function($q) use ($dept) {
                $q->whereHas('assignee', function($subQ) use ($dept) {
                    $subQ->where('department_id', $dept->id);
                })
                ->orWhereHas('creator', function($subQ) use ($dept) {
                    $subQ->where('department_id', $dept->id);
                });
            })->get();
            
            // Debug logging
            \Log::info("Department: {$dept->name}, Total tasks: {$deptTasks->count()}");
            \Log::info("Tasks in department: " . $deptTasks->pluck('id', 'status')->toJson());
            
            $total = $deptTasks->count();
            $finished = $deptTasks->where('status', 'finished')->count();
            $doing = $deptTasks->where('status', 'in_progress')->count();
            $overdue = $deptTasks->where('status', 'overdue')->count();
            
            $efficiency = $total > 0 ? round(($finished / $total) * 100) : 0;
            $effClass = $efficiency >= 80 ? 'eff-green' : 
                       ($efficiency >= 60 ? 'eff-yellow' : 'eff-red');
            
            return [
                'name' => $dept->name,
                'total' => $total,
                'finished' => $finished,
                'doing' => $doing,
                'overdue' => $overdue,
                'eff' => $efficiency,
                'effClass' => $effClass,
            ];
        })->toArray();
    }

    private function getRandomColor()
    {
        $colors = ['2563eb', '22c55e', 'facc15', 'ef4444', '8b5cf6', 'f97316'];
        return $colors[array_rand($colors)];
    }
}
