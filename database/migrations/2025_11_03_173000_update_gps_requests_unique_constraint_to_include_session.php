<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Unique: user_id + request_date + session (1 checkin + 1 checkout / ngày).
     * Cẩn thận: MySQL có thể dùng index unique_request cho FK user_id → phải có index riêng trước khi drop.
     */
    public function up(): void
    {
        if (!Schema::hasTable('gps_requests')) {
            return;
        }

        $indexNames = $this->indexNames('gps_requests');

        // Đảm bảo có index cho user_id (phục vụ FK) trước khi drop unique cũ
        if (!$this->hasIndexCoveringUserId($indexNames)) {
            try {
                DB::statement('CREATE INDEX gps_requests_user_id_support ON gps_requests (user_id)');
            } catch (\Throwable $e) {
                // index có thể đã tồn tại dưới tên khác
            }
            $indexNames = $this->indexNames('gps_requests');
        }

        // Drop unique cũ nếu còn
        if (in_array('unique_request', $indexNames, true)) {
            try {
                DB::statement('ALTER TABLE gps_requests DROP INDEX unique_request');
            } catch (\Throwable $e) {
                // Thử thêm index support rồi drop lại
                try {
                    DB::statement('CREATE INDEX gps_requests_user_id_support2 ON gps_requests (user_id)');
                } catch (\Throwable $e2) {
                }
                DB::statement('ALTER TABLE gps_requests DROP INDEX unique_request');
            }
        }

        // Tạo unique mới (3 cột) nếu chưa có
        $indexNames = $this->indexNames('gps_requests');
        if (!in_array('unique_request', $indexNames, true)) {
            // Chỉ tạo khi cột session tồn tại
            if (Schema::hasColumn('gps_requests', 'session')) {
                DB::statement(
                    'ALTER TABLE gps_requests ADD UNIQUE unique_request (user_id, request_date, session)'
                );
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('gps_requests')) {
            return;
        }

        $indexNames = $this->indexNames('gps_requests');

        if (in_array('unique_request', $indexNames, true)) {
            DB::statement('ALTER TABLE gps_requests DROP INDEX unique_request');
        }

        try {
            DB::statement('ALTER TABLE gps_requests ADD UNIQUE unique_request (user_id, request_date)');
        } catch (\Throwable $e) {
            // ignore if exists
        }
    }

    /**
     * @return list<string>
     */
    private function indexNames(string $table): array
    {
        $rows = DB::select("SHOW INDEX FROM `{$table}`");
        $names = [];
        foreach ($rows as $row) {
            $names[] = $row->Key_name;
        }

        return array_values(array_unique($names));
    }

    /**
     * @param  list<string>  $indexNames
     */
    private function hasIndexCoveringUserId(array $indexNames): bool
    {
        // Foreign id Laravel thường tạo gps_requests_user_id_foreign
        foreach ($indexNames as $name) {
            if ($name === 'PRIMARY') {
                continue;
            }
            if (str_contains($name, 'user_id')) {
                return true;
            }
        }

        // Kiểm tra cột đầu của unique_request có phải user_id không
        $rows = DB::select("SHOW INDEX FROM gps_requests WHERE Key_name = 'unique_request' AND Seq_in_index = 1");
        if ($rows && ($rows[0]->Column_name ?? null) === 'user_id') {
            // unique đang cover user_id — cần index khác trước khi drop
            return false;
        }

        return count(DB::select("SHOW INDEX FROM gps_requests WHERE Column_name = 'user_id'")) > 0;
    }
};
