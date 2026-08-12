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
        Schema::table('tehsils', function (Blueprint $table) {
            $table->foreign(['district_id'])->references(['id'])->on('districts')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tehsils', function (Blueprint $table) {
            $table->dropForeign('tehsils_district_id_foreign');
        });
    }
};
