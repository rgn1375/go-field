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
            // Add indexes for better query performance on booking lookups
            $table->index(['lapangan_id', 'tanggal', 'status'], 'idx_booking_lookup');
            $table->index(['tanggal', 'jam_mulai', 'jam_selesai'], 'idx_time_slot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_booking_lookup');
            $table->dropIndex('idx_time_slot');
        });
    }
};
