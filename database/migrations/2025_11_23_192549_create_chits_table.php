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
        Schema::create('chits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('chits_user_id_foreign');
            $table->unsignedBigInteger('department_id')->nullable()->index('chits_department_id_foreign');
            $table->unsignedBigInteger('patient_id')->index('chits_patient_id_foreign');
            $table->unsignedBigInteger('fee_type_id')->nullable()->index('chits_fee_type_id_foreign');
            $table->unsignedBigInteger('government_department_id')->nullable()->index('chits_government_department_id_foreign');
            $table->string('address')->nullable();
            $table->timestamp('issued_date')->useCurrent();
            $table->decimal('amount', 15);
            $table->decimal('amount_hif', 14)->default(0);
            $table->decimal('govt_amount', 14)->nullable()->default(0);
            $table->decimal('actual_amount', 15, 2)->default(0);
            $table->string('sehat_sahulat_visit_no')->nullable();
            $table->boolean('ipd_opd');
            $table->boolean('payment_status')->default(true);
            $table->boolean('government_non_gov')->nullable();
            $table->string('government_card_no', 15)->nullable();
            $table->string('designation', 30)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['government_non_gov', 'created_at'], 'chits_gov_created_at_idx');
            $table->index(['user_id', 'created_at'], 'chits_user_id_created_at_idx');
            $table->index(['issued_date', 'fee_type_id', 'government_non_gov'], 'idx_chits_issued_date_fee_type_gov');
            $table->index(['issued_date', 'government_non_gov'], 'idx_chits_issued_date_gov');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chits');
    }
};
