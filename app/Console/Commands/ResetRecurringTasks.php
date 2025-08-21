<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetRecurringTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:reset-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset recurring tasks and update their deadlines';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting recurring tasks reset...');
        
        // Lấy tất cả tasks có is_recurring = true
        $recurringTasks = \App\Models\Task::where('is_recurring', true)->get();
        
        $this->info("Found {$recurringTasks->count()} recurring tasks");
        
        $resetCount = 0;
        
        foreach ($recurringTasks as $task) {
            if ($task->needsNewDeadline()) {
                $this->info("Resetting task: {$task->title} (ID: {$task->id})");
                
                if ($task->updateRecurringDeadline()) {
                    $resetCount++;
                    $this->info("✓ Task reset successfully. New deadline: {$task->deadline->format('Y-m-d')}");
                } else {
                    $this->error("✗ Failed to reset task: {$task->title}");
                }
            } else {
                $this->line("Task {$task->title} doesn't need reset yet");
            }
        }
        
        $this->info("Reset completed. {$resetCount} tasks were reset.");
        
        return 0;
    }
}
