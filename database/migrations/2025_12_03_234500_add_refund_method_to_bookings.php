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
            // Refund method: points (auto), bank_transfer (manual admin), none
            $table->enum('refund_method', ['points', 'bank_transfer', 'none'])
                ->default('none')
                ->after('refund_processed_at');
            
            // Refund notes for admin to track transfer details
            $table->text('refund_notes')->nullable()->after('refund_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['refund_method', 'refund_notes']);
        });
    }
};
