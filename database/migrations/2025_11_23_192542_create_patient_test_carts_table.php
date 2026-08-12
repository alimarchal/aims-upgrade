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
        Schema::create('patient_test_carts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('patient_id')->index('patient_test_carts_patient_id_foreign');
            $table->unsignedBigInteger('fee_type_id')->index('patient_test_carts_fee_type_id_foreign');
            $table->boolean('government_non_gov')->nullable();
            $table->integer('government_department_id')->nullable();
            $table->string('government_card_no', 20)->nullable();
            $table->enum('status', ['Normal', 'Return'])->default('Normal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_test_carts');
    }
};
