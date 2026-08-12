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
        Schema::create('fee_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('fee_category_id')->index('fee_types_fee_category_id_foreign');
            $table->string('type')->nullable();
            $table->decimal('amount', 14);
            $table->decimal('hif', 14)->default(0);
            $table->enum('status', ['Normal', 'Return Fee'])->default('Normal');
            $table->timestamps();

            $table->index(['fee_category_id', 'status'], 'idx_fee_types_fee_category_id_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_types');
    }
};
