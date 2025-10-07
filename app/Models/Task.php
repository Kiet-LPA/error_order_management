<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description', 
        'status',
        'priority',
        'attachments',
        'qr_code',
        'department_id',
        'assignee_id',
        'creator_id',
        'rejection_reason',
        'finish_note',
        'deadline',
        'is_recurring',
        'recurring_start_date',
        'recurring_days',
        'last_reset_date',
        'completed_at',
        'is_multi_department',
        'forwarded_to',
        'forwarded_by',
        'forward_reason',
        'forwarded_at'
    ];
    
    protected $casts = [
        'deadline' => 'datetime',
        'attachments' => 'array',
        'recurring_start_date' => 'date',
        'forwarded_at' => 'datetime',
        'last_reset_date' => 'date',
        'completed_at' => 'datetime',
        'is_multi_department' => 'boolean',
        'is_multi_user' => 'boolean',
        'is_recurring' => 'boolean',
    ];

    // Relationships
    public function department()
    { 
        return $this->belongsTo(Department::class); 
    }
    
    public function assignee()
    { 
        return $this->belongsTo(User::class, 'assignee_id'); 
    }
    
    public function creator()
    { 
        return $this->belongsTo(User::class, 'creator_id'); 
    }
    
    public function forwardedTo()
    { 
        return $this->belongsTo(User::class, 'forwarded_to'); 
    }
    
    public function forwardedBy()
    { 
        return $this->belongsTo(User::class, 'forwarded_by'); 
    }
    
    public function activities()
    { 
        return $this->hasMany(TaskActivity::class); 
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }

    public function files()
    {
        return $this->hasMany(TaskFile::class);
    }

    // Subtasks relationship
    public function subtasks()
    {
        return $this->hasMany(TaskSubtask::class)->ordered();
    }

    // Multi-user assignments
    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_assignees', 'task_id', 'user_id')
                    ->withTimestamps();
    }

    public function assignedUsers()
    {
        return $this->hasMany(TaskAssignee::class);
    }

    // Task submissions relationship
    public function submissions()
    {
        return $this->hasMany(TaskSubmission::class);
    }

    // Multi-department assignments
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_tasks', 'task_id', 'department_id')
                    ->withTimestamps();
    }

    public function departmentTasks()
    {
        return $this->hasMany(DepartmentTask::class);
    }

    // Task forwards
    public function forwards()
    {
        return $this->hasMany(TaskForward::class)->orderBy('forwarded_at', 'desc');
    }

    public function latestForward()
    {
        return $this->hasOne(TaskForward::class)->latest('forwarded_at');
    }

    // Multi-assigned tasks (for users)
    public function multiAssignedTasks()
    {
        return $this->hasMany(TaskAssignee::class);
    }

    // Task Followers relationships
    public function followers()
    {
        return $this->belongsToMany(User::class, 'task_followers');
    }

    public function followersUsers()
    {
        return $this->belongsToMany(User::class, 'task_followers');
    }

    public function isFollowedBy(User $user): bool
    {
        return $this->followers()->where('users.id', $user->id)->exists();
    }

    // Task Approvals relationships
    public function approvals()
    {
        return $this->hasMany(TaskApproval::class);
    }

    public function pendingApprovals()
    {
        return $this->approvals()->where('status', 'pending');
    }

    public function approvedApprovals()
    {
        return $this->approvals()->where('status', 'approved');
    }

    public function rejectedApprovals()
    {
        return $this->approvals()->where('status', 'rejected');
    }

    public function isFullyApproved()
    {
        if (!$this->is_multi_department) {
            return true; // Single department tasks don't need approval
        }

        $totalDepartments = $this->departments()->count();
        $approvedDepartments = $this->approvedApprovals()->count();
        
        return $totalDepartments === $approvedDepartments;
    }

    public function hasPendingApprovals()
    {
        return $this->pendingApprovals()->exists();
    }

    public function canBeActivated()
    {
        // Admin can activate any task
        if (auth()->user()->isAdmin()) {
            return true;
        }

        // For multi-department tasks, need all approvals
        if ($this->is_multi_department) {
            return $this->isFullyApproved();
        }

        return true;
    }

    public function getAvailableFollowers()
    {
        // Lấy tất cả user IDs đã tham gia task (creator, assignee, assignees, followers)
        $involvedUserIds = collect([
            $this->creator_id,
            $this->assignee_id
        ])->filter();
        
        $involvedUserIds = $involvedUserIds->merge($this->assignees()->pluck('users.id'));
        $involvedUserIds = $involvedUserIds->merge($this->followers()->pluck('id'));

        return User::whereNotIn('id', $involvedUserIds->unique())
                  ->with('department')
                  ->orderBy('department_id')
                  ->orderBy('name')
                  ->get()
                  ->groupBy('department_id');
    }
    
    /**
     * Kiểm tra xem task có cần deadline mới không
     */
    public function needsNewDeadline(): bool
    {
        if (!$this->is_recurring || !$this->recurring_days) {
            return false;
        }
        
        // Nếu chưa có last_reset_date, sử dụng recurring_start_date
        $startDate = $this->last_reset_date ?? $this->recurring_start_date;
        
        if (!$startDate) {
            return false;
        }
        
        // Tính ngày hiện tại
        $today = now()->startOfDay();
        
        // Tính ngày deadline tiếp theo
        $nextDeadline = $startDate->addDays($this->recurring_days);
        
        // Nếu ngày hiện tại đã vượt qua deadline tiếp theo, cần reset
        return $today->gte($nextDeadline);
    }
    
    /**
     * Tính deadline mới dựa trên recurring_days
     */
    public function calculateNextDeadline(): \Carbon\Carbon
    {
        if (!$this->is_recurring || !$this->recurring_days) {
            return $this->deadline;
        }
        
        // Sử dụng last_reset_date nếu có, nếu không thì dùng recurring_start_date
        $startDate = $this->last_reset_date ?? $this->recurring_start_date;
        
        if (!$startDate) {
            return $this->deadline;
        }
        
        return $startDate->copy()->addDays($this->recurring_days);
    }
    
    /**
     * Cập nhật deadline và last_reset_date
     */
    public function updateRecurringDeadline(): bool
    {
        if (!$this->is_recurring || !$this->needsNewDeadline()) {
            return false;
        }
        
        // Cập nhật deadline mới
        $this->deadline = $this->calculateNextDeadline();
        
        // Cập nhật last_reset_date
        $this->last_reset_date = now()->toDateString();
        
        // Reset status về 'in_progress'
        $this->status = 'in_progress';
        
        // Xóa rejection_reason và finish_note
        $this->rejection_reason = null;
        $this->finish_note = null;
        
        return $this->save();
    }
    
    /**
     * Kiểm tra có thể hoàn tác không (trong vòng 3 tiếng)
     */
    public function canUndo(): bool
    {
        // Có thể hoàn tác khi task đã gửi duyệt (pending_approval) và có completed_at
        if ($this->status !== 'pending_approval' || !$this->completed_at) {
            return false;
        }
        
        // Kiểm tra xem đã qua 3 tiếng chưa
        $threeHoursAgo = now()->subHours(3);
        return $this->completed_at->gt($threeHoursAgo);
    }
    
    /**
     * Chuyển status từ 'pending_approval' về 'in_progress' (rút lại yêu cầu duyệt)
     */
    public function undoCompletion(): bool
    {
        if (!$this->canUndo()) {
            return false;
        }
        
        // Chuyển status về 'in_progress' (rút lại yêu cầu duyệt)
        $this->status = 'in_progress';
        
        // Xóa completed_at
        $this->completed_at = null;
        
        // Xóa finish_note
        $this->finish_note = null;
        
        return $this->save();
    }

    /**
     * Lấy danh sách users được assign cho task này
     */
    public function getAssignedUsers()
    {
        $users = collect();
        
        // Thêm assignee chính
        if ($this->assignee) {
            $users->push($this->assignee);
        }
        
        // Thêm multi-assignees
        $users = $users->merge($this->assignees);
        
        return $users->unique('id');
    }

    /**
     * Kiểm tra user đã submit task chưa
     */
    public function hasUserSubmitted(User $user): bool
    {
        $submission = $this->submissions()->where('user_id', $user->id)->first();
        return $submission && $submission->isSubmitted();
    }

    /**
     * Lấy submission của user
     */
    public function getUserSubmission(User $user): ?TaskSubmission
    {
        return $this->submissions()->where('user_id', $user->id)->first();
    }

    /**
     * Submit task cho user
     */
    public function submitByUser(User $user): bool
    {
        // Tạo hoặc cập nhật submission
        $submission = $this->submissions()->firstOrCreate(
            ['user_id' => $user->id],
            ['status' => 'pending']
        );
        
        $submission->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'undone_at' => null
        ]);
        
        // Kiểm tra xem tất cả users đã submit chưa
        $this->checkAllUsersSubmitted();
        
        return true;
    }

    /**
     * Undo submission của user
     */
    public function undoSubmissionByUser(User $user): bool
    {
        $submission = $this->getUserSubmission($user);
        
        if (!$submission || !$submission->canUndo()) {
            return false;
        }
        
        $submission->update([
            'status' => 'undone',
            'undone_at' => now()
        ]);
        
        // Nếu task đang pending_approval, chuyển về in_progress
        if ($this->status === 'pending_approval') {
            $this->status = 'in_progress';
            $this->completed_at = null;
            $this->save();
        }
        
        return true;
    }

    /**
     * Kiểm tra xem tất cả users đã submit chưa
     */
    public function checkAllUsersSubmitted(): void
    {
        $assignedUsers = $this->getAssignedUsers();
        $submittedCount = $this->submissions()->submitted()->count();
        
        // Nếu tất cả users đã submit, chuyển task sang pending_approval
        if ($submittedCount >= $assignedUsers->count() && $this->status === 'in_progress') {
            $this->status = 'pending_approval';
            $this->completed_at = now();
            $this->save();
        }
    }

    /**
     * Lấy thông tin submission progress (3/4 người đã submit)
     */
    public function getSubmissionProgress(): array
    {
        $assignedUsers = $this->getAssignedUsers();
        $submittedCount = $this->submissions()->submitted()->count();
        
        return [
            'total' => $assignedUsers->count(),
            'submitted' => $submittedCount,
            'progress' => $assignedUsers->count() > 0 ? round(($submittedCount / $assignedUsers->count()) * 100) : 0,
            'all_submitted' => $submittedCount >= $assignedUsers->count()
        ];
    }

    /**
     * Scope để lọc task theo phòng ban
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId)
                    ->orWhereHas('departments', function($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    });
    }

    /**
     * Scope để lọc task theo nhiều phòng ban
     */
    public function scopeByDepartments($query, $departmentIds)
    {
        if (empty($departmentIds)) {
            return $query;
        }
        
        return $query->where(function($q) use ($departmentIds) {
            $q->whereIn('department_id', $departmentIds)
              ->orWhereHas('departments', function($subQ) use ($departmentIds) {
                  $subQ->whereIn('department_id', $departmentIds);
              });
        });
    }

    /**
     * Scope để lọc task theo user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('assignee_id', $userId)
                    ->orWhere('creator_id', $userId)
                    ->orWhereHas('assignees', function($q) use ($userId) {
                        $q->where('user_id', $userId);
                    });
    }

    /**
     * Scope để lọc multi-department tasks
     */
    public function scopeMultiDepartment($query)
    {
        return $query->where('is_multi_department', true);
    }

    /**
     * Scope để lọc single department tasks
     */
    public function scopeSingleDepartment($query)
    {
        return $query->where('is_multi_department', false);
    }

    /**
     * Kiểm tra task có bị trễ hạn không
     */
    public function isOverdue(): bool
    {
        return $this->deadline && 
               $this->deadline->isPast() && 
               !in_array($this->status, ['completed', 'finished', 'rejected', 'overdue']);
    }

    /**
     * Kiểm tra task có cần cập nhật trạng thái overdue không
     */
    public function needsOverdueUpdate(): bool
    {
        return $this->status === 'in_progress' && $this->isOverdue();
    }

    /**
     * Cập nhật trạng thái thành overdue nếu cần
     */
    public function updateOverdueStatusIfNeeded(): bool
    {
        if (!$this->needsOverdueUpdate()) {
            return false;
        }

        $this->status = 'overdue';
        
        // Ghi log activity (sử dụng system user hoặc bỏ qua nếu không có)
        try {
            $systemUser = User::where('role', 'admin')->first();
            if ($systemUser) {
                $this->activities()->create([
                    'action' => 'updated_status',
                    'meta' => json_encode([
                        'description' => 'Trạng thái tự động chuyển thành "Trễ hạn" do vượt quá deadline',
                        'old_status' => 'in_progress',
                        'new_status' => 'overdue',
                        'auto_updated' => true
                    ]),
                    'user_id' => $systemUser->id
                ]);
            }
        } catch (\Exception $e) {
            // Bỏ qua nếu không thể tạo activity
        }

        return $this->save();
    }

    /**
     * Scope để lọc task đang trễ hạn
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    /**
     * Scope để lọc task cần cập nhật trạng thái overdue
     */
    public function scopeNeedsOverdueUpdate($query)
    {
        return $query->where('status', 'in_progress')
                    ->where('deadline', '<', now());
    }

    /**
     * Tự động phát hiện và gán phòng ban dựa trên assignees
     */
    public function autoDetectAndAssignDepartments(): array
    {
        $departmentIds = collect();
        
        // Lấy phòng ban từ assignee chính
        if ($this->assignee_id && $this->assignee) {
            $departmentIds->push($this->assignee->department_id);
        }
        
        // Lấy phòng ban từ multi-assignees
        $assigneeDepartments = $this->assignees()
            ->with('department')
            ->get()
            ->pluck('department_id')
            ->filter();
        
        $departmentIds = $departmentIds->merge($assigneeDepartments);
        
        // Loại bỏ duplicate và null values
        $uniqueDepartmentIds = $departmentIds->unique()->filter()->values();
        
        return $uniqueDepartmentIds->toArray();
    }

    /**
     * Cập nhật phòng ban tự động dựa trên assignees
     */
    public function updateDepartmentsFromAssignees(): bool
    {
        $detectedDepartmentIds = $this->autoDetectAndAssignDepartments();
        
        if (empty($detectedDepartmentIds)) {
            return false;
        }
        
        // Cập nhật is_multi_department
        $this->is_multi_department = count($detectedDepartmentIds) > 1;
        
        // Nếu chỉ có 1 phòng ban, gán vào department_id chính
        if (count($detectedDepartmentIds) === 1) {
            $this->department_id = $detectedDepartmentIds[0];
        } else {
            // Nếu có nhiều phòng ban, department_id chính có thể null hoặc giữ nguyên
            // và sử dụng bảng department_tasks
        }
        
        // Sync departments trong bảng department_tasks
        $this->departments()->sync($detectedDepartmentIds);
        
        return $this->save();
    }

    /**
     * Lấy danh sách phòng ban hiện tại của task
     */
    public function getCurrentDepartments(): \Illuminate\Support\Collection
    {
        $departments = collect();
        
        // Thêm department chính nếu có
        if ($this->department_id && $this->department) {
            $departments->push($this->department);
        }
        
        // Thêm các departments từ bảng department_tasks
        $multiDepartments = $this->departments()->get();
        $departments = $departments->merge($multiDepartments);
        
        return $departments->unique('id');
    }

    /**
     * Kiểm tra xem task có phòng ban nào không
     */
    public function hasDepartments(): bool
    {
        return $this->department_id || $this->departments()->exists();
    }

    /**
     * Lấy tên các phòng ban của task
     */
    public function getDepartmentNames(): string
    {
        $departments = $this->getCurrentDepartments();
        
        if ($departments->isEmpty()) {
            return 'Chưa phân phòng ban';
        }
        
        return $departments->pluck('name')->join(', ');
    }

    // Subtasks helper methods
    /**
     * Kiểm tra xem tất cả subtasks đã hoàn thành chưa
     */
    public function allSubtasksCompleted(): bool
    {
        if ($this->subtasks()->count() === 0) {
            return true; // Nếu không có subtask thì coi như hoàn thành
        }
        
        return $this->subtasks()->where('status', '!=', 'completed')->count() === 0;
    }

    /**
     * Kiểm tra xem có subtask nào chưa hoàn thành không
     */
    public function hasIncompleteSubtasks(): bool
    {
        return !$this->allSubtasksCompleted();
    }

    /**
     * Lấy số lượng subtasks đã hoàn thành
     */
    public function getCompletedSubtasksCount(): int
    {
        return $this->subtasks()->where('status', 'completed')->count();
    }

    /**
     * Lấy tổng số subtasks
     */
    public function getTotalSubtasksCount(): int
    {
        return $this->subtasks()->count();
    }

    /**
     * Lấy tiến độ hoàn thành subtasks (phần trăm)
     */
    public function getSubtasksProgressPercentage(): float
    {
        $total = $this->getTotalSubtasksCount();
        if ($total === 0) {
            return 100.0; // Nếu không có subtask thì 100%
        }
        
        $completed = $this->getCompletedSubtasksCount();
        return round(($completed / $total) * 100, 2);
    }

    /**
     * Lấy danh sách users có thể được assign vào subtasks
     */
    public function getAvailableUsersForSubtasks(): \Illuminate\Database\Eloquent\Collection
    {
        $userIds = [];
        
        // Thêm assignee chính
        if ($this->assignee_id) {
            $userIds[] = $this->assignee_id;
        }
        
        // Thêm multi-assignees
        $assigneeIds = $this->assignees()->pluck('users.id')->toArray();
        $userIds = array_merge($userIds, $assigneeIds);
        
        // Loại bỏ duplicate và trả về Eloquent Collection
        $uniqueUserIds = array_unique($userIds);
        
        return User::whereIn('id', $uniqueUserIds)->get();
    }

    /**
     * Reset tất cả subtasks về trạng thái pending (khi task bị reject)
     */
    public function resetSubtasksToPending(): void
    {
        $this->subtasks()->update([
            'status' => 'todo',
            'completed_at' => null
        ]);
    }

    /**
     * Kiểm tra xem task có subtasks không
     */
    public function hasSubtasks(): bool
    {
        return $this->subtasks()->count() > 0;
    }

    /**
     * Kiểm tra xem task có thể được hoàn thành không (tất cả subtasks đã xong)
     */
    public function canBeCompleted(): bool
    {
        return $this->allSubtasksCompleted();
    }
}

