<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Government patient import archive tables currently require PostgreSQL.');
        }

        foreach ([
            'patients' => 'patients_import',
            'invoices' => 'invoices_improt',
            'patient_tests' => 'patient_tests_import',
        ] as $sourceTable => $archiveTable) {
            DB::statement("CREATE TABLE {$archiveTable} (LIKE {$sourceTable} INCLUDING ALL)");

            $primaryKey = DB::selectOne(
                'SELECT conname FROM pg_constraint WHERE conrelid = ?::regclass AND contype = ?',
                [$archiveTable, 'p'],
            );

            if ($primaryKey !== null) {
                $constraint = DB::connection()->getQueryGrammar()->wrap($primaryKey->conname);
                DB::statement("ALTER TABLE {$archiveTable} DROP CONSTRAINT {$constraint}");
            }

            DB::statement("ALTER TABLE {$archiveTable} ALTER COLUMN id DROP DEFAULT");

            Schema::table($archiveTable, function (Blueprint $table): void {
                $table->uuid('import_batch_id')->index();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_tests_import');
        Schema::dropIfExists('invoices_improt');
        Schema::dropIfExists('patients_import');
    }
};
