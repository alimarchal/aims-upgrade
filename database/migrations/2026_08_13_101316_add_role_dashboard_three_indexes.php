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

    private function createIndexIfNotExists(string $tableName, array|string $columns, string $indexName): void
    {
        if ($this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
            $table->index($columns, $indexName);
        });
    }

    private function dropIndexIfExists(string $tableName, string $indexName): void
    {
        if (! $this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createIndexIfNotExists('chits', ['user_id', 'issued_date', 'government_non_gov'], 'idx_chits_user_issued_gov');
        $this->createIndexIfNotExists('invoices', ['user_id', 'created_at', 'government_non_government'], 'idx_invoices_user_created_gov');
        $this->createIndexIfNotExists('patients', ['created_at', 'dob'], 'idx_patients_created_dob');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('chits', 'idx_chits_user_issued_gov');
        $this->dropIndexIfExists('invoices', 'idx_invoices_user_created_gov');
        $this->dropIndexIfExists('patients', 'idx_patients_created_dob');
    }
};
