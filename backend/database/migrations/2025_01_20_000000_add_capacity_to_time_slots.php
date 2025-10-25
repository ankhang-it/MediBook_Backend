<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('time_slots', function (Blueprint $table) {
            $table->integer('max_capacity')->default(5)->after('is_available');
            $table->integer('current_bookings')->default(0)->after('max_capacity');
        });
    }

    public function down(): void
    {
        Schema::table('time_slots', function (Blueprint $table) {
            $table->dropColumn(['max_capacity', 'current_bookings']);
        });
    }
};
