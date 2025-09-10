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
        'is_read',
        'read_at',
        'read_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason'
    ];

    protected $casts = [
        'report_date' => 'date',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'rejected_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
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

    public function readBy()
    {
        return $this->belongsTo(User::class, 'read_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    // Accessors
    public function getReadByAdminAttribute()
    {
        return $this->readBy && $this->readBy->isAdmin();
    }

    public function getRejectedByAdminAttribute()
    {
        return $this->rejectedBy && $this->rejectedBy->isAdmin();
    }

    public function getReadByUserAttribute()
    {
        return $this->readBy;
    }

    public function getRejectedByUserAttribute()
    {
        return $this->rejectedBy;
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

    // Get week start and end dates (Monday to Sunday)
    public static function getWeekDates($year, $weekNumber)
    {
        $date = Carbon::now()->setISODate($year, $weekNumber, 1); // Monday
        $startDate = $date->copy();
        $endDate = $date->copy()->addDays(6); // Sunday
        
        return [
            'start' => $startDate->format('Y-m-d'),
            'end' => $endDate->format('Y-m-d'),
            'start_formatted' => $startDate->format('d/m/Y'),
            'end_formatted' => $endDate->format('d/m/Y')
        ];
    }

    // Get week number from date
    public static function getWeekFromDate($date)
    {
        return Carbon::parse($date)->weekOfYear;
    }

    // Get week of month from date
    public static function getWeekOfMonth($date)
    {
        $carbon = Carbon::parse($date);
        $firstDayOfMonth = $carbon->copy()->startOfMonth();
        $dayOfMonth = $carbon->day;
        
        // Tính tuần thứ mấy của tháng
        $weekOfMonth = ceil($dayOfMonth / 7);
        
        return $weekOfMonth;
    }

    // Get comprehensive week info from date
    public static function getWeekInfoFromDate($date)
    {
        $carbon = Carbon::parse($date);
        $year = $carbon->year;
        $month = $carbon->month;
        $weekOfYear = $carbon->weekOfYear;
        $weekOfMonth = self::getWeekOfMonth($date);
        
        return [
            'year' => $year,
            'month' => $month,
            'week_of_year' => $weekOfYear,
            'week_of_month' => $weekOfMonth,
            'date' => $carbon->format('Y-m-d'),
            'formatted_date' => $carbon->format('d/m/Y')
        ];
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

    // Get available weeks for a year and month
    public static function getAvailableWeeks($year, $month = null, $userId = null)
    {
        $query = self::byYear($year);
        if ($month) {
            $query->byMonth($month);
        }
        if ($userId) {
            $query->byUser($userId);
        }
        return $query->distinct()->pluck('week')->sort()->values();
    }

    // Get reports for a specific week
    public static function getWeekReports($year, $week, $month = null, $userId = null)
    {
        $query = self::with(['user', 'department'])
                    ->byYear($year)
                    ->byWeek($week);
        
        if ($month) {
            $query->byMonth($month);
        }
        
        if ($userId) {
            $query->byUser($userId);
        }
        
        return $query->orderBy('report_date', 'desc')->get();
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
