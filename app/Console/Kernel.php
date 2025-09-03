<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        
        // Reset recurring tasks every day at 7:00 AM
        $schedule->command('tasks:reset-recurring')->dailyAt('07:00');
        
        // Kiểm tra và cập nhật trạng thái overdue mỗi giờ
        $schedule->command('tasks:update-overdue')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
