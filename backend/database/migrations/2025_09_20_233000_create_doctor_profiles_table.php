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
        Schema::create('doctor_profiles', function (Blueprint $table) {
            $table->uuid('doctor_id')->primary();
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->string('fullname')->nullable();
            $table->uuid('specialty_id')->nullable();
            $table->text('experience')->nullable();
            $table->string('license_number')->nullable();
            $table->decimal('consultation_fee', 10, 2)->default(500000);
            $table->text('bio')->nullable();
            $table->json('schedule')->nullable();
            $table->timestamps();

            $table->foreign('specialty_id')->references('specialty_id')->on('specialties')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_profiles');
    }
};
