<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['patients_import', 'invoices_improt', 'patient_tests_import'] as $tableName) {
            if (Schema::hasColumn($tableName, 'archived_at')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->timestampTz('archived_at', 6)->useCurrent()->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['patients_import', 'invoices_improt', 'patient_tests_import'] as $tableName) {
            if (! Schema::hasColumn($tableName, 'archived_at')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn('archived_at');
            });
        }
    }
};
