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
        Schema::table('chits', function (Blueprint $table) {
            $table->foreign(['department_id'])->references(['id'])->on('departments')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['fee_type_id'])->references(['id'])->on('fee_types')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['government_department_id'])->references(['id'])->on('government_departments')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['patient_id'])->references(['id'])->on('patients')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chits', function (Blueprint $table) {
            $table->dropForeign('chits_department_id_foreign');
            $table->dropForeign('chits_fee_type_id_foreign');
            $table->dropForeign('chits_government_department_id_foreign');
            $table->dropForeign('chits_patient_id_foreign');
            $table->dropForeign('chits_user_id_foreign');
        });
    }
};
