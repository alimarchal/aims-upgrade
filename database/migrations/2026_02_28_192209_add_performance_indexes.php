<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Check if index exists (cross-database compatible)
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            return DB::selectOne('SELECT EXISTS (SELECT 1 FROM pg_indexes WHERE indexname = ?)', [$indexName]) !== null
                && DB::selectOne('SELECT EXISTS (SELECT 1 FROM pg_indexes WHERE indexname = ?)', [$indexName])->exists;
        }

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $result = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);

            return count($result) > 0;
        }

        return false;
    }

    /**
     * Safely create index if it doesn't exist
     */
    private function createIndexIfNotExists(Blueprint $table, array|string $columns, string $indexName): void
    {
        $tableName = $table->getTable();
        if (! $this->indexExists($tableName, $indexName)) {
            $table->index($columns, $indexName);
        }
    }

    /**
     * Run the migrations.
     * Performance indexes for PostgreSQL/MySQL/MariaDB optimization
     */
    public function up(): void
    {
        // Patients table - for searching and filtering
        Schema::table('patients', function (Blueprint $table) {
            $this->createIndexIfNotExists($table, 'first_name', 'idx_patients_first_name');
            $this->createIndexIfNotExists($table, 'last_name', 'idx_patients_last_name');
            $this->createIndexIfNotExists($table, 'father_husband_name', 'idx_patients_father_husband_name');
            $this->createIndexIfNotExists($table, 'cnic', 'idx_patients_cnic');
            $this->createIndexIfNotExists($table, 'mobile', 'idx_patients_mobile');
            $this->createIndexIfNotExists($table, 'phone', 'idx_patients_phone');
            $this->createIndexIfNotExists($table, 'created_at', 'idx_patients_created_at');
            $this->createIndexIfNotExists($table, 'sex', 'idx_patients_sex');
            $this->createIndexIfNotExists($table, 'government_non_gov', 'idx_patients_gov');
            $this->createIndexIfNotExists($table, ['sex', 'created_at'], 'idx_patients_sex_created_at');
            $this->createIndexIfNotExists($table, ['government_non_gov', 'created_at'], 'idx_patients_gov_created_at');
        });

        // Chits table - for dashboard and reports
        Schema::table('chits', function (Blueprint $table) {
            $this->createIndexIfNotExists($table, ['department_id', 'issued_date', 'ipd_opd'], 'idx_chits_dept_issued_ipd');
            $this->createIndexIfNotExists($table, ['department_id', 'issued_date', 'government_non_gov'], 'idx_chits_dept_issued_gov');
            $this->createIndexIfNotExists($table, ['fee_type_id', 'issued_date'], 'idx_chits_fee_type_issued');
            $this->createIndexIfNotExists($table, ['ipd_opd', 'issued_date'], 'idx_chits_ipd_issued');
        });

        // Invoices table - for dashboard and reports
        Schema::table('invoices', function (Blueprint $table) {
            $this->createIndexIfNotExists($table, ['patient_id', 'created_at'], 'idx_invoices_patient_created');
        });

        // Admissions table - for reports
        Schema::table('admissions', function (Blueprint $table) {
            $this->createIndexIfNotExists($table, 'created_at', 'idx_admissions_created_at');
            $this->createIndexIfNotExists($table, 'status', 'idx_admissions_status');
            $this->createIndexIfNotExists($table, ['status', 'created_at'], 'idx_admissions_status_created');
            $this->createIndexIfNotExists($table, 'government_department_id', 'idx_admissions_govt_dept');
        });

        // Patient tests - for reports
        Schema::table('patient_tests', function (Blueprint $table) {
            $this->createIndexIfNotExists($table, 'status', 'idx_patient_tests_status');
            $this->createIndexIfNotExists($table, ['invoice_id', 'status'], 'idx_patient_tests_invoice_status');
        });

        // Fee types - for filtering
        Schema::table('fee_types', function (Blueprint $table) {
            $this->createIndexIfNotExists($table, 'status', 'idx_fee_types_status');
            $this->createIndexIfNotExists($table, 'type', 'idx_fee_types_type');
            $this->createIndexIfNotExists($table, ['fee_category_id', 'status'], 'idx_fee_types_category_status');
        });

        // Fee categories - for filtering
        Schema::table('fee_categories', function (Blueprint $table) {
            $this->createIndexIfNotExists($table, 'name', 'idx_fee_categories_name');
        });

        // Departments - for filtering
        Schema::table('departments', function (Blueprint $table) {
            $this->createIndexIfNotExists($table, 'name', 'idx_departments_name');
        });

        // Users table - for filtering
        Schema::table('users', function (Blueprint $table) {
            $this->createIndexIfNotExists($table, 'status', 'idx_users_status');
            $this->createIndexIfNotExists($table, 'department_id', 'idx_users_department');
            $this->createIndexIfNotExists($table, ['status', 'department_id'], 'idx_users_status_dept');
        });

        // Patient emergency treatments - for reports
        Schema::table('patient_emergency_treatments', function (Blueprint $table) {
            $this->createIndexIfNotExists($table, 'created_at', 'idx_pet_created_at');
            $this->createIndexIfNotExists($table, 'patient_id', 'idx_pet_patient_id');
        });

        // Total fees - for reports
        Schema::table('total_fees', function (Blueprint $table) {
            $this->createIndexIfNotExists($table, 'created_at', 'idx_total_fees_created_at');
        });
    }

    /**
     * Safely drop index if it exists
     */
    private function dropIndexIfExists(Blueprint $table, string $indexName): void
    {
        $tableName = $table->getTable();
        if ($this->indexExists($tableName, $indexName)) {
            $table->dropIndex($indexName);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Patients
        Schema::table('patients', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_patients_first_name');
            $this->dropIndexIfExists($table, 'idx_patients_last_name');
            $this->dropIndexIfExists($table, 'idx_patients_father_husband_name');
            $this->dropIndexIfExists($table, 'idx_patients_cnic');
            $this->dropIndexIfExists($table, 'idx_patients_mobile');
            $this->dropIndexIfExists($table, 'idx_patients_phone');
            $this->dropIndexIfExists($table, 'idx_patients_created_at');
            $this->dropIndexIfExists($table, 'idx_patients_sex');
            $this->dropIndexIfExists($table, 'idx_patients_gov');
            $this->dropIndexIfExists($table, 'idx_patients_sex_created_at');
            $this->dropIndexIfExists($table, 'idx_patients_gov_created_at');
        });

        // Chits
        Schema::table('chits', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_chits_dept_issued_ipd');
            $this->dropIndexIfExists($table, 'idx_chits_dept_issued_gov');
            $this->dropIndexIfExists($table, 'idx_chits_fee_type_issued');
            $this->dropIndexIfExists($table, 'idx_chits_ipd_issued');
        });

        // Invoices
        Schema::table('invoices', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_invoices_patient_created');
        });

        // Admissions
        Schema::table('admissions', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_admissions_created_at');
            $this->dropIndexIfExists($table, 'idx_admissions_status');
            $this->dropIndexIfExists($table, 'idx_admissions_status_created');
            $this->dropIndexIfExists($table, 'idx_admissions_govt_dept');
        });

        // Patient tests
        Schema::table('patient_tests', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_patient_tests_status');
            $this->dropIndexIfExists($table, 'idx_patient_tests_invoice_status');
        });

        // Fee types
        Schema::table('fee_types', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_fee_types_status');
            $this->dropIndexIfExists($table, 'idx_fee_types_type');
            $this->dropIndexIfExists($table, 'idx_fee_types_category_status');
        });

        // Fee categories
        Schema::table('fee_categories', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_fee_categories_name');
        });

        // Departments
        Schema::table('departments', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_departments_name');
        });

        // Users
        Schema::table('users', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_users_status');
            $this->dropIndexIfExists($table, 'idx_users_department');
            $this->dropIndexIfExists($table, 'idx_users_status_dept');
        });

        // Patient emergency treatments
        Schema::table('patient_emergency_treatments', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_pet_created_at');
            $this->dropIndexIfExists($table, 'idx_pet_patient_id');
        });

        // Total fees
        Schema::table('total_fees', function (Blueprint $table) {
            $this->dropIndexIfExists($table, 'idx_total_fees_created_at');
        });
    }
};
