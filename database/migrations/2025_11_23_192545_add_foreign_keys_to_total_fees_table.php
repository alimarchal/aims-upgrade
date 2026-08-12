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
        Schema::table('total_fees', function (Blueprint $table) {
            $table->foreign(['department_id'])->references(['id'])->on('departments')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['lab_test_id'])->references(['id'])->on('lab_tests')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['patient_id'])->references(['id'])->on('patients')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('total_fees', function (Blueprint $table) {
            $table->dropForeign('total_fees_department_id_foreign');
            $table->dropForeign('total_fees_lab_test_id_foreign');
            $table->dropForeign('total_fees_patient_id_foreign');
            $table->dropForeign('total_fees_user_id_foreign');
        });
    }
};
