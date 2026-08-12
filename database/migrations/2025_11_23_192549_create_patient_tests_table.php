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
        Schema::create('patient_tests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('patient_id')->index('patient_tests_patient_id_foreign');
            $table->unsignedBigInteger('fee_type_id')->index('patient_tests_fee_type_id_foreign');
            $table->unsignedBigInteger('invoice_id')->index('patient_tests_invoice_id_foreign');
            $table->boolean('government_non_gov')->default(false);
            $table->string('government_department_id', 10)->nullable();
            $table->string('government_card_no', 10)->nullable();
            $table->decimal('total_amount', 15);
            $table->decimal('hif_amount', 14)->default(0);
            $table->decimal('govt_amount', 14)->default(0);
            $table->decimal('actual_total_amount', 15, 2)->default(0);
            $table->enum('status', ['Normal', 'Return'])->default('Normal');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['created_at', 'fee_type_id', 'government_non_gov', 'status'], 'idx_patient_tests_created_at_fee_type_gov_status');
            $table->index(['created_at', 'government_non_gov'], 'idx_patient_tests_created_at_gov');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_tests');
    }
};
