<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;

class ResetRejectedTaskSubtasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:reset-rejected-subtasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset subtasks to todo status for all rejected tasks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Đang reset subtasks cho các task bị reject...');
        
        // Tìm tất cả tasks có status = 'rejected' và có subtasks
        $rejectedTasks = Task::where('status', 'rejected')
            ->whereHas('subtasks')
            ->with('subtasks')
            ->get();

        $this->info("📋 Tìm thấy {$rejectedTasks->count()} task bị reject có subtasks:");
        
        $totalReset = 0;
        
        foreach ($rejectedTasks as $task) {
            $this->line("Task ID: {$task->id} - {$task->title}");
            $this->line("Status: {$task->status}");
            $this->line("Rejection reason: " . ($task->rejection_reason ?? 'Không có lý do'));
            
            $completedSubtasks = $task->subtasks()->where('status', 'completed')->count();
            $totalSubtasks = $task->subtasks()->count();
            
            $this->line("Subtasks: {$completedSubtasks}/{$totalSubtasks} đã hoàn thành");
            
            if ($completedSubtasks > 0) {
                $this->line("🔄 Resetting subtasks...");
                
                // Reset tất cả subtasks về todo status
                $task->subtasks()->update([
                    'status' => 'todo',
                    'completed_at' => null
                ]);
                
                $this->line("✅ Đã reset {$completedSubtasks} subtasks về trạng thái 'todo'");
                $totalReset += $completedSubtasks;
            } else {
                $this->line("ℹ️  Không có subtasks nào cần reset");
            }
            
            $this->line(str_repeat('-', 50));
        }
        
        $this->info("🎉 Hoàn thành! Đã reset tổng cộng {$totalReset} subtasks của các task bị reject.");
        
        return Command::SUCCESS;
    }
}
