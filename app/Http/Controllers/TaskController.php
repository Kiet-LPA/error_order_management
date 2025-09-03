<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use App\Models\Department;
use App\Models\TaskFollower;
use App\Services\NotificationService;
use App\Rules\FutureDate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function create()
    {
        $user = auth()->user();
        
        if (!$user->isAdmin() && !$user->isDirector() && !$user->isManager()) {
            abort(403, 'Bạn không có quyền giao việc.');
        }
        
        if ($user->isAdmin() || $user->isDirector()) {
            // Admin có thể giao việc cho tất cả users và departments
            $users = User::with('department')->orderBy('name')->get();
            $departments = \App\Models\Department::orderBy('name')->get();
        } else {
            // Manager chỉ có thể giao việc cho Employee
            $users = User::with('department')
                        ->where('role', 'employee')
                        ->where('id', '!=', $user->id) // Không giao việc cho chính mình
                        ->orderBy('name')
                        ->get();
            $departments = \App\Models\Department::orderBy('name')->get();
        }
        
        return view('tasks.create', compact('users', 'departments'));
    }

    public function store(Request $r)
    {
        $user = $r->user();
        
        // Kiểm tra quyền giao việc
        if (!$user->isAdmin() && !$user->isDirector() && !$user->isManager()) {
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
            'deadline'    => ['nullable', 'date', new FutureDate],
            'priority'    => 'nullable|in:low,medium,high',
            'files.*'     => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,webp,mp4,avi,mov,wmv,flv,webm|max:51200',
            'is_recurring' => 'nullable|boolean',
        ]);

        // Kiểm tra quyền theo role
        if ($user->isManager()) {
            // Kiểm tra assignee_id (single user)
            if ($data['assignee_id']) {
                $assignee = User::find($data['assignee_id']);
                if ($assignee && $assignee->role !== 'employee') {
                    abort(403, 'Manager chỉ có thể giao việc cho Employee.');
                }
            }
            
            // Kiểm tra assignee_ids (multi-user)
            if ($data['assignee_ids']) {
                foreach ($data['assignee_ids'] as $assigneeId) {
                    $assignee = User::find($assigneeId);
                    if ($assignee && $assignee->role !== 'employee') {
                        abort(403, 'Manager chỉ có thể giao việc cho Employee.');
                    }
                }
            }
        } elseif ($user->isDirector()) {
            // Director có quyền như Admin - có thể giao việc cho tất cả user (trừ Admin)
            if ($data['assignee_id']) {
                $assignee = User::find($data['assignee_id']);
                if ($assignee && $assignee->isAdmin()) {
                    abort(403, 'Director không thể giao việc cho Admin.');
                }
            }
            
            // Kiểm tra assignee_ids (multi-user)
            if ($data['assignee_ids']) {
                foreach ($data['assignee_ids'] as $assigneeId) {
                    $assignee = User::find($assigneeId);
                    if ($assignee && $assignee->isAdmin()) {
                        abort(403, 'Director không thể giao việc cho Admin.');
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
                // Gửi thông báo cho assignee
                $assignee = User::find($assigneeId);
                if ($assignee) {
                    NotificationService::taskAssigned($task, $user, $assignee);
                }
            }
        } elseif ($r->filled('assignee_id')) {
            // Single user assignment - cũng lưu vào pivot table để thống nhất
            $task->assignees()->attach($r->assignee_id);
            // Gửi thông báo cho assignee
            $assignee = User::find($r->assignee_id);
            if ($assignee) {
                NotificationService::taskAssigned($task, $user, $assignee);
            }
        }

        // Xử lý multi-department assignments
        if ($r->boolean('is_multi_department') && $r->has('department_ids')) {
            foreach ($r->department_ids as $departmentId) {
                $task->departments()->attach($departmentId);
            }
        }

        // Xử lý followers khi tạo task
        if ($r->has('followers') && is_array($r->followers)) {
            $validFollowers = [];
            foreach ($r->followers as $followerId) {
                // Kiểm tra user không phải là người tham gia task
                if ($followerId != $task->creator_id && 
                    $followerId != $task->assignee_id &&
                    !in_array($followerId, $r->assignee_ids ?? [])) {
                    $validFollowers[] = $followerId;
                }
            }
            if (!empty($validFollowers)) {
                $task->followers()->attach($validFollowers);
            }
        }

        // Xử lý approval system cho multi-department tasks
        if ($task->is_multi_department && ($user->isManager() || $user->isDirector())) {
            // Tạo approval requests
            \App\Http\Controllers\TaskApprovalController::createApprovalRequests($task);
            return redirect()->route('task-detail', $task)->with('ok', 'Đã tạo công việc. Task đang chờ phê duyệt từ các Manager.');
        }

        return redirect()->route('task-detail', $task)->with('ok', 'Đã tạo công việc');
    }

    public function show(Task $task)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền xem task
        if ($user->isAdmin() || $user->isDirector()) {
            // Admin và Director có thể xem mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể xem task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể xem task của phòng ban mình.');
            }
        } else {
            // Employee có thể xem task mà họ được assign, tạo, hoặc follow
            $isAssigned = $task->assignee_id === $user->id || 
                         $task->creator_id === $user->id ||
                         $task->assignees->contains('id', $user->id) ||
                         $task->followers->contains('id', $user->id);
            
            if (!$isAssigned) {
                abort(403, 'Bạn chỉ có thể xem task mà bạn được assign, tạo, hoặc theo dõi.');
            }
        }
        
        $task->load(['creator','assignee','assignees','departments','followers.department','approvals.department','approvals.manager']);
        $task->load(['activities' => function($query) {
            $query->orderBy('created_at', 'desc');
        }, 'activities.user']);
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền chỉnh sửa task
        if ($user->isAdmin() || $user->isDirector()) {
            // Admin và Director có thể chỉnh sửa mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể chỉnh sửa task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể chỉnh sửa task của phòng ban mình.');
            }
        } else {
            // Employee chỉ có thể chỉnh sửa task mà họ được assign hoặc tạo
            $isAssigned = $task->assignee_id === $user->id || 
                         $task->creator_id === $user->id ||
                         $task->assignees->contains('id', $user->id);
            
            if (!$isAssigned) {
                abort(403, 'Bạn chỉ có thể chỉnh sửa task mà bạn được assign hoặc tạo.');
            }
        }
        
        // Load relationships
        $task->load(['assignees', 'departments', 'followers.department']);
        
        // Lấy danh sách users có thể assign
        if ($user->isAdmin() || $user->isDirector()) {
            // Admin và Director có thể giao việc cho tất cả users
            $users = User::with('department')->orderBy('name')->get(['id','name','department_id','role']);
        } elseif ($user->isManager()) {
            // Manager chỉ có thể giao việc cho Employee
            $users = User::with('department')
                        ->where('role', 'employee')
                        ->where('id', '!=', $user->id) // Không giao việc cho chính mình
                        ->orderBy('name')
                        ->get(['id','name','department_id','role']);
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
        if ($user->isAdmin() || $user->isDirector()) {
            // Admin và Director có thể cập nhật mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể cập nhật task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể cập nhật task của phòng ban mình.');
            }
        } else {
            // Employee chỉ có thể cập nhật task mà họ được assign hoặc tạo
            $isAssigned = $task->assignee_id === $user->id || 
                         $task->creator_id === $user->id ||
                         $task->assignees->contains('id', $user->id);
            
            if (!$isAssigned) {
                abort(403, 'Bạn chỉ có thể cập nhật task mà bạn được assign hoặc tạo.');
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
            'deadline'    => ['nullable', 'date', new FutureDate],
            'priority'    => 'nullable|in:low,medium,high',
            'status'      => 'required|in:in_progress,completed,rejected,overdue,finished,pending_approval',
            'rejection_reason' => 'nullable|string|max:1000',

            'files.*'     => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,webp,mp4,avi,mov,wmv,flv,webm|max:51200',
            'followers'   => 'nullable|array',
            'followers.*' => 'exists:users,id',
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
            
            // Kiểm tra quyền theo role cho tất cả assignees
            if ($user->isManager()) {
                foreach ($assigneeIds as $assigneeId) {
                    $assignee = User::find($assigneeId);
                    if ($assignee->role !== 'employee') {
                        abort(403, 'Manager chỉ có thể giao việc cho Employee.');
                    }
                }
            }
            
            // Thêm assignments mới
            $task->assignees()->attach($assigneeIds);
            $data['assignee_id'] = null; // Clear single assignee
        } elseif ($request->has('assignee_id') && $request->assignee_id) {
            // Single user assignment
            $data['assignee_id'] = $request->assignee_id;
            
            // Kiểm tra quyền theo role cho assignee
            if ($user->isManager()) {
                $assignee = User::find($data['assignee_id']);
                if ($assignee->role !== 'employee') {
                    abort(403, 'Manager chỉ có thể giao việc cho Employee.');
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



        // Lưu trạng thái cũ để kiểm tra
        $oldStatus = $task->status;
        $oldDeadline = $task->deadline;
        
        $task->update($data);
        
        // Kiểm tra nếu deadline được cập nhật và task đang trễ hạn
        if ($oldStatus === 'overdue' && 
            $oldDeadline && 
            $data['deadline'] && 
            $oldDeadline->ne(\Carbon\Carbon::parse($data['deadline'])) && 
            now()->lt(\Carbon\Carbon::parse($data['deadline']))) {
            
            // Tự động chuyển về trạng thái "Đang làm" nếu deadline mới trong tương lai
            $task->update(['status' => 'in_progress']);
            
            // Ghi log hoạt động
            $task->activities()->create([
                'user_id' => $user->id,
                'action'  => 'updated_status',
                'meta'    => json_encode([
                    'description' => 'Trạng thái tự động chuyển từ "Trễ hạn" về "Đang làm" do deadline được cập nhật',
                    'old_status' => 'overdue',
                    'new_status' => 'in_progress',
                    'old_deadline' => $oldDeadline->format('Y-m-d H:i:s'),
                    'new_deadline' => \Carbon\Carbon::parse($data['deadline'])->format('Y-m-d H:i:s')
                ])
            ]);
        }

        // Xử lý Task Followers (chỉ Admin/Director/Manager)
        if (($user->isAdmin() || $user->isDirector() || $user->isManager()) && $request->has('followers')) {
            // Xóa followers cũ
            $task->followers()->detach();
            
            // Thêm followers mới
            $followerIds = $request->followers;
            $involvedUserIds = collect([
                $task->creator_id,
                $task->assignee_id
            ])->filter();
            $involvedUserIds = $involvedUserIds->merge($task->assignees()->pluck('users.id'));
            
            $validFollowers = [];
            foreach ($followerIds as $followerId) {
                // Kiểm tra không phải là người tham gia task
                if (!$involvedUserIds->contains($followerId)) {
                    // Kiểm tra quyền theo role
                    if ($user->isManager()) {
                        $follower = User::find($followerId);
                        if ($follower->role !== 'employee') {
                            continue; // Bỏ qua nếu không phải Employee
                        }
                    }
                    
                    $validFollowers[] = $followerId;
                }
            }
            
            if (!empty($validFollowers)) {
                $task->followers()->attach($validFollowers);
            }
        }

        // Xử lý approval system cho multi-department tasks
        // Chỉ tạo approval requests khi task được tạo mới hoặc khi user chủ động chuyển sang pending_approval
        if ($task->is_multi_department && ($user->isManager() || $user->isDirector()) && $data['status'] === 'pending_approval') {
            // Chỉ tạo approval requests nếu task chưa có approval hoặc chưa pending
            if (!$task->approvals()->exists() || $task->status !== 'pending_approval') {
                \App\Http\Controllers\TaskApprovalController::createApprovalRequests($task);
            }
        }

        // Gửi thông báo cho assignees và followers
        NotificationService::taskUpdated($task, $user);

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
        if ($user->isAdmin() || $user->isDirector()) {
            // Admin và Director có thể xóa mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể xóa task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể xóa task của phòng ban mình.');
            }
        } else {
            // Employee chỉ có thể xóa task mà họ được assign hoặc tạo
            $isAssigned = $task->assignee_id === $user->id || 
                         $task->creator_id === $user->id ||
                         $task->assignees->contains('id', $user->id);
            
            if (!$isAssigned) {
                abort(403, 'Bạn chỉ có thể xóa task mà bạn được assign hoặc tạo.');
            }
        }
        
        // Load assignees trước khi kiểm tra quyền
        $task->load('assignees');
        
        $task->delete();
        
        return redirect()->route('dashboard')->with('ok', 'Đã xóa công việc');
    }

    public function updateStatus(Task $task, Request $r)
    {
        $user = $r->user();
        
        // Kiểm tra quyền cập nhật trạng thái task
        if ($user->isAdmin() || $user->isDirector()) {
            // Admin và Director có thể cập nhật mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể cập nhật task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể cập nhật task của phòng ban mình.');
            }
        } else {
            // Employee chỉ có thể cập nhật task mà họ được assign hoặc tạo
            $isAssigned = $task->assignee_id === $user->id || 
                         $task->creator_id === $user->id ||
                         $task->assignees->contains('id', $user->id);
            
            if (!$isAssigned) {
                abort(403, 'Bạn chỉ có thể cập nhật task mà bạn được assign hoặc tạo.');
            }
        }
        
        // Load assignees trước khi kiểm tra quyền
        $task->load('assignees');
        
        $status = $r->get('status');
        $rejectionReason = $r->get('rejection_reason');
        $finishNote = $r->get('finish_note');
        
        // Kiểm tra workflow hợp lệ
        $validTransitions = $this->getValidStatusTransitions($task, $user);
        
        if (!in_array($status, $validTransitions)) {
            return back()->withErrors(['status' => 'Không thể chuyển sang trạng thái này']);
        }
        
        // Kiểm tra đặc biệt cho trường hợp chuyển từ overdue về in_progress
        if ($task->status === 'overdue' && $status === 'in_progress') {
            // Bắt buộc phải cập nhật deadline thành ngày trong tương lai
            if (!$task->deadline || $task->deadline->isPast()) {
                return back()->withErrors(['status' => 'Không thể chuyển về "Đang làm" khi deadline vẫn trong quá khứ. Vui lòng cập nhật deadline trước.']);
            }
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
            'finished' => 'Đã kết thúc' . ($finishNote ? ': ' . $finishNote : ''),
            'pending_approval' => 'Đang chờ phê duyệt'
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
                if (in_array($userRole, ['admin', 'director', 'manager'])) {
                    return ['completed', 'approved', 'rejected'];
                }
                break;
                
            case 'completed':
                // Chỉ role cao mới có thể kết thúc hoặc từ chối
                if (in_array($userRole, ['admin', 'director', 'manager'])) {
                    return ['finished', 'rejected'];
                }
                break;
                
            case 'pending_approval':
                // Chỉ role cao mới có thể kết thúc hoặc từ chối task đang chờ phê duyệt
                if (in_array($userRole, ['admin', 'director', 'manager'])) {
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
                // Chỉ có thể chuyển về in_progress nếu deadline đã được cập nhật thành tương lai
                // Hoặc nếu task được đánh dấu là recurring
                if ($task->deadline && $task->deadline->isFuture()) {
                    if ($userRole === 'employee' && $task->assignee_id === $user->id) {
                        return ['in_progress'];
                    }
                    if (in_array($userRole, ['admin', 'director', 'manager'])) {
                        return ['in_progress', 'completed', 'approved', 'rejected'];
                    }
                } else {
                    // Nếu deadline vẫn trong quá khứ, chỉ cho phép cập nhật deadline
                    return [];
                }
                break;
        }
        
        return [];
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        if ($user->isAdmin() || $user->isDirector()) {
            // Admin và Director thấy tất cả tasks
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
        
        if ($user->isAdmin() || $user->isDirector()) {
            // Admin và Director thấy tất cả tasks
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
        
        // Load assignees trước khi kiểm tra quyền
        $task->load('assignees');
        
        // Kiểm tra quyền comment trên task
        if ($user->isAdmin() || $user->isDirector()) {
            // Admin và Director có thể comment trên mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể comment trên task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể comment trên task của phòng ban mình.');
            }
        } else {
            // Employee chỉ có thể comment trên task mà họ được assign hoặc tạo
            $isAssigned = $task->assignee_id === $user->id || 
                         $task->creator_id === $user->id ||
                         $task->assignees->contains('id', $user->id);
            
            if (!$isAssigned) {
                abort(403, 'Bạn chỉ có thể comment trên task mà bạn được assign hoặc tạo.');
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
        
        // Load assignees trước khi kiểm tra quyền
        $task->load('assignees');
        
        // Kiểm tra quyền xem lịch sử task
        if ($user->isAdmin() || $user->isDirector()) {
            // Admin và Director có thể xem lịch sử mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể xem lịch sử task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể xem lịch sử task của phòng ban mình.');
            }
        } else {
            // Employee chỉ có thể xem lịch sử task mà họ được assign hoặc tạo
            $isAssigned = $task->assignee_id === $user->id || 
                         $task->creator_id === $user->id ||
                         $task->assignees->contains('id', $user->id);
            
            if (!$isAssigned) {
                abort(403, 'Bạn chỉ có thể xem lịch sử task mà bạn được assign hoặc tạo.');
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
        
        // Load assignees trước khi kiểm tra quyền
        $task->load('assignees');
        
        // Kiểm tra quyền xóa file
        if ($user->isAdmin() || $user->isDirector()) {
            // Admin và Director có thể xóa file của mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể xóa file của task phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                return response()->json(['success' => false, 'message' => 'Bạn chỉ có thể xóa file của task phòng ban mình.']);
            }
        } else {
            // Employee chỉ có thể xóa file của task mà họ được assign hoặc tạo
            $isAssigned = $task->assignee_id === $user->id || 
                         $task->creator_id === $user->id ||
                         $task->assignees->contains('id', $user->id);
            
            if (!$isAssigned) {
                return response()->json(['success' => false, 'message' => 'Bạn chỉ có thể xóa file của task mà bạn được assign hoặc tạo.']);
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
        
        // Load assignees trước khi kiểm tra quyền
        $task->load('assignees');
        
        // Kiểm tra quyền hoàn tác
        if ($user->isAdmin() || $user->isDirector()) {
            // Admin và Director có thể hoàn tác mọi task
        } elseif ($user->isManager()) {
            // Manager chỉ có thể hoàn tác task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                abort(403, 'Bạn chỉ có thể hoàn tác task của phòng ban mình.');
            }
        } else {
            // Employee chỉ có thể hoàn tác task mà họ được assign hoặc tạo
            $isAssigned = $task->assignee_id === $user->id || 
                         $task->creator_id === $user->id ||
                         $task->assignees->contains('id', $user->id);
            
            if (!$isAssigned) {
                abort(403, 'Bạn chỉ có thể hoàn tác task mà bạn được assign hoặc tạo.');
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

    /**
     * Show Kanban board
     */
    public function kanban()
    {
        $user = auth()->user();
        
        // Tất cả role đều có thể xem Kanban board
        
        // Lấy tasks theo quyền - giống như method index()
        if ($user->isAdmin() || $user->isDirector()) {
            // Admin và Director thấy tất cả tasks
            $query = Task::with(['assignee', 'creator', 'assignees', 'departments']);
        } elseif ($user->isManager()) {
            // Manager chỉ thấy tasks của phòng ban mình
            $query = Task::with(['assignee', 'creator', 'assignees', 'departments'])
                        ->where(function($q) use ($user) {
                            $q->whereHas('assignee', function($subQ) use ($user) {
                                $subQ->where('department_id', $user->department_id);
                            })
                            ->orWhereHas('creator', function($subQ) use ($user) {
                                $subQ->where('department_id', $user->department_id);
                            })
                            ->orWhereHas('assignees', function($subQ) use ($user) {
                                $subQ->where('department_id', $user->department_id);
                            });
                        });
        } else {
            // Employee chỉ thấy tasks của mình
            $query = Task::with(['assignee', 'creator', 'assignees', 'departments'])
                        ->where(function($q) use ($user) {
                            $q->where('assignee_id', $user->id)
                              ->orWhere('creator_id', $user->id)
                              ->orWhereHas('assignees', function($subQ) use ($user) {
                                  $subQ->where('user_id', $user->id);
                              });
                        });
        }
        
        $tasks = $query->orderBy('created_at', 'desc')->get();
        
        // Nhóm tasks theo status
        $kanbanData = [
            'in_progress' => $tasks->where('status', 'in_progress'),
            'completed' => $tasks->where('status', 'completed'),
            'pending_approval' => $tasks->where('status', 'pending_approval'),
            'rejected' => $tasks->where('status', 'rejected'),
            'overdue' => $tasks->where('status', 'overdue'),
            'finished' => $tasks->where('status', 'finished'),
        ];
        
        return view('tasks.kanban', compact('kanbanData'));
    }

    /**
     * Update task status via AJAX (for Kanban drag & drop)
     */
    public function updateStatusAjax(Request $request, Task $task)
    {
        $user = $request->user();
        
        // Kiểm tra quyền cập nhật task
        if (!$user->isAdmin() && !$user->isDirector() && !$user->isManager()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền cập nhật task này.'], 403);
        }
        
        // Kiểm tra quyền theo role
        if ($user->isManager()) {
            // Manager chỉ có thể cập nhật task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                return response()->json(['success' => false, 'message' => 'Bạn chỉ có thể cập nhật task của phòng ban mình.'], 403);
            }
        }
        
        $newStatus = $request->input('status');
        
        // Validate status
        $validStatuses = ['in_progress', 'completed', 'rejected', 'overdue', 'finished', 'pending_approval'];
        if (!in_array($newStatus, $validStatuses)) {
            return response()->json(['success' => false, 'message' => 'Trạng thái không hợp lệ.'], 400);
        }
        
        // Kiểm tra workflow hợp lệ
        $validTransitions = $this->getValidStatusTransitions($task, $user);
        if (!in_array($newStatus, $validTransitions)) {
            return response()->json(['success' => false, 'message' => 'Không thể chuyển sang trạng thái này.'], 400);
        }
        
        // Cập nhật trạng thái
        $task->update(['status' => $newStatus]);
        
        // Ghi log hoạt động
        $statusMessages = [
            'in_progress' => 'Đã giao việc',
            'completed' => 'Đã hoàn thành và gửi duyệt',
            'rejected' => 'Đã từ chối',
            'overdue' => 'Đã trễ hạn',
            'finished' => 'Đã kết thúc',
            'pending_approval' => 'Đang chờ phê duyệt'
        ];
        
        $task->activities()->create([
            'user_id' => $user->id,
            'action'  => 'updated_status',
            'meta'    => $statusMessages[$newStatus] ?? "Cập nhật trạng thái: $newStatus",
        ]);
        
        return response()->json([
            'success' => true, 
            'message' => 'Đã cập nhật trạng thái công việc',
            'task' => $task->load(['assignee', 'creator', 'assignees'])
        ]);
    }
}
