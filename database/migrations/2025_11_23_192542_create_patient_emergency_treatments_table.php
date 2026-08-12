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
        Schema::create('patient_emergency_treatments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('patient_emergency_treatments_user_id_foreign');
            $table->unsignedBigInteger('patient_id')->index('patient_emergency_treatments_patient_id_foreign');
            $table->unsignedBigInteger('disease_id')->index('patient_emergency_treatments_disease_id_foreign');
            $table->text('treatment_details')->nullable();
            $table->text('medications')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_emergency_treatments');
    }
};
