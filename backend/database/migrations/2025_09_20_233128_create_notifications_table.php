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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('notification_id')->primary();
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->text('message');
            $table->enum('type', ['appointment', 'payment', 'system', 'reminder'])->nullable();
            $table->boolean('status')->default(false); // false = unread, true = read
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
