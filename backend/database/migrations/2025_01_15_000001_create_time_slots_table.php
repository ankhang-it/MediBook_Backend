<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->string('doctor_id', 36);
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->foreign('doctor_id')->references('doctor_id')->on('doctor_profiles')->onDelete('cascade');
            $table->unique(['doctor_id', 'date', 'start_time'], 'unique_slot');
            $table->index(['doctor_id', 'date', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
