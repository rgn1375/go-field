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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique(); // TRX-YYYYMMDD-XXXXX
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('payment_method_id')->constrained('payment_methods')->onDelete('restrict');
            $table->decimal('amount', 15, 2); // Total amount paid
            $table->decimal('admin_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 15, 2); // amount + admin_fee
            $table->enum('status', ['pending', 'waiting_confirmation', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('payment_proof')->nullable(); // Image path for payment proof
            $table->text('notes')->nullable(); // Customer notes
            $table->text('admin_notes')->nullable(); // Admin notes
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('refunded_at')->nullable();
            $table->decimal('refund_amount', 15, 2)->nullable();
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index('transaction_code');
            $table->index('status');
            $table->index(['booking_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
