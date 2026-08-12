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
        Schema::create('total_fees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('patient_test_id');
            $table->unsignedBigInteger('user_id')->index('total_fees_user_id_foreign');
            $table->unsignedBigInteger('department_id')->index('total_fees_department_id_foreign');
            $table->unsignedBigInteger('patient_id')->index('total_fees_patient_id_foreign');
            $table->unsignedBigInteger('lab_test_id')->index('total_fees_lab_test_id_foreign');
            $table->decimal('hif_amount', 15);
            $table->decimal('government_amount', 15);
            $table->decimal('total_amount', 15);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('total_fees');
    }
};
