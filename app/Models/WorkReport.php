<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WorkReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'year',
        'month',
        'week',
        'report_date',
        'daily_work',
        'difficulties',
        'comments',
        'custom_fields'
    ];

    protected $casts = [
        'report_date' => 'date',
        'custom_fields' => 'array'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Scopes
    public function scopeByYear($query, $year)
    {
        return $query->where('year', $year);
    }

    public function scopeByMonth($query, $month)
    {
        return $query->where('month', $month);
    }

    public function scopeByWeek($query, $week)
    {
        return $query->where('week', $week);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helper methods
    public static function getWeekNumber($date)
    {
        return Carbon::parse($date)->weekOfYear;
    }

    public static function getMonthNumber($date)
    {
        return Carbon::parse($date)->month;
    }

    public static function getYearNumber($date)
    {
        return Carbon::parse($date)->year;
    }

    // Get available years for a user
    public static function getAvailableYears($userId = null)
    {
        $query = self::query();
        if ($userId) {
            $query->byUser($userId);
        }
        return $query->distinct()->pluck('year')->sort()->values();
    }

    // Get available months for a year
    public static function getAvailableMonths($year, $userId = null)
    {
        $query = self::byYear($year);
        if ($userId) {
            $query->byUser($userId);
        }
        return $query->distinct()->pluck('month')->sort()->values();
    }

    // Get available weeks for a month
    public static function getAvailableWeeks($year, $month, $userId = null)
    {
        $query = self::byYear($year)->byMonth($month);
        if ($userId) {
            $query->byUser($userId);
        }
        return $query->distinct()->pluck('week')->sort()->values();
    }

    // Get reports for a specific week
    public static function getWeekReports($year, $month, $week, $userId = null)
    {
        $query = self::with(['user', 'department'])
                    ->byYear($year)
                    ->byMonth($month)
                    ->byWeek($week);
        
        if ($userId) {
            $query->byUser($userId);
        }
        
        return $query->orderBy('report_date')->get();
    }

    // Get department custom fields configuration
    public function getDepartmentCustomFields()
    {
        // Có thể mở rộng để lấy từ database hoặc config
        $customFields = [
            'IT' => [
                'projects_worked_on' => 'Dự án đang làm',
                'bugs_fixed' => 'Lỗi đã sửa',
                'code_reviews' => 'Code review',
                'meetings_attended' => 'Cuộc họp tham gia'
            ],
            'HR' => [
                'candidates_interviewed' => 'Ứng viên phỏng vấn',
                'contracts_processed' => 'Hợp đồng xử lý',
                'training_sessions' => 'Buổi đào tạo',
                'employee_issues' => 'Vấn đề nhân viên'
            ],
            'Finance' => [
                'transactions_processed' => 'Giao dịch xử lý',
                'reports_generated' => 'Báo cáo tạo',
                'budget_reviews' => 'Đánh giá ngân sách',
                'audit_tasks' => 'Công việc kiểm toán'
            ]
        ];

        return $customFields[$this->department->name] ?? [];
    }
}
