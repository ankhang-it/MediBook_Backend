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
        Schema::create('feedback', function (Blueprint $table) {
            $table->uuid('feedback_id')->primary();
            $table->uuid('patient_id');
            $table->uuid('doctor_id');
            $table->integer('rating')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('patient_id')->references('patient_id')->on('patient_profiles')->onDelete('cascade');
            $table->foreign('doctor_id')->references('doctor_id')->on('doctor_profiles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
