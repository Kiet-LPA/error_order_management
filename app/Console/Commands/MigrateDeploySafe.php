<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Chạy migration trên production khi DB đã được sửa tay / lệch bảng migrations.
 * Gặp "already exists" / "Duplicate column" → đánh dấu migration đã chạy và tiếp tục.
 */
class MigrateDeploySafe extends Command
{
    protected $signature = 'migrate:deploy-safe {--pretend : Chỉ liệt kê, không chạy}';

    protected $description = 'Migrate an toàn cho deploy (bỏ qua schema đã có trên DB)';

    public function handle(): int
    {
        $pretend = (bool) $this->option('pretend');
        $maxSteps = 200;

        for ($step = 1; $step <= $maxSteps; $step++) {
            $before = $this->pendingCount();
            if ($before === 0) {
                $this->info('Nothing to migrate.');
                return self::SUCCESS;
            }

            $this->line("— Step {$step}: {$before} migration(s) pending");

            if ($pretend) {
                Artisan::call('migrate', ['--pretend' => true, '--force' => true]);
                $this->line(Artisan::output());
                return self::SUCCESS;
            }

            $exit = Artisan::call('migrate', [
                '--force' => true,
                '--step' => true,
                '--no-interaction' => true,
            ]);
            $output = Artisan::output();
            $this->line($output);

            if ($exit === 0) {
                $after = $this->pendingCount();
                if ($after === 0) {
                    $this->info('All migrations applied.');
                    return self::SUCCESS;
                }
                // Bước thành công, còn pending → vòng tiếp
                continue;
            }

            // Thất bại: nếu lỗi "đã tồn tại" thì log migration và tiếp tục
            if ($this->isAlreadyAppliedError($output)) {
                $name = $this->extractFailedMigrationName($output);
                if (!$name) {
                    $this->error('Migration failed (already exists) nhưng không lấy được tên file.');
                    $this->line($output);
                    return self::FAILURE;
                }

                $this->warn("Schema already present — marking as run: {$name}");
                $this->markMigrationAsRan($name);
                continue;
            }

            $this->error('Migration failed with a non-skippable error.');
            return self::FAILURE;
        }

        $this->error("Stopped after {$maxSteps} steps (possible loop).");
        return self::FAILURE;
    }

    private function pendingCount(): int
    {
        Artisan::call('migrate:status', ['--no-interaction' => true]);
        $status = Artisan::output();
        // Dòng Pending (Laravel 10)
        return substr_count($status, 'Pending');
    }

    private function isAlreadyAppliedError(string $output): bool
    {
        return (bool) preg_match(
            '/already exists|Duplicate column|Duplicate key name|Base table or view already exists/i',
            $output
        );
    }

    private function extractFailedMigrationName(string $output): ?string
    {
        // "2025_10_06_104627_create_task_submissions_table ... FAIL"
        if (preg_match('/(\d{4}_\d{2}_\d{2}_\d{6}_[a-z0-9_]+)\s+\.+\s*FAIL/i', $output, $m)) {
            return $m[1];
        }
        // "Migrating: 2025_..."
        if (preg_match('/Migrating:\s+(\d{4}_\d{2}_\d{2}_\d{6}_[a-z0-9_]+)/i', $output, $m)) {
            return $m[1];
        }
        // fallback: bất kỳ migration name trong output
        if (preg_match('/(\d{4}_\d{2}_\d{2}_\d{6}_[a-z0-9_]+)/', $output, $m)) {
            return $m[1];
        }

        return null;
    }

    private function markMigrationAsRan(string $name): void
    {
        $exists = DB::table('migrations')->where('migration', $name)->exists();
        if ($exists) {
            $this->line("Already recorded: {$name}");
            return;
        }

        // Bảo đảm file tồn tại trong filesystem
        $found = false;
        foreach (File::glob(database_path('migrations/'.$name.'*.php')) as $file) {
            $found = true;
            break;
        }
        if (!$found && !File::exists(database_path('migrations/'.$name.'.php'))) {
            $this->warn("Migration file not found for {$name}, still recording name.");
        }

        $batch = (int) DB::table('migrations')->max('batch');
        $batch = $batch > 0 ? $batch + 1 : 1;

        DB::table('migrations')->insert([
            'migration' => $name,
            'batch' => $batch,
        ]);

        $this->info("Recorded {$name} as batch {$batch}");
    }
}
