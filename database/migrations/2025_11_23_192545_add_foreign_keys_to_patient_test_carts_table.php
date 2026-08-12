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
        Schema::table('patient_test_carts', function (Blueprint $table) {
            $table->foreign(['fee_type_id'])->references(['id'])->on('fee_types')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['patient_id'])->references(['id'])->on('patients')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_test_carts', function (Blueprint $table) {
            $table->dropForeign('patient_test_carts_fee_type_id_foreign');
            $table->dropForeign('patient_test_carts_patient_id_foreign');
        });
    }
};
