<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const EMAIL = 'kh.marchal@gmail.com';

    /**
     * Show the developer's account as "SeeChange Innovative" across the software and reports.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('email', self::EMAIL)
            ->update(['name' => 'SeeChange Innovative', 'updated_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')
            ->where('email', self::EMAIL)
            ->update(['name' => 'Ali Raza Marchal (SA)', 'updated_at' => now()]);
    }
};
