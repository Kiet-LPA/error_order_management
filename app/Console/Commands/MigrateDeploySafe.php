<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Migrate production khi DB lệch bảng migrations.
 * Gặp already exists / Duplicate column → ghi nhận migration đã chạy và tiếp tục.
 */
class MigrateDeploySafe extends Command
{
    protected $signature = 'migrate:deploy-safe';

    protected $description = 'Migrate an toàn cho deploy (bỏ qua schema đã có trên DB)';

    public function handle(): int
    {
        $maxSteps = 200;

        for ($step = 1; $step <= $maxSteps; $step++) {
            $pending = $this->pendingMigrationNames();
            if ($pending === []) {
                $this->info('Nothing to migrate.');
                return self::SUCCESS;
            }

            $current = $pending[0];
            $this->line("— Step {$step}: ".count($pending)." pending — next: {$current}");

            $output = '';
            $exit = 0;

            try {
                $exit = Artisan::call('migrate', [
                    '--force' => true,
                    '--step' => true,
                    '--no-interaction' => true,
                    '--path' => 'database/migrations/'.$current.'.php',
                ]);
                $output = Artisan::output();
            } catch (\Throwable $e) {
                $exit = 1;
                $output = Artisan::output()."\n".$e->getMessage()."\n".$e->getTraceAsString();
            }

            if ($output !== '') {
                $this->line(trim($output));
            }

            if ($exit === 0) {
                continue;
            }

            if ($this->isAlreadyAppliedError($output)) {
                $this->warn("Schema already present — marking as run: {$current}");
                $this->markMigrationAsRan($current);
                continue;
            }

            $this->error("Migration failed (not skippable): {$current}");
            return self::FAILURE;
        }

        $this->error("Stopped after {$maxSteps} steps.");
        return self::FAILURE;
    }

    /**
     * @return list<string>
     */
    private function pendingMigrationNames(): array
    {
        $ran = DB::table('migrations')->pluck('migration')->all();
        $files = File::glob(database_path('migrations/*.php')) ?: [];

        $pending = [];
        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            if (! in_array($name, $ran, true)) {
                $pending[] = $name;
            }
        }

        sort($pending);

        return $pending;
    }

    private function isAlreadyAppliedError(string $output): bool
    {
        return (bool) preg_match(
            '/already exists|Duplicate column|Duplicate key name|Base table or view already exists|SQLSTATE\[42S01\]|SQLSTATE\[42S21\]/i',
            $output
        );
    }

    private function markMigrationAsRan(string $name): void
    {
        if (DB::table('migrations')->where('migration', $name)->exists()) {
            $this->line("Already recorded: {$name}");
            return;
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
