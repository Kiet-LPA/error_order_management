<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Carbon\Carbon;

class UpdateTaskOverdueStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:update-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật trạng thái overdue cho các task đã quá deadline';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Bắt đầu kiểm tra và cập nhật trạng thái overdue...');

        // Lấy tất cả task cần cập nhật trạng thái overdue
        $overdueTasks = Task::where('status', 'in_progress')
                            ->where('deadline', '<', now())
                            ->get();

        $count = 0;
        foreach ($overdueTasks as $task) {
            if ($task->updateOverdueStatusIfNeeded()) {
                $count++;
                $this->line("Task '{$task->title}' đã được cập nhật thành overdue");
            }
        }

        $this->info("Hoàn thành! Đã cập nhật {$count} task thành trạng thái overdue.");
        
        return 0;
    }
}
