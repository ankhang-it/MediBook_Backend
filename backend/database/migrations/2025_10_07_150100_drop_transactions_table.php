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
        // Drop foreign key constraint first
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
        });

        // Drop the transactions table
        Schema::dropIfExists('transactions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate transactions table if needed
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_id');
            $table->integer('order_code');
            $table->decimal('amount', 10, 2);
            $table->text('description');
            $table->string('payment_link_id');
            $table->text('checkout_url');
            $table->text('qr_code')->nullable();
            $table->json('payment_info')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('appointment_id')->references('appointment_id')->on('appointments')->onDelete('cascade');
            $table->index(['payment_link_id', 'order_code']);
        });
    }
};
