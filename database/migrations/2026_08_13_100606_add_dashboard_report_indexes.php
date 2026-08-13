<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            $result = DB::selectOne(
                'SELECT EXISTS (SELECT 1 FROM pg_indexes WHERE schemaname = current_schema() AND tablename = ? AND indexname = ?) AS index_exists',
                [$table, $indexName]
            );

            return (bool) ($result->index_exists ?? false);
        }

        if ($driver === 'mysql' || $driver === 'mariadb') {
            return count(DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName])) > 0;
        }

        return false;
    }

    private function createIndexIfNotExists(Blueprint $table, array|string $columns, string $indexName): void
    {
        if (! $this->indexExists($table->getTable(), $indexName)) {
            $table->index($columns, $indexName);
        }
    }

    private function dropIndexIfExists(Blueprint $table, string $indexName): void
    {
        if ($this->indexExists($table->getTable(), $indexName)) {
            $table->dropIndex($indexName);
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chits', function (Blueprint $table) {
            $this->createIndexIfNotExists($table, ['user_id', 'issued_date'], 'idx_chits_user_issued_date');
            $this->createIndexIfNotExists($table, ['government_department_id', 'issued_date'], 'idx_chits_gov_department_issued_date');
            $this->createIndexIfNotExists($table, ['issued_date', 'department_id', 'user_id'], 'idx_chits_issued_department_user');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $this->createIndexIfNotExists($table, ['government_department_id', 'created_at'], 'idx_invoices_gov_department_created_at');
        });

        Schema::table('patient_tests', function (Blueprint $table) {
            $this->createIndexIfNotExists($table, ['created_at', 'deleted_at', 'fee_type_id'], 'idx_patient_tests_created_deleted_fee_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chits', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_chits_user_issued_date');
            $this->dropIndexIfExists($table, 'idx_chits_gov_department_issued_date');
            $this->dropIndexIfExists($table, 'idx_chits_issued_department_user');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_invoices_gov_department_created_at');
        });

        Schema::table('patient_tests', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_patient_tests_created_deleted_fee_type');
        });
    }
};
