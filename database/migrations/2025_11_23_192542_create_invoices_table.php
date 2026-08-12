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
        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index('invoices_user_id_foreign');
            $table->unsignedBigInteger('patient_id')->index('invoices_patient_id_foreign');
            $table->decimal('total_amount', 15)->default(0);
            $table->decimal('hif_amount', 14)->default(0);
            $table->decimal('govt_amount', 14)->default(0);
            $table->decimal('actual_total_amount', 15, 2)->default(0);
            $table->boolean('government_non_government')->default(false);
            $table->softDeletes();
            $table->string('government_department_id', 10)->nullable();
            $table->string('government_card_no', 10)->nullable();
            $table->timestamps();

            $table->index(['created_at', 'user_id'], 'idx_invoices_created_at_user');
            $table->index(['government_non_government', 'created_at'], 'invoices_gov_created_at_idx');
            $table->index(['user_id', 'created_at'], 'invoices_user_id_created_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
