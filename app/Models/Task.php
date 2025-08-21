<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['title','description','status','priority','attachments','qr_code','tracking_code','department_id','assignee_id','creator_id','rejection_reason','finish_note','deadline','is_recurring','recurring_start_date','recurring_days','last_reset_date','completed_at'];
    
    protected $casts = [
        'deadline' => 'datetime',
        'attachments' => 'array',
        'recurring_start_date' => 'date',
        'last_reset_date' => 'date',
        'completed_at' => 'datetime',
    ];
    public function department(){ return $this->belongsTo(Department::class); }
    public function assignee(){ return $this->belongsTo(User::class, 'assignee_id'); }
    public function creator(){ return $this->belongsTo(User::class, 'creator_id'); }
    public function activities(){ return $this->hasMany(TaskActivity::class); }
    
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
        if ($this->status !== 'completed' || !$this->completed_at) {
            return false;
        }
        
        // Kiểm tra xem đã qua 3 tiếng chưa
        $threeHoursAgo = now()->subHours(3);
        return $this->completed_at->gt($threeHoursAgo);
    }
    
    /**
     * Chuyển status từ 'completed' về 'in_progress'
     */
    public function undoCompletion(): bool
    {
        if (!$this->canUndo()) {
            return false;
        }
        
        // Chuyển status về 'in_progress'
        $this->status = 'in_progress';
        
        // Xóa completed_at
        $this->completed_at = null;
        
        // Xóa finish_note
        $this->finish_note = null;
        
        return $this->save();
    }
}

