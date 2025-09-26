<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use App\Models\Department;
use App\Models\TaskFollower;
use App\Services\NotificationService;
use App\Services\TaskPermissionService;
use App\Rules\FutureDate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    /**
     * Kiểm tra quyền Manager có thể thao tác với task không
     */
    private function canManagerAccessTask(User $user, Task $task): bool
    {
        // Admin và Director có toàn quyền truy cập
        if ($user->isAdmin() || $user->isDirector()) {
            return true;
        }
        
        // Lấy tất cả phòng ban mà Manager quản lý
        $managerDepartmentIds = $user->departments->pluck('id')->toArray();
        
        // Kiểm tra nếu assignee thuộc phòng ban mà Manager quản lý
        if ($task->assignee && in_array($task->assignee->department_id, $managerDepartmentIds)) {
            return true;
        }
        
        // Kiểm tra nếu creator thuộc phòng ban mà Manager quản lý
        if ($task->creator && in_array($task->creator->department_id, $managerDepartmentIds)) {
            return true;
        }
        
        // Kiểm tra nếu có assignee nào thuộc phòng ban mà Manager quản lý
        if ($task->assignees->whereIn('department_id', $managerDepartmentIds)->count() > 0) {
            return true;
        }
        
        // Kiểm tra nếu task thuộc phòng ban mà Manager quản lý
        if ($task->department_id && in_array($task->department_id, $managerDepartmentIds)) {
            return true;
        }
        
        // Kiểm tra nếu task là multi-department và có phòng ban mà Manager quản lý
        if ($task->is_multi_department && $task->departments->whereIn('id', $managerDepartmentIds)->count() > 0) {
            return true;
        }
        
        // Kiểm tra nếu task đã được forward cho manager này
        if ($task->forwarded_to === $user->id) {
            return true;
        }
        
        return false;
    }

    /**
     * Hiển thị form forward task
     */
    public function showForwardForm(Task $task)
    {
        $user = auth()->user();
        
        // Chỉ Manager, Admin, Director mới có thể forward task
        if (!$user->isAdmin() && !$user->isDirector() && !$user->isManager()) {
            abort(403, 'Không đủ quyền thao tác');
        }
        
        // Kiểm tra quyền forward task
        $canForward = false;
        if ($user->isAdmin() || $user->isDirector()) {
            $canForward = true; // Admin và Director luôn có thể forward
        } elseif ($user->isManager()) {
            $canForward = $user->canViewTask($task); // Manager chỉ cần có quyền xem task
        }
        
        if (!$canForward) {
            abort(403, 'Bạn không có quyền forward task này');
        }
        
        // Lấy danh sách Manager khác (trừ chính họ)
        $managers = User::where('role', 'manager')
                       ->where('id', '!=', $user->id)
                       ->with('department')
                       ->orderBy('name')
                       ->get();
        
        return view('tasks.forward', compact('task', 'managers'));
    }

    /**
     * Xử lý forward task
     */
    public function forward(Request $request, Task $task)
    {
        $user = auth()->user();
        
        // Chỉ Manager, Admin, Director mới có thể forward task
        if (!$user->isAdmin() && !$user->isDirector() && !$user->isManager()) {
            abort(403, 'Không đủ quyền thao tác');
        }
        
        $data = $request->validate([
            'forward_to' => 'required|array|min:1',
            'forward_to.*' => 'exists:users,id',
            'forward_reason' => 'required|string|max:1000',
        ]);
        
        // Kiểm tra tất cả người được forward phải là Manager
        $forwardToUsers = User::whereIn('id', $data['forward_to'])->get();
        foreach ($forwardToUsers as $forwardToUser) {
            if (!$forwardToUser->isManager()) {
                abort(403, 'Chỉ có thể forward task cho Manager khác');
            }
            
            // Kiểm tra quyền forward task cho từng người
            if (!$user->canForwardTask($task, $forwardToUser)) {
                abort(403, "Bạn không có quyền forward task này cho {$forwardToUser->name}");
            }
        }
        
        // Lưu lịch sử forward trước đó (nếu có)
        $previousForwards = $task->forwards()->get()->map(function($forward) {
            return [
                'forwarded_to' => $forward->forwarded_to,
                'forwarded_by' => $forward->forwarded_by,
                'forward_reason' => $forward->forward_reason,
                'forwarded_at' => $forward->forwarded_at,
            ];
        })->toArray();
        
        // Lấy danh sách người đã được forward trước đó
        $previouslyForwarded = $task->forwards()->pluck('forwarded_to')->toArray();
        
        // Tạo forward records cho từng người (chỉ những người chưa được forward)
        $forwardedUserNames = [];
        $newForwardedUsers = [];
        
        foreach ($forwardToUsers as $forwardToUser) {
            // Chỉ tạo record mới nếu chưa được forward trước đó
            if (!in_array($forwardToUser->id, $previouslyForwarded)) {
                $task->forwards()->create([
                    'forwarded_to' => $forwardToUser->id,
                    'forwarded_by' => $user->id,
                    'forward_reason' => $data['forward_reason'],
                    'forwarded_at' => now(),
                ]);
                
                $newForwardedUsers[] = $forwardToUser;
                
                // Gửi thông báo cho Manager mới được forward
                NotificationService::taskForwarded($task, $user, $forwardToUser);
            }
            
            $forwardedUserNames[] = $forwardToUser->name;
        }
        
        // Cập nhật task với thông tin forward mới (giữ lại cho backward compatibility)
        $task->update([
            'forwarded_to' => $data['forward_to'][0], // Lưu người đầu tiên cho compatibility
            'forwarded_by' => $user->id,
            'forward_reason' => $data['forward_reason'],
            'forwarded_at' => now(),
        ]);
        
        // Lưu lịch sử forward vào task_activities (chỉ nếu có người mới)
        if (!empty($newForwardedUsers)) {
            $newForwardedNames = array_map(fn($user) => $user->name, $newForwardedUsers);
            $task->activities()->create([
                'user_id' => $user->id,
                'action' => 'forwarded',
                'description' => "Task được forward từ {$user->name} đến: " . implode(', ', $newForwardedNames),
                'metadata' => [
                    'forward_to' => array_map(fn($user) => $user->id, $newForwardedUsers),
                    'forward_reason' => $data['forward_reason'],
                    'previous_forwards' => $previousForwards,
                ],
            ]);
        }
        
        // Tạo message phù hợp
        if (!empty($newForwardedUsers)) {
            $newForwardedNames = array_map(fn($user) => $user->name, $newForwardedUsers);
            $message = 'Task đã được forward thành công cho: ' . implode(', ', $newForwardedNames);
        } else {
            $message = 'Danh sách người forward đã được cập nhật (không có người mới)';
        }
        
        return redirect()->route('task-detail', $task)->with('success', $message);
    }
    public function create()
    {
        $user = auth()->user();
        
        // Kiểm tra quyền tạo task
        if (!$user->canCreateTask()) {
            abort(403, 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện');
        }
        
        // Lấy danh sách users và departments có thể giao task
        $users = $user->getAssignableUsers();
        $departments = $user->getAssignableDepartments();
        
        return view('tasks.create', compact('users', 'departments'));
    }

    public function store(Request $r)
    {
        $user = $r->user();
        
        // Kiểm tra quyền tạo task
        if (!$user->canCreateTask()) {
            abort(403, 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện');
        }
        
        $data = $r->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'assignee_ids' => 'nullable|array',
            'subtasks' => 'nullable|array',
            'subtasks.*.title' => 'required|string|max:255',
            'subtasks.*.description' => 'nullable|string',
            'subtasks.*.assignee_id' => 'required|exists:users,id',
            'subtasks.*.order' => 'required|integer|min:0',
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
            'enable_followers' => 'nullable|boolean',
            'followers' => 'nullable|array',
            'followers.*' => 'exists:users,id',
        ]);

        // Debug: Log raw request data
        \Log::info('Raw request data:', $r->all());
        \Log::info('Description from request:', ['description' => $r->input('description')]);

        // Bắt buộc phải có phòng ban
        $isMultiDepartment = $r->boolean('is_multi_department');
        if ($isMultiDepartment) {
            if (!$r->has('department_ids') || empty($r->department_ids)) {
                return back()->withErrors(['department_ids' => 'Vui lòng chọn ít nhất một phòng ban'])->withInput();
            }
        } else {
            if (!$r->has('department_id') || !$r->department_id) {
                return back()->withErrors(['department_id' => 'Vui lòng chọn phòng ban'])->withInput();
            }
        }

        // Bắt buộc phải có employee
        $isMultiUser = $r->boolean('is_multi_user');
        if ($isMultiUser) {
            if (!$r->has('assignee_ids') || empty($r->assignee_ids)) {
                return back()->withErrors(['assignee_ids' => 'Vui lòng chọn ít nhất một nhân viên'])->withInput();
            }
        } else {
            if (!$r->has('assignee_id') || !$r->assignee_id) {
                return back()->withErrors(['assignee_id' => 'Vui lòng chọn nhân viên phụ trách'])->withInput();
            }
        }

        // Validate task assignment permissions
        $permissionErrors = TaskPermissionService::validateTaskAssignment($user, $data);
        if (!empty($permissionErrors)) {
            return back()->withErrors($permissionErrors)->withInput();
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

        // Debug description
        \Log::info('Task creation data:', $data);
        \Log::info('Description value:', ['description' => $data['description'] ?? 'NULL']);
        
        // Ensure description is properly handled
        if (isset($data['description']) && $data['description'] === '') {
            $data['description'] = null;
        }
        
        $task = Task::create($data);
        
        // Debug: Check if task was created with description
        \Log::info('Task created with ID:', ['id' => $task->id]);
        \Log::info('Task description after creation:', ['description' => $task->description]);

        // Xử lý user assignments
        if ($r->boolean('is_multi_user') && $r->has('assignee_ids') && is_array($r->assignee_ids)) {
            // Multi-user assignment - loại bỏ duplicate
            $uniqueAssigneeIds = array_unique($r->assignee_ids);
            foreach ($uniqueAssigneeIds as $assigneeId) {
                // Kiểm tra xem đã tồn tại chưa để tránh duplicate
                if (!$task->assignees()->where('user_id', $assigneeId)->exists()) {
                    $task->assignees()->attach($assigneeId);
                    // Gửi thông báo cho assignee
                    $assignee = User::find($assigneeId);
                    if ($assignee) {
                        NotificationService::taskAssigned($task, $user, $assignee);
                    }
                }
            }
        } elseif ($r->filled('assignee_id')) {
            // Single user assignment - cũng lưu vào pivot table để thống nhất
            if (!$task->assignees()->where('user_id', $r->assignee_id)->exists()) {
                $task->assignees()->attach($r->assignee_id);
                // Gửi thông báo cho assignee
                $assignee = User::find($r->assignee_id);
                if ($assignee) {
                    NotificationService::taskAssigned($task, $user, $assignee);
                }
            }
        }

        // Xử lý multi-department assignments
        if ($r->boolean('is_multi_department') && $r->has('department_ids')) {
            // Nếu user chọn manual departments, sử dụng departments từ form
            $task->departments()->sync($r->department_ids);
        } else {
            // Tự động phát hiện và gán phòng ban dựa trên assignees
            $task->updateDepartmentsFromAssignees();
        }

        // Xử lý followers khi tạo task (chỉ khi enable_followers được check)
        if ($r->boolean('enable_followers') && $r->has('followers') && is_array($r->followers)) {
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

        // Xử lý subtasks
        if ($r->has('subtasks') && is_array($r->subtasks)) {
            $this->createSubtasks($task, $r->subtasks);
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
        if (!$user->canViewTask($task)) {
            abort(403, 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện');
        }
        
        // Load relationships
        $task->load(['creator','assignee','assignees','departments','followers.department','approvals.department','approvals.manager','forwardedTo','forwardedBy','subtasks.assignedUser']);
        $task->load(['activities' => function($query) {
            $query->orderBy('created_at', 'desc');
        }, 'activities.user']);
        
        // Ensure description is loaded
        $task->refresh();
        
        // Debug description
        \Log::info('DEBUG TASK DESCRIPTION', [
            'id' => $task->id,
            'attributes' => $task->getAttributes(),
            'description' => $task->description,
        ]);
        
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền chỉnh sửa task
        if (!$user->canEditTask($task)) {
            abort(403, 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện');
        }
        
        // Load relationships
        $task->load(['assignees', 'departments', 'followers.department', 'subtasks.assignedUser']);
        
        // Lấy danh sách users và departments có thể assign
        $users = $user->getAssignableUsers();
        $departments = $user->getAssignableDepartments();
        
        return view('tasks.edit', compact('task', 'users', 'departments'));
    }

    public function update(Request $request, Task $task)
    {
        $user = $request->user();
        
        // Kiểm tra quyền cập nhật task
        if (!$user->canEditTask($task)) {
            abort(403, 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện');
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
            
            // Subtasks validation
            'subtasks' => 'nullable|array',
            'subtasks.*.title' => 'required|string|max:255',
            'subtasks.*.description' => 'nullable|string',
            'subtasks.*.assignee_id' => 'required|exists:users,id',
            'subtasks.*.order' => 'required|integer|min:1',
        ]);

        // Kiểm tra lý do từ chối khi trạng thái là rejected
        if ($data['status'] === 'rejected' && empty($data['rejection_reason'])) {
            return back()->withErrors(['rejection_reason' => 'Phải nhập lý do từ chối khi trạng thái là "Từ chối".'])->withInput();
        }

        // Kiểm tra subtasks assignees
        if ($request->has('subtasks') && is_array($request->subtasks)) {
            $availableUserIds = $task->getAvailableUsersForSubtasks()->pluck('id')->toArray();
            
            \Log::debug('SUBTASK VALIDATION', [
                'subtasks_count' => count($request->subtasks),
                'available_user_ids' => $availableUserIds,
                'subtasks_data' => $request->subtasks
            ]);
            
            foreach ($request->subtasks as $index => $subtask) {
                if (!in_array($subtask['assignee_id'], $availableUserIds)) {
                    \Log::error('SUBTASK VALIDATION FAILED', [
                        'index' => $index,
                        'assignee_id' => $subtask['assignee_id'],
                        'available_user_ids' => $availableUserIds
                    ]);
                    return back()->withErrors([
                        "subtasks.{$index}.assignee_id" => 'Người được giao subtask phải là người tham gia task chính'
                    ])->withInput();
                }
            }
        }

        // Xóa lý do từ chối nếu trạng thái không phải là rejected
        if ($data['status'] !== 'rejected') {
            $data['rejection_reason'] = null;
        }

        // Xử lý multi-user và multi-department assignments
        $isMultiUser = $request->has('is_multi_user');
        $isMultiDepartment = $request->has('is_multi_department');

        // Xử lý assignees - giữ lại người từ phòng ban khác, chỉ thêm/xóa người cùng phòng ban
        if ($isMultiUser && $request->has('assignee_ids')) {
            // Multi-user assignment
            $newAssigneeIds = $request->assignee_ids;
            $currentAssigneeIds = $task->assignees->pluck('id')->toArray();
            
            // Lấy danh sách người từ phòng ban khác (cần giữ lại)
            $otherDeptAssignees = $task->assignees->filter(function($assignee) use ($user) {
                return $user->isManager() && $assignee->department_id !== $user->department_id;
            })->pluck('id')->toArray();
            
            // Lấy danh sách người cùng phòng ban (có thể thay đổi)
            $sameDeptAssignees = $task->assignees->filter(function($assignee) use ($user) {
                return !$user->isManager() || $assignee->department_id === $user->department_id;
            })->pluck('id')->toArray();
            
            // Lấy danh sách người mới từ phòng ban hiện tại
            $newSameDeptAssignees = array_filter($newAssigneeIds, function($assigneeId) use ($user) {
                $assignee = User::find($assigneeId);
                return !$user->isManager() || $assignee->department_id === $user->department_id;
            });
            
            // Kiểm tra quyền cho người mới
            if ($user->isManager()) {
                foreach ($newSameDeptAssignees as $assigneeId) {
                    $assignee = User::find($assigneeId);
                    if ($assignee->role !== 'employee') {
                        abort(403, 'Manager chỉ có thể assign cho Employee trong cùng phòng ban');
                    }
                }
            }
            
            // Xóa người cùng phòng ban cũ và thêm người mới
            $task->assignees()->detach($sameDeptAssignees);
            $task->assignees()->attach($newSameDeptAssignees);
            
            // Không cần attach lại người từ phòng ban khác vì họ vẫn còn trong task
            
            $data['assignee_id'] = null; // Clear single assignee
        } elseif ($request->has('assignee_id') && $request->assignee_id) {
            // Single user assignment
            $data['assignee_id'] = $request->assignee_id;
            
            // Kiểm tra quyền theo role cho assignee
            if ($user->isManager()) {
                $assignee = User::find($data['assignee_id']);
                if ($assignee->role !== 'employee') {
                    abort(403, 'Manager chỉ có thể assign cho Employee trong cùng phòng ban');
                }
            }
            
            // Xóa tất cả assignees khi chuyển sang single user
            $task->assignees()->detach();
        } else {
            // Không có assignee nào được chọn
            $task->assignees()->detach();
        }
        
        // Xử lý departments
        if ($isMultiDepartment && $request->has('department_ids')) {
            // Multi-department assignment - chỉ update khi có department_ids mới
            $departmentIds = $request->department_ids;
            $task->departments()->sync($departmentIds); // Dùng sync thay vì detach + attach
            $data['department_id'] = null; // Clear single department
            $data['is_multi_department'] = true;
        } elseif ($request->has('department_id') && $request->department_id) {
            // Single department assignment
            $task->departments()->detach(); // Xóa multi-department
            $data['department_id'] = $request->department_id;
            $data['is_multi_department'] = false;
        } else {
            // Nếu không có thay đổi gì về department, giữ nguyên
            if (!$request->has('department_ids') && !$request->has('department_id')) {
                // Không thay đổi gì về departments - giữ nguyên
                // Không cần update gì cả
            } else {
                // Có thay đổi nhưng không hợp lệ - giữ nguyên departments hiện tại
                // Không làm gì cả để preserve departments
            }
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
        
        // Tự động phát hiện và gán phòng ban dựa trên assignees
        $task->updateDepartmentsFromAssignees();
        
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

        // Cập nhật subtasks
        if ($request->has('subtasks')) {
            \Log::debug('UPDATING SUBTASKS', [
                'task_id' => $task->id,
                'subtasks_count' => is_array($request->subtasks) ? count($request->subtasks) : 0,
                'subtasks_data' => $request->subtasks,
                'available_users' => $task->getAvailableUsersForSubtasks()->pluck('id')->toArray()
            ]);
            
            try {
                $this->updateSubtasks($task, $request->subtasks);
                
                // Tự động chuyển task về "in_progress" khi edit subtasks
                \Log::debug('CHECKING AUTO STATUS CHANGE', [
                    'task_id' => $task->id,
                    'current_status' => $task->status,
                    'should_change' => in_array($task->status, ['pending_approval', 'completed', 'rejected'])
                ]);
                
                // Kiểm tra xem có subtasks chưa hoàn thành không
                $hasIncompleteSubtasks = !$task->allSubtasksCompleted();
                \Log::debug('SUBTASK COMPLETION CHECK', [
                    'task_id' => $task->id,
                    'has_incomplete_subtasks' => $hasIncompleteSubtasks,
                    'all_subtasks_completed' => $task->allSubtasksCompleted()
                ]);
                
                if (in_array($task->status, ['pending_approval', 'completed', 'rejected']) || 
                    (in_array($task->status, ['in_progress']) && $hasIncompleteSubtasks)) {
                    $oldStatus = $task->status;
                    $task->status = 'in_progress';
                    $task->save();
                    
                    \Log::debug('AUTO STATUS CHANGE', [
                        'task_id' => $task->id,
                        'old_status' => $oldStatus,
                        'new_status' => 'in_progress',
                        'reason' => 'subtasks_edited_with_incomplete_subtasks',
                        'has_incomplete_subtasks' => $hasIncompleteSubtasks
                    ]);
                } else {
                    \Log::debug('NO AUTO STATUS CHANGE', [
                        'task_id' => $task->id,
                        'current_status' => $task->status,
                        'has_incomplete_subtasks' => $hasIncompleteSubtasks,
                        'reason' => 'status_not_in_changeable_list_or_all_subtasks_completed'
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('ERROR UPDATING SUBTASKS', [
                    'task_id' => $task->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return back()->withErrors(['subtasks' => 'Lỗi khi cập nhật subtasks: ' . $e->getMessage()])->withInput();
            }
        }

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
        if (!$user->canDeleteTask($task)) {
            abort(403, 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện');
        }
        
        // Load assignees trước khi kiểm tra quyền
        $task->load('assignees');
        
        $task->delete();
        
        return redirect()->route('dashboard')->with('ok', 'Đã xóa công việc');
    }

    public function updateStatus(Task $task, Request $r)
    {
        \Log::debug('UPDATE STATUS REQUEST', [
            'method' => $r->method(),
            'is_ajax' => $r->ajax(),
            'wants_json' => $r->wantsJson(),
            'input' => $r->all(),
            'json' => $r->json()->all()
        ]);
        
        $user = $r->user();
        $status = $r->input('status') ?? $r->json('status');
        $task->load('assignees');

        // ✅ Cho phép employee resubmit khi task bị rejected
        $isEmployeeResubmitting = $user->role === 'employee' &&
            $task->status === 'rejected' &&
            $status === 'pending_approval' &&
            ($task->assignee_id === $user->id || $task->assignees->contains('id', $user->id));

        if (!$isEmployeeResubmitting && !$user->canApproveTask($task)) {
            abort(403, 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện');
        }

        // ✅ Kiểm tra transition hợp lệ
        $validTransitions = $this->getValidStatusTransitions($task, $user);
        if (!in_array($status, $validTransitions)) {
            if ($r->ajax() || $r->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Không thể chuyển sang trạng thái này'], 400);
            }
            return back()->withErrors(['status' => 'Không thể chuyển sang trạng thái này']);
        }

        // ✅ Subtask validation
        if (in_array($status, ['pending_approval', 'finished']) && $task->hasSubtasks()) {
            \Log::debug('SUBTASK VALIDATION CHECK', [
                'task_id' => $task->id,
                'status' => $status,
                'has_subtasks' => $task->hasSubtasks(),
                'all_subtasks_completed' => $task->allSubtasksCompleted(),
                'subtasks_count' => $task->subtasks()->count(),
                'completed_subtasks_count' => $task->subtasks()->where('status', 'completed')->count()
            ]);
            
            if (!$task->allSubtasksCompleted()) {
                $errorMessage = 'Cần hoàn thành tất cả subtask trước khi gửi duyệt/hoàn thành';
                \Log::debug('SUBTASK VALIDATION FAILED', [
                    'error_message' => $errorMessage,
                    'task_id' => $task->id
                ]);
                
                if ($r->ajax() || $r->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $errorMessage], 400);
                }
                return back()->withErrors(['status' => $errorMessage]);
            }
        }

        $task->status = $status;
        $task->save();
        
        // Tự động reset subtasks khi task bị reject
        if ($status === 'rejected' && $task->hasSubtasks()) {
            $task->resetSubtasksToPending();
            \Log::debug('RESET SUBTASKS ON REJECT', [
                'task_id' => $task->id,
                'subtasks_count' => $task->subtasks()->count()
            ]);
        }

        if ($r->ajax() || $r->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Cập nhật trạng thái thành công']);
        }
        return back()->with('success', 'Cập nhật trạng thái thành công');
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
                if ($userRole === 'employee') {
                    // Kiểm tra cả assignee_id và assignees (multi-user)
                    $isAssigned = $task->assignee_id === $user->id || $task->assignees->contains('id', $user->id);
                    if ($isAssigned) {
                        return ['pending_approval'];
                    }
                }
                // Role cao có thể thay đổi trạng thái
                if (in_array($userRole, ['admin', 'director', 'manager'])) {
                    return ['pending_approval', 'finished', 'rejected'];
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
                if ($userRole === 'employee') {
                    // Kiểm tra cả assignee_id và assignees (multi-user)
                    $isAssigned = $task->assignee_id === $user->id || $task->assignees->contains('id', $user->id);
                    if ($isAssigned) {
                        return ['pending_approval'];
                    }
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
                        return ['in_progress', 'pending_approval', 'finished', 'rejected'];
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
            if (!$user->department_id) {
                abort(403, 'Bạn chưa được phân phòng ban. Vui lòng liên hệ quản trị viên.');
            }
            
            $query = Task::with(['assignee', 'creator'])
                        ->where(function($q) use ($user) {
                            $q->whereHas('assignee', function($subQ) use ($user) {
                                $subQ->where('department_id', $user->department_id);
                            })
                            ->orWhereHas('creator', function($subQ) use ($user) {
                                $subQ->where('department_id', $user->department_id);
                            })
                            ->orWhere('forwarded_to', $user->id); // Manager cũng thấy task được forward cho họ
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
            // Manager thấy tasks của phòng ban mình + tasks được forward
            $tasks = Task::with('assignee','creator')
                        ->where(function($q) use ($user) {
                            $q->whereHas('assignee', function($subQ) use ($user) {
                                $subQ->where('department_id', $user->department_id);
                            })
                            ->orWhereHas('creator', function($subQ) use ($user) {
                                $subQ->where('department_id', $user->department_id);
                            })
                            ->orWhere('forwarded_to', $user->id)  // Task được forward cho Manager này
                            ->orWhere('creator_id', $user->id);   // Task do Manager này tạo
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
            // Employee chỉ thấy tasks của mình + tasks được forward
            $tasks = Task::with('assignee','creator')
                        ->where(function($q) use ($user) {
                            $q->where('assignee_id', $user->id)
                              ->orWhere('creator_id', $user->id)
                              ->orWhereHas('assignees', function($subQ) use ($user) {
                                  $subQ->where('user_id', $user->id);
                              })
                              ->orWhere('forwarded_to', $user->id)  // Task được forward cho Employee này
                              ->orWhereHas('followers', function($subQ) use ($user) {
                                  $subQ->where('user_id', $user->id);  // Task mà Employee follow
                              });
                        })
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
            $query = Task::with(['assignee', 'creator', 'assignees', 'departments', 'department']);
        } elseif ($user->isManager()) {
            // Manager chỉ thấy tasks của phòng ban mình
            $query = Task::with(['assignee', 'creator', 'assignees', 'departments', 'department'])
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
            $query = Task::with(['assignee', 'creator', 'assignees', 'departments', 'department'])
                        ->where(function($q) use ($user) {
                            $q->where('assignee_id', $user->id)
                              ->orWhere('creator_id', $user->id)
                              ->orWhereHas('assignees', function($subQ) use ($user) {
                                  $subQ->where('user_id', $user->id);
                              });
                        });
        }
        
        $tasks = $query->orderBy('created_at', 'desc')->get();
        
        // Tự động cập nhật status cho tasks quá hạn
        $now = now();
        foreach ($tasks as $task) {
            if ($task->deadline && $task->deadline < $now && 
                !in_array($task->status, ['finished', 'rejected', 'overdue'])) {
                $task->update(['status' => 'overdue']);
                $task->status = 'overdue'; // Cập nhật trong collection
            }
        }
        
        // Nhóm tasks theo status (sau khi đã cập nhật)
        $kanbanData = [
            'in_progress' => $tasks->where('status', 'in_progress'),
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
        
        // Load assignees trước khi kiểm tra quyền
        $task->load('assignees');
        
        // Get status from request (support both form data and JSON)
        $newStatus = $request->input('status') ?? $request->json('status');
        
        // Debug logging
        \Log::debug('UPDATE STATUS AJAX DEBUG', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'user_role' => $user->role,
            'current_status' => $task->status,
            'requested_status' => $newStatus,
            'assignee_id' => $task->assignee_id,
            'assignees' => $task->assignees->pluck('id')->toArray(),
            'canApproveTask' => $user->canApproveTask($task)
        ]);
        
        // Kiểm tra quyền cập nhật task
        $isEmployeeResubmitting = $user->role === 'employee' && 
                                 $task->status === 'rejected' && 
                                 $newStatus === 'pending_approval' &&
                                 ($task->assignee_id === $user->id || $task->assignees->contains('id', $user->id));
        
        if (!$user->isAdmin() && !$user->isDirector() && !$user->isManager() && !$isEmployeeResubmitting) {
            return response()->json(['success' => false, 'message' => 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện'], 403);
        }
        
        // Kiểm tra quyền theo role
        if ($user->isManager()) {
            // Manager chỉ có thể cập nhật task của phòng ban mình
            if ($task->assignee && $task->assignee->department_id !== $user->department_id &&
                $task->creator && $task->creator->department_id !== $user->department_id) {
                return response()->json(['success' => false, 'message' => 'Không đủ quyền thao tác, vui lòng gửi yêu cầu đến tài khoản cao hơn thực hiện'], 403);
            }
        }
        
        // Validate status
        $validStatuses = ['in_progress', 'rejected', 'overdue', 'finished', 'pending_approval'];
        if (!in_array($newStatus, $validStatuses)) {
            return response()->json(['success' => false, 'message' => 'Trạng thái không hợp lệ.'], 400);
        }
        
        // Kiểm tra workflow hợp lệ
        $validTransitions = $this->getValidStatusTransitions($task, $user);
        
        \Log::debug('VALID TRANSITIONS AJAX', [
            'validTransitions' => $validTransitions,
            'isStatusValid' => in_array($newStatus, $validTransitions)
        ]);
        
        if (!in_array($newStatus, $validTransitions)) {
            return response()->json(['success' => false, 'message' => 'Không thể chuyển sang trạng thái này.'], 400);
        }
        
        // Kiểm tra subtasks khi hoàn thành task
        if (in_array($newStatus, ['pending_approval', 'finished']) && $task->hasSubtasks()) {
            if (!$task->allSubtasksCompleted()) {
                // Lấy danh sách subtasks chưa hoàn thành
                $incompleteSubtasks = $task->subtasks()->where('status', '!=', 'completed')->get();
                $incompleteTitles = $incompleteSubtasks->pluck('title')->toArray();
                
                $message = 'Không thể hoàn thành công việc. Còn các bước thực hiện chưa hoàn thành: "' . implode('", "', $incompleteTitles) . '"';
                
                return response()->json([
                    'success' => false, 
                    'message' => $message
                ], 400);
            }
        }
        
        // Cập nhật trạng thái với logic đầy đủ
        $updateData = ['status' => $newStatus];
        
        // Set completed_at khi status = 'pending_approval' (khi employee hoàn thành task)
        if ($newStatus === 'pending_approval') {
            $updateData['completed_at'] = now();
        }
        
        // Clear completed_at khi chuyển từ pending_approval sang status khác
        if ($task->status === 'pending_approval' && $newStatus !== 'pending_approval') {
            $updateData['completed_at'] = null;
        }
        
        // Reset subtasks khi task bị reject
        if ($newStatus === 'rejected' && $task->hasSubtasks()) {
            $task->subtasks()->update([
                'status' => 'todo',
                'completed_at' => null
            ]);
        }
        
        // Xử lý trạng thái overdue
        if ($newStatus === 'in_progress') {
            // Nếu chuyển về in_progress, kiểm tra lại deadline
            if ($task->deadline && $task->deadline->isPast()) {
                // Nếu deadline vẫn trong quá khứ, chuyển thành overdue
                $updateData['status'] = 'overdue';
            }
        }
        
        $task->update($updateData);
        
        // Ghi log hoạt động
        $statusMessages = [
            'in_progress' => 'Đã giao việc',
            'pending_approval' => 'Đã hoàn thành và gửi duyệt',
            'rejected' => 'Đã từ chối',
            'overdue' => 'Đã trễ hạn',
            'finished' => 'Đã kết thúc'
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

    /**
     * Tạo subtasks cho task
     */
    private function createSubtasks(Task $task, array $subtasksData)
    {
        $availableUsers = $task->getAvailableUsersForSubtasks();
        $availableUserIds = $availableUsers->pluck('id')->toArray();

        foreach ($subtasksData as $subtaskData) {
            // Validate assigned user is in available users
            if (!in_array($subtaskData['assignee_id'], $availableUserIds)) {
                throw new \InvalidArgumentException('Người được giao subtask phải là người tham gia task chính');
            }

            $task->subtasks()->create([
                'title' => $subtaskData['title'],
                'description' => $subtaskData['description'] ?? null,
                'assignee_id' => $subtaskData['assignee_id'],
                'order' => $subtaskData['order'],
                'status' => 'todo'
            ]);
        }
    }

    /**
     * Cập nhật subtasks cho task
     */
    private function updateSubtasks(Task $task, array $subtasksData)
    {
        \Log::debug('updateSubtasks called', [
            'task_id' => $task->id,
            'subtasks_data_count' => count($subtasksData)
        ]);
        
        // Lưu trạng thái hiện tại của subtasks (để giữ lại completed status)
        $existingSubtasks = $task->subtasks()->get()->keyBy('title');
        \Log::debug('existing subtasks', [
            'count' => $existingSubtasks->count(),
            'titles' => $existingSubtasks->keys()->toArray()
        ]);
        
        // Chỉ xóa subtasks cũ sau khi tạo mới thành công
        $oldSubtasks = $task->subtasks()->get();
        
        // Tạo subtasks mới
        if (!empty($subtasksData)) {
            $availableUsers = $task->getAvailableUsersForSubtasks();
            $availableUserIds = $availableUsers->pluck('id')->toArray();
            
            \Log::debug('available users for subtasks', [
                'user_ids' => $availableUserIds,
                'users_count' => $availableUsers->count()
            ]);

            $newSubtasks = [];
            foreach ($subtasksData as $index => $subtaskData) {
                \Log::debug('processing subtask', [
                    'index' => $index,
                    'title' => $subtaskData['title'] ?? 'no title',
                    'assignee_id' => $subtaskData['assignee_id'] ?? 'no assignee'
                ]);
                
                // Validate assigned user is in available users
                if (!in_array($subtaskData['assignee_id'], $availableUserIds)) {
                    \Log::error('Invalid assignee for subtask', [
                        'assignee_id' => $subtaskData['assignee_id'],
                        'available_user_ids' => $availableUserIds
                    ]);
                    throw new \InvalidArgumentException('Người được giao subtask phải là người tham gia task chính');
                }

                // Giữ nguyên status nếu subtask đã được hoàn thành trước đó
                $status = 'todo';
                $completedAt = null;
                
                if ($existingSubtasks->has($subtaskData['title'])) {
                    $existingSubtask = $existingSubtasks->get($subtaskData['title']);
                    \Log::debug('Found existing subtask', [
                        'title' => $subtaskData['title'],
                        'existing_status' => $existingSubtask->status,
                        'existing_completed_at' => $existingSubtask->completed_at
                    ]);
                    
                    if ($existingSubtask->status === 'completed') {
                        $status = 'completed';
                        $completedAt = $existingSubtask->completed_at;
                    }
                }
                
                \Log::debug('Creating subtask with status', [
                    'title' => $subtaskData['title'],
                    'status' => $status,
                    'completed_at' => $completedAt
                ]);

                $newSubtasks[] = [
                    'title' => $subtaskData['title'],
                    'description' => $subtaskData['description'] ?? null,
                    'assignee_id' => $subtaskData['assignee_id'],
                    'order' => $subtaskData['order'],
                    'status' => $status,
                    'completed_at' => $completedAt
                ];
            }
            
            // Xóa subtasks cũ
            $task->subtasks()->delete();
            
            // Tạo subtasks mới
            foreach ($newSubtasks as $subtaskData) {
                $task->subtasks()->create($subtaskData);
            }
            
            \Log::debug('Successfully updated subtasks', [
                'deleted_count' => $oldSubtasks->count(),
                'created_count' => count($newSubtasks)
            ]);
        } else {
            // Nếu không có subtasks mới, xóa tất cả
            $task->subtasks()->delete();
        }
    }

    /**
     * Hoàn thành subtask
     */
    public function completeSubtask(Request $request, Task $task, $subtaskId)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền xem task
        if (!$user->canViewTask($task)) {
            abort(403, 'Không đủ quyền thao tác');
        }

        $subtask = $task->subtasks()->findOrFail($subtaskId);
        
        // Kiểm tra user có quyền hoàn thành subtask này không
        if (!$subtask->canBeCompletedBy($user)) {
            abort(403, 'Bạn không có quyền hoàn thành subtask này');
        }

        $subtask->markAsCompleted();

        // Ghi log hoạt động
        $task->activities()->create([
            'user_id' => $user->id,
            'action' => 'subtask_completed',
            'description' => "Đã hoàn thành bước thực hiện: {$subtask->title}",
            'metadata' => [
                'subtask_id' => $subtask->id,
                'subtask_title' => $subtask->title
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã hoàn thành bước thực hiện',
            'subtask' => $subtask,
            'task_progress' => $task->getSubtasksProgressPercentage()
        ]);
    }

    /**
     * Cập nhật trạng thái subtask
     */
    public function updateSubtaskStatus(Request $request, Task $task, $subtaskId)
    {
        $user = auth()->user();
        
        // Kiểm tra quyền xem task
        if (!$user->canViewTask($task)) {
            abort(403, 'Không đủ quyền thao tác');
        }

        $subtask = $task->subtasks()->findOrFail($subtaskId);
        $status = $request->validate(['status' => 'required|in:todo,in_progress,completed'])['status'];

        // Kiểm tra quyền thay đổi trạng thái
        if ($status === 'completed' && !$subtask->canBeCompletedBy($user)) {
            abort(403, 'Bạn không có quyền hoàn thành subtask này');
        }

        $oldStatus = $subtask->status;
        $subtask->update(['status' => $status]);

        // Ghi log hoạt động
        $statusMessages = [
            'todo' => 'Chờ thực hiện',
            'in_progress' => 'Đang thực hiện',
            'completed' => 'Đã hoàn thành'
        ];

        $task->activities()->create([
            'user_id' => $user->id,
            'action' => 'subtask_status_updated',
            'description' => "Cập nhật trạng thái bước thực hiện '{$subtask->title}': {$statusMessages[$oldStatus]} → {$statusMessages[$status]}",
            'metadata' => [
                'subtask_id' => $subtask->id,
                'subtask_title' => $subtask->title,
                'old_status' => $oldStatus,
                'new_status' => $status
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật trạng thái bước thực hiện',
            'subtask' => $subtask,
            'task_progress' => $task->getSubtasksProgressPercentage()
        ]);
    }
}

