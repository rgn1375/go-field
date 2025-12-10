<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing 'points' values to 'manual' since we removed points system
        DB::table('bookings')
            ->where('refund_method', 'points')
            ->update(['refund_method' => 'bank_transfer']);
        
        // Modify enum to include 'manual' and remove 'points'
        DB::statement("ALTER TABLE bookings MODIFY COLUMN refund_method ENUM('manual', 'bank_transfer', 'none') DEFAULT 'none'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum
        DB::statement("ALTER TABLE bookings MODIFY COLUMN refund_method ENUM('points', 'bank_transfer', 'none') DEFAULT 'none'");
    }
};
