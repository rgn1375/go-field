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
        Schema::table('bookings', function (Blueprint $table) {
            // Payment method: cash, bank_transfer, qris, e_wallet
            $table->enum('payment_method', ['cash', 'bank_transfer', 'qris', 'e_wallet'])->nullable()->after('harga');
            
            // Payment status: unpaid, waiting_confirmation, paid, refunded
            $table->enum('payment_status', ['unpaid', 'waiting_confirmation', 'paid', 'refunded'])->default('unpaid')->after('payment_method');
            
            // Payment proof image
            $table->string('payment_proof')->nullable()->after('payment_status');
            
            // Payment timestamps
            $table->timestamp('paid_at')->nullable()->after('payment_proof');
            $table->timestamp('payment_confirmed_at')->nullable()->after('paid_at');
            $table->foreignId('payment_confirmed_by')->nullable()->constrained('users')->onDelete('set null')->after('payment_confirmed_at');
            
            // Payment notes (e.g., bank name, e-wallet provider, admin notes)
            $table->text('payment_notes')->nullable()->after('payment_confirmed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['payment_confirmed_by']);
            $table->dropColumn([
                'payment_method',
                'payment_status',
                'payment_proof',
                'paid_at',
                'payment_confirmed_at',
                'payment_confirmed_by',
                'payment_notes',
            ]);
        });
    }
};
