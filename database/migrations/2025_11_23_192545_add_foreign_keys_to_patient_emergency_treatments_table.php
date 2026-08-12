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
        Schema::table('patient_emergency_treatments', function (Blueprint $table) {
            $table->foreign(['disease_id'])->references(['id'])->on('diseases')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['patient_id'])->references(['id'])->on('patients')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_emergency_treatments', function (Blueprint $table) {
            $table->dropForeign('patient_emergency_treatments_disease_id_foreign');
            $table->dropForeign('patient_emergency_treatments_patient_id_foreign');
            $table->dropForeign('patient_emergency_treatments_user_id_foreign');
        });
    }
};
