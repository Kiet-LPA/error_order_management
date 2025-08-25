<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use App\Models\Department;
use App\Services\QRGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function create()
    {
        $user = auth()->user();
        
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Bạn không có quyền giao việc.');
        }
        
        if ($user->isAdmin()) {
            // Admin có thể giao việc cho tất cả users và departments
            $users = User::with('department')->orderBy('name')->get();
            $departments = \App\Models\Department::orderBy('name')->get();
        } else {
            // Manager chỉ có thể giao việc cho users cùng phòng ban
            $users = User::with('department')
                        ->where('department_id', $user->department_id)
                        ->where('id', '!=', $user->id) // Không giao việc cho chính mình
                        ->orderBy('name')
                        ->get();
            $departments = \App\Models\Department::where('id', $user->department_id)->get();
        }
        
        return view('tasks.create', compact('users', 'departments'));
    }

    public function store(Request $r)
    {
        $user = $r->user();
        
        // Kiểm tra quyền giao việc
        if (!$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Bạn không có quyền giao việc.');
        }
        
        $data = $r->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'assignee_ids' => 'nullable|array',
            'assignee_ids.*' => 'exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'exists:departments,id',
            'is_multi_department' => 'nullable|boolean',
            'is_multi_user' => 'nullable|boolean',
            'deadline'    => 'nullable|date',
            'priority'    => 'nullable|in:low,medium,high',
            'files.*'     => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,webp,mp4,avi,mov,wmv,flv,webm|max:51200',
            'tracking_code' => 'nullable|string|max:255',
            'is_recurring' => 'nullable|boolean',
        ]);

        // Kiểm tra quyền theo phòng ban
        if ($user->isManager()) {
            // Kiểm tra assignee_id (single user)
            if ($data['assignee_id']) {
                $assignee = User::find($data['assignee_id']);
                if ($assignee && $assignee->department_id !== $user->department_id) {
                    abort(403, 'Bạn chỉ có thể giao việc cho nhân viên cùng phòng ban.');
                }
            }
            
            // Kiểm tra assignee_ids (multi-user)
            if ($data['assignee_ids']) {
                foreach ($data['assignee_ids'] as $assigneeId) {
                    $assignee = User::find($assigneeId);
                    if ($assignee && $assignee->department_id !== $user->department_id) {
                        abort(403, 'Bạn chỉ có thể giao việc cho nhân viên cùng phòng ban.');
                    }
                }
            }
            
            // Kiểm tra department_id (single department)
            if ($data['department_id'] && $data['department_id'] !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể giao việc cho phòng ban của mình.');
            }
            
            // Kiểm tra department_ids (multi-department)
            if ($data['department_ids']) {
                foreach ($data['department_ids'] as $deptId) {
                    if ($deptId !== $user->department_id) {
                        abort(403, 'Bạn chỉ có thể giao việc cho phòng ban của mình.');
                    }
                }
            }
        }

        $data['creator_id'] = $user->id;
        $data['status']     = 'in_progress';
        
        // Xử lý recurring task
        if ($r->boolean('is_recurring') && $r->filled('deadline')) {
            $data['is_recurring'] = true;
            $data['recurring_start_date'] = $r->input('deadline');
            
            // Tính recurring_days từ deadline gốc
            $deadline = \Carbon\Carbon::parse($r->input('deadline'));
            $today = now();
            $data['recurring_days'] = $deadline->diffInDays($today);
            
            // Đảm bảo recurring_days ít nhất là 1
            if ($data['recurring_days'] < 1) {
                $data['recurring_days'] = 1;
            }
        } else {
            $data['is_recurring'] = false;
            $data['recurring_start_date'] = null;
            $data['recurring_days'] = null;
        }

        // Xử lý upload file
        $attachments = [];
        if ($r->hasFile('files')) {
            foreach ($r->file('files') as $file) {
                $originalName = $file->getClientOriginalName();
                
                // Tạo tên file an toàn (tránh trùng lặp)
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
                $safeName = $nameWithoutExt;
                $counter = 1;
                
                // Kiểm tra xem file đã tồn tại chưa
                while (file_exists(public_path('storage/attachments/' . $safeName . '.' . $extension))) {
                    $safeName = $nameWithoutExt . '_' . $counter;
                    $counter++;
                }
                
                $fileName = $safeName . '.' . $extension;
                $file->storeAs('public/attachments', $fileName);
                $attachments[] = [
                    'name' => $originalName,
                    'url' => asset('storage/attachments/' . $fileName),
                    'size' => $file->getSize(),
                ];
            }
        }
        $data['attachments'] = $attachments;

        // Xử lý tracking code
        if ($r->filled('tracking_code')) {
            $data['tracking_code'] = $r->input('tracking_code');
            
            // Tạo QR code từ tracking code đã nhập
            $qrGatewayService = new QRGatewayService();
            $qrCode = $qrGatewayService->generateQRCode($data['tracking_code']);
            
            if ($qrCode) {
                // Lưu QR code vào storage
                $qrFileName = time() . '_qr_' . $data['tracking_code'] . '.png';
                Storage::put('public/qr_codes/' . $qrFileName, $qrCode);
                $data['qr_code'] = asset('storage/qr_codes/' . $qrFileName);
            }
        }

        // Xử lý multi-department
        if ($r->boolean('is_multi_department') && $r->has('department_ids')) {
            $data['is_multi_department'] = true;
            $data['department_id'] = null; // Không set department_id chính nếu là multi-department
        } else {
            $data['is_multi_department'] = false;
        }

        $task = Task::create($data);

        // Xử lý user assignments
        if ($r->boolean('is_multi_user') && $r->has('assignee_ids') && is_array($r->assignee_ids)) {
            // Multi-user assignment
            foreach ($r->assignee_ids as $assigneeId) {
                $task->assignees()->attach($assigneeId);
            }
        } elseif ($r->filled('assignee_id')) {
            // Single user assignment - cũng lưu vào pivot table để thống nhất
            $task->assignees()->attach($r->assignee_id);
        }

        // Xử lý multi-department assignments
        if ($r->boolean('is_multi_department') && $r->has('department_ids')) {
            foreach ($r->department_ids as $departmentId) {
                $task->departments()->attach($departmentId);
            }
        }

        return redirect()->route('task-detail', $task)->with('ok', 'Đã tạo công việc');
    }

    public function show(Task $task)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền xem task
        if ($user->isAdmin()) {
            // Admin có thể xem mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể xem task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể xem task của phòng ban mình.');
            }
        } else {
            // Employee chỉ có thể xem task của mình
            if ($task->assignee_id !== $user->id && $task->creator_id !== $user->id) {
                abort(403, 'Bạn chỉ có thể xem task của mình.');
            }
        }
        
        $task->load(['creator','assignee','assignees']);
        $task->load(['activities' => function($query) {
            $query->orderBy('created_at', 'desc');
        }, 'activities.user']);
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền chỉnh sửa task
        if ($user->isAdmin()) {
            // Admin có thể chỉnh sửa mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể chỉnh sửa task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể chỉnh sửa task của phòng ban mình.');
            }
        } else {
            // Employee chỉ có thể chỉnh sửa task của mình
            if ($task->assignee_id !== $user->id && $task->creator_id !== $user->id) {
                abort(403, 'Bạn chỉ có thể chỉnh sửa task của mình.');
            }
        }
        
        // Load relationships
        $task->load(['assignees', 'departments']);
        
        // Lấy danh sách users có thể assign
        if ($user->isAdmin()) {
            // Admin có thể giao việc cho tất cả users
            $users = User::with('department')->orderBy('name')->get(['id','name','department_id']);
        } elseif ($user->isManager()) {
            // Manager chỉ có thể giao việc cho users cùng phòng ban
            $users = User::with('department')
                        ->where('department_id', $user->department_id)
                        ->where('id', '!=', $user->id) // Không giao việc cho chính mình
                        ->orderBy('name')
                        ->get(['id','name','department_id']);
        } else {
            // Employee không thể giao việc
            $users = collect();
        }
        
        // Lấy danh sách departments
        $departments = Department::orderBy('name')->get(['id', 'name']);
        
        return view('tasks.edit', compact('task', 'users', 'departments'));
    }

    public function update(Request $request, Task $task)
    {
        $user = $request->user();
        
        // Kiểm tra quyền cập nhật task
        if ($user->isAdmin()) {
            // Admin có thể cập nhật mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể cập nhật task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể cập nhật task của phòng ban mình.');
            }
        } else {
            // Employee chỉ có thể cập nhật task của mình
            if ($task->assignee_id !== $user->id && $task->creator_id !== $user->id) {
                abort(403, 'Bạn chỉ có thể cập nhật task của mình.');
            }
        }
        
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'assignee_ids' => 'nullable|array',
            'assignee_ids.*' => 'exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'exists:departments,id',
            'is_multi_user' => 'nullable|boolean',
            'is_multi_department' => 'nullable|boolean',
            'deadline'    => 'nullable|date',
            'priority'    => 'nullable|in:low,medium,high',
            'status'      => 'required|in:in_progress,completed,rejected,overdue,finished',
            'rejection_reason' => 'nullable|string|max:1000',
            'tracking_code' => 'nullable|string|max:255',
            'files.*'     => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,webp,mp4,avi,mov,wmv,flv,webm|max:51200',
        ]);

        // Kiểm tra lý do từ chối khi trạng thái là rejected
        if ($data['status'] === 'rejected' && empty($data['rejection_reason'])) {
            return back()->withErrors(['rejection_reason' => 'Phải nhập lý do từ chối khi trạng thái là "Từ chối".'])->withInput();
        }

        // Xóa lý do từ chối nếu trạng thái không phải là rejected
        if ($data['status'] !== 'rejected') {
            $data['rejection_reason'] = null;
        }

        // Xử lý multi-user và multi-department assignments
        $isMultiUser = $request->has('is_multi_user');
        $isMultiDepartment = $request->has('is_multi_department');

        // Xóa các assignments cũ
        $task->assignees()->detach();
        $task->departments()->detach();

        if ($isMultiUser && $request->has('assignee_ids')) {
            // Multi-user assignment
            $assigneeIds = $request->assignee_ids;
            
            // Kiểm tra quyền theo phòng ban cho tất cả assignees
            if ($user->isManager()) {
                foreach ($assigneeIds as $assigneeId) {
                    $assignee = User::find($assigneeId);
                    if ($assignee->department_id !== $user->department_id) {
                        abort(403, 'Bạn chỉ có thể giao việc cho nhân viên cùng phòng ban.');
                    }
                }
            }
            
            // Thêm assignments mới
            $task->assignees()->attach($assigneeIds);
            $data['assignee_id'] = null; // Clear single assignee
        } elseif ($request->has('assignee_id') && $request->assignee_id) {
            // Single user assignment
            $data['assignee_id'] = $request->assignee_id;
            
            // Kiểm tra quyền theo phòng ban cho assignee
            if ($user->isManager()) {
                $assignee = User::find($data['assignee_id']);
                if ($assignee->department_id !== $user->department_id) {
                    abort(403, 'Bạn chỉ có thể giao việc cho nhân viên cùng phòng ban.');
                }
            }
        } else {
            $data['assignee_id'] = null;
        }

        if ($isMultiDepartment && $request->has('department_ids')) {
            // Multi-department assignment
            $departmentIds = $request->department_ids;
            $task->departments()->attach($departmentIds);
            $data['department_id'] = null; // Clear single department
            $data['is_multi_department'] = true;
        } elseif ($request->has('department_id') && $request->department_id) {
            // Single department assignment
            $data['department_id'] = $request->department_id;
            $data['is_multi_department'] = false;
        } else {
            $data['department_id'] = null;
            $data['is_multi_department'] = false;
        }

        // Xử lý upload file
        $attachments = $task->attachments ?? [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $originalName = $file->getClientOriginalName();
                
                // Tạo tên file an toàn (tránh trùng lặp)
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
                $safeName = $nameWithoutExt;
                $counter = 1;
                
                // Kiểm tra xem file đã tồn tại chưa
                while (file_exists(public_path('storage/attachments/' . $safeName . '.' . $extension))) {
                    $safeName = $nameWithoutExt . '_' . $counter;
                    $counter++;
                }
                
                $fileName = $safeName . '.' . $extension;
                $file->storeAs('public/attachments', $fileName);
                $attachments[] = [
                    'name' => $originalName,
                    'url' => asset('storage/attachments/' . $fileName),
                    'size' => $file->getSize(),
                ];
            }
        }
        $data['attachments'] = $attachments;

        // Cập nhật tracking code và tạo QR code mới
        if ($request->has('tracking_code') && $request->tracking_code !== $task->tracking_code) {
            $data['tracking_code'] = $request->tracking_code;
            
            // Tạo QR code mới từ tracking code
            $qrGatewayService = new QRGatewayService();
            $qrCode = $qrGatewayService->generateQRCode($data['tracking_code']);
            
            if ($qrCode) {
                // Lưu QR code mới vào storage
                $qrFileName = time() . '_qr_' . $data['tracking_code'] . '.png';
                Storage::put('public/qr_codes/' . $qrFileName, $qrCode);
                $data['qr_code'] = asset('storage/qr_codes/' . $qrFileName);
            }
        }

        $task->update($data);

        // Ghi log hoạt động
        $task->activities()->create([
            'user_id' => $user->id,
            'action'  => 'updated_task',
            'meta'    => 'Cập nhật thông tin công việc',
        ]);

        return redirect()->route('task-detail', $task)->with('ok', 'Đã cập nhật công việc');
    }

    public function destroy(Task $task)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền xóa task
        if ($user->isAdmin()) {
            // Admin có thể xóa mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể xóa task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể xóa task của phòng ban mình.');
            }
        } else {
            // Employee chỉ có thể xóa task của mình
            if ($task->assignee_id !== $user->id && $task->creator_id !== $user->id) {
                abort(403, 'Bạn chỉ có thể xóa task của mình.');
            }
        }
        
        $task->delete();
        
        return redirect()->route('dashboard')->with('ok', 'Đã xóa công việc');
    }

    public function updateStatus(Task $task, Request $r)
    {
        $user = $r->user();
        
        // Kiểm tra quyền cập nhật trạng thái task
        if ($user->isAdmin()) {
            // Admin có thể cập nhật mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể cập nhật task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể cập nhật task của phòng ban mình.');
            }
        } else {
            // Employee chỉ có thể cập nhật task của mình
            if ($task->assignee_id !== $user->id && $task->creator_id !== $user->id) {
                abort(403, 'Bạn chỉ có thể cập nhật task của mình.');
            }
        }
        
        $status = $r->get('status');
        $rejectionReason = $r->get('rejection_reason');
        $finishNote = $r->get('finish_note');
        
        // Kiểm tra workflow hợp lệ
        $validTransitions = $this->getValidStatusTransitions($task, $user);
        
        if (!in_array($status, $validTransitions)) {
            return back()->withErrors(['status' => 'Không thể chuyển sang trạng thái này']);
        }
        
        // Cập nhật trạng thái
        $updateData = ['status' => $status];
        if ($status === 'rejected' && $rejectionReason) {
            $updateData['rejection_reason'] = $rejectionReason;
        }
        if ($status === 'finished' && $finishNote) {
            $updateData['finish_note'] = $finishNote;
        }
        
        // Set completed_at khi status = 'completed'
        if ($status === 'completed') {
            $updateData['completed_at'] = now();
        }
        
        $task->update($updateData);
        
        // Tạo activity log với thông tin chi tiết
        $statusMessages = [
            'in_progress' => 'Đã giao việc',
            'completed' => 'Đã hoàn thành và gửi duyệt',
            'rejected' => 'Đã từ chối' . ($rejectionReason ? ': ' . $rejectionReason : ''),
            'overdue' => 'Đã trễ hạn',
            'finished' => 'Đã kết thúc' . ($finishNote ? ': ' . $finishNote : '')
        ];
        
        $task->activities()->create([
            'user_id' => $user->id,
            'action'  => 'updated_status',
            'meta'    => $statusMessages[$status] ?? "Cập nhật trạng thái: $status",
        ]);
        
        return back()->with('ok', 'Đã cập nhật trạng thái công việc');
    }
    
    private function getValidStatusTransitions(Task $task, $user)
    {
        $currentStatus = $task->status;
        $userRole = $user->role;
        
        // Kiểm tra nếu task quá hạn
        if ($task->deadline && $task->deadline->isPast() && $currentStatus !== 'overdue') {
            return ['overdue'];
        }
        
        switch ($currentStatus) {
                
            case 'in_progress':
                // Role thấp có thể hoàn thành và gửi duyệt
                if ($userRole === 'employee' && $task->assignee_id === $user->id) {
                    return ['completed'];
                }
                // Role cao có thể thay đổi trạng thái
                if (in_array($userRole, ['admin', 'manager'])) {
                    return ['completed', 'approved', 'rejected'];
                }
                break;
                
            case 'completed':
                // Chỉ role cao mới có thể kết thúc hoặc từ chối
                if (in_array($userRole, ['admin', 'manager'])) {
                    return ['finished', 'rejected'];
                }
                break;
                
            case 'rejected':
                // Role thấp có thể làm lại và gửi duyệt
                if ($userRole === 'employee' && $task->assignee_id === $user->id) {
                    return ['completed'];
                }
                break;
                
            case 'overdue':
                // Có thể chuyển về in_progress nếu bắt đầu làm
                if ($userRole === 'employee' && $task->assignee_id === $user->id) {
                    return ['in_progress'];
                }
                // Role cao có thể thay đổi trạng thái
                if (in_array($userRole, ['admin', 'manager'])) {
                    return ['in_progress', 'completed', 'approved', 'rejected'];
                }
                break;
        }
        
        return [];
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            // Admin thấy tất cả tasks
            $query = Task::with(['assignee', 'creator']);
        } elseif ($user->isManager()) {
            // Manager chỉ thấy tasks của phòng ban mình
            $query = Task::with(['assignee', 'creator'])
                        ->where(function($q) use ($user) {
                            $q->whereHas('assignee', function($subQ) use ($user) {
                                $subQ->where('department_id', $user->department_id);
                            })
                            ->orWhereHas('creator', function($subQ) use ($user) {
                                $subQ->where('department_id', $user->department_id);
                            });
                        });
        } else {
            // Employee chỉ thấy tasks của mình
            $query = Task::with(['assignee', 'creator'])
                        ->where(function($q) use ($user) {
                            $q->where('assignee_id', $user->id)
                              ->orWhere('creator_id', $user->id);
                        });
        }
        
        if ($request->has('status') && in_array($request->status, ['todo','in_progress','done'])) {
            $query->where('status', $request->status);
        }
        
        $tasks = $query->latest()->paginate(15);
        return view('admin.tasks.index', compact('tasks'));
    }

    // (Tuỳ bạn đã có hay chưa)
    public function myTasks(Request $r)
    {
        $user = $r->user();
        
        if ($user->isAdmin()) {
            // Admin thấy tất cả tasks
            $tasks = Task::with('assignee','creator')->latest()->paginate(10);
            
            $stats = [
                'doing'   => Task::where('status','in_progress')->count(),
                'done'    => Task::where('status','done')->count(),
                'todo'    => Task::where('status','todo')->count(),
                'overdue' => Task::where('status','!=','done')
                                 ->whereNotNull('deadline')->where('deadline','<',now())->count(),
            ];
        } elseif ($user->isManager()) {
            // Manager thấy tasks của phòng ban mình
            $tasks = Task::with('assignee','creator')
                        ->where(function($q) use ($user) {
                            $q->whereHas('assignee', function($subQ) use ($user) {
                                $subQ->where('department_id', $user->department_id);
                            })
                            ->orWhereHas('creator', function($subQ) use ($user) {
                                $subQ->where('department_id', $user->department_id);
                            });
                        })
                        ->latest()
                        ->paginate(10);
            
            $stats = [
                'doing'   => Task::whereHas('assignee', function($q) use ($user) {
                                $q->where('department_id', $user->department_id);
                            })->where('status','in_progress')->count(),
                'done'    => Task::whereHas('assignee', function($q) use ($user) {
                                $q->where('department_id', $user->department_id);
                            })->where('status','done')->count(),
                'todo'    => Task::whereHas('assignee', function($q) use ($user) {
                                $q->where('department_id', $user->department_id);
                            })->where('status','todo')->count(),
                'overdue' => Task::whereHas('assignee', function($q) use ($user) {
                                $q->where('department_id', $user->department_id);
                            })->where('status','!=','done')
                                 ->whereNotNull('deadline')->where('deadline','<',now())->count(),
            ];
        } else {
            // Employee chỉ thấy tasks của mình
            $tasks = Task::with('assignee','creator')
                        ->where('assignee_id', $user->id)
                        ->latest()
                        ->paginate(10);
            
            $stats = [
                'doing'   => Task::where('assignee_id',$user->id)->where('status','in_progress')->count(),
                'done'    => Task::where('assignee_id',$user->id)->where('status','done')->count(),
                'todo'    => Task::where('assignee_id',$user->id)->where('status','todo')->count(),
                'overdue' => Task::where('assignee_id',$user->id)->where('status','!=','done')
                                 ->whereNotNull('deadline')->where('deadline','<',now())->count(),
            ];
        }

        return view('welcome', compact('tasks','stats'));
    }

    // (Tuỳ bạn đã có hay chưa)
    public function comment(Task $task, Request $r)
    {
        $user = $r->user();
        
        // Kiểm tra quyền comment trên task
        if ($user->isAdmin()) {
            // Admin có thể comment trên mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể comment trên task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể comment trên task của phòng ban mình.');
            }
        } else {
            // Employee chỉ có thể comment trên task của mình
            if ($task->assignee_id !== $user->id && $task->creator_id !== $user->id) {
                abort(403, 'Bạn chỉ có thể comment trên task của mình.');
            }
        }
        
        $r->validate([
            'content' => 'required|string|max:1000',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,avi,mov,wmv,flv,webm,pdf,doc,docx,xls,xlsx,ppt,pptx|max:1073741824', // 1GB
        ]);
        
        // Xử lý file upload
        $attachments = [];
        if ($r->hasFile('attachments')) {
            $totalSize = 0;
            $maxTotalSize = 1073741824; // 1GB
            
            foreach ($r->file('attachments') as $file) {
                $totalSize += $file->getSize();
                if ($totalSize > $maxTotalSize) {
                    return back()->withErrors(['attachments' => 'Tổng kích thước file vượt quá 1GB.']);
                }
            }
            
            foreach ($r->file('attachments') as $file) {
                $originalName = $file->getClientOriginalName();
                
                // Tạo tên file an toàn (tránh trùng lặp)
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
                $safeName = $nameWithoutExt;
                $counter = 1;
                
                // Kiểm tra xem file đã tồn tại chưa
                while (file_exists(public_path('storage/task-comments/' . $safeName . '.' . $extension))) {
                    $safeName = $nameWithoutExt . '_' . $counter;
                    $counter++;
                }
                
                $fileName = $safeName . '.' . $extension;
                $filePath = $file->storeAs('public/task-comments', $fileName);
                
                // Đảm bảo thư mục public/storage/task-comments tồn tại
                $publicPath = public_path('storage/task-comments');
                if (!file_exists($publicPath)) {
                    mkdir($publicPath, 0755, true);
                }
                
                // Copy file từ storage sang public storage
                $sourcePath = storage_path('app/public/task-comments/' . $fileName);
                $destPath = $publicPath . '/' . $fileName;
                if (file_exists($sourcePath)) {
                    copy($sourcePath, $destPath);
                }
                
                $attachments[] = [
                    'name' => $originalName,
                    'url' => asset('storage/task-comments/' . $fileName),
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
        }
        
        // Tạo comment với file đính kèm
        $meta = [
            'content' => $r->content,
            'attachments' => $attachments
        ];
        
        $task->activities()->create([
            'user_id' => $user->id,
            'action'  => 'comment',
            'meta'    => json_encode($meta),
        ]);
        
        return back()->with('success', 'Bình luận đã được gửi thành công!');
    }

    public function history(Task $task)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền xem lịch sử task
        if ($user->isAdmin()) {
            // Admin có thể xem lịch sử mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể xem lịch sử task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể xem lịch sử task của phòng ban mình.');
            }
        } else {
            // Employee chỉ có thể xem lịch sử task của mình
            if ($task->assignee_id !== $user->id && $task->creator_id !== $user->id) {
                abort(403, 'Bạn chỉ có thể xem lịch sử task của mình.');
            }
        }
        
        $task->load(['activities' => function($query) {
            $query->orderBy('created_at', 'desc');
        }, 'activities.user']);
        return view('tasks.history', compact('task'));
    }

    public function removeFile(Task $task, Request $request)
    {
        $user = $request->user();
        
        // Kiểm tra quyền xóa file
        if ($user->isAdmin()) {
            // Admin có thể xóa file của mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể xóa file của task phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                return response()->json(['success' => false, 'message' => 'Bạn chỉ có thể xóa file của task phòng ban mình.']);
            }
        } else {
            // Employee chỉ có thể xóa file của task của mình
            if ($task->assignee_id !== $user->id && $task->creator_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'Bạn chỉ có thể xóa file của task của mình.']);
            }
        }
        
        $fileIndex = $request->input('file_index');
        $attachments = $task->attachments ?? [];
        
        if (!isset($attachments[$fileIndex])) {
            return response()->json(['success' => false, 'message' => 'File không tồn tại.']);
        }
        
        $fileToRemove = $attachments[$fileIndex];
        
        // Xóa file khỏi storage
        $filePath = str_replace(asset('storage/'), 'public/', $fileToRemove['url']);
        if (\Storage::exists($filePath)) {
            \Storage::delete($filePath);
        }
        
        // Xóa khỏi array attachments
        unset($attachments[$fileIndex]);
        $attachments = array_values($attachments); // Re-index array
        
        // Cập nhật task
        $task->update(['attachments' => $attachments]);
        
        // Ghi log hoạt động
        $task->activities()->create([
            'user_id' => $user->id,
            'action'  => 'removed_file',
            'meta'    => 'Đã xóa file: ' . $fileToRemove['name'],
        ]);
        
        return response()->json(['success' => true, 'message' => 'Đã xóa file thành công.']);
    }
    
    /**
     * Hoàn tác công việc đã hoàn thành
     */
    public function undoCompletion(Task $task)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền hoàn tác
        if ($user->isAdmin()) {
            // Admin có thể hoàn tác mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể hoàn tác task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể hoàn tác task của phòng ban mình.');
            }
        } else {
            // Employee chỉ có thể hoàn tác task của mình
            if ($task->assignee_id !== $user->id) {
                abort(403, 'Bạn chỉ có thể hoàn tác task của mình.');
            }
        }
        
        // Kiểm tra xem có thể hoàn tác không
        if (!$task->canUndo()) {
            return back()->withErrors(['undo' => 'Không thể hoàn tác công việc này. Chỉ có thể hoàn tác trong vòng 3 tiếng sau khi hoàn thành.']);
        }
        
        // Thực hiện hoàn tác
        if ($task->undoCompletion()) {
            // Ghi log hoạt động
            $task->activities()->create([
                'user_id' => $user->id,
                'action'  => 'undo_completion',
                'meta'    => 'Đã hoàn tác công việc hoàn thành',
            ]);
            
            return back()->with('ok', 'Đã hoàn tác công việc thành công. Công việc đã được chuyển về trạng thái "Đang làm".');
        }
        
        return back()->withErrors(['undo' => 'Không thể hoàn tác công việc. Vui lòng thử lại.']);
    }
}
