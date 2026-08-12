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
        Schema::create('admissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('admissions_user_id_foreign');
            $table->unsignedBigInteger('invoice_id')->index('admissions_invoice_id_foreign');
            $table->unsignedBigInteger('patient_id')->index('admissions_patient_id_foreign');
            $table->unsignedBigInteger('government_department_id')->nullable();
            $table->decimal('actual_total_amount', 15, 2)->default(0);
            $table->string('unit_ward')->nullable();
            $table->string('disease')->nullable();
            $table->string('category')->nullable();
            $table->string('nok_name')->nullable();
            $table->string('relation_with_patient')->nullable();
            $table->string('village', 30)->nullable();
            $table->string('tehsil', 30)->nullable();
            $table->string('district', 30)->nullable();
            $table->string('address')->nullable();
            $table->string('cell_no')->nullable();
            $table->string('cnic_no')->nullable();
            $table->enum('status', ['Yes', 'No'])->nullable()->default('No');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};
