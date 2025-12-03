<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tambah composite index untuk cursor-based pagination pada:
     * - Riwayat booking (past tab)
     * - Booking yang dibatalkan (cancelled tab)
     *
     * Index ini mengoptimalkan query dengan ORDER BY tanggal DESC, jam_mulai DESC, id DESC
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Composite index untuk cursor pagination
            // Order: tanggal DESC, jam_mulai DESC, id DESC
            // Digunakan untuk semua tab (upcoming, past, cancelled)
            $table->index(['user_id', 'tanggal', 'jam_mulai', 'id'], 'idx_bookings_cursor_pagination');
            
            // Index untuk filter by status (untuk tab cancelled)
            $table->index(['user_id', 'status', 'tanggal', 'jam_mulai', 'id'], 'idx_bookings_status_cursor');
            
            // Index untuk tanggal saja (untuk filter date comparison)
            $table->index(['user_id', 'tanggal'], 'idx_bookings_user_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_bookings_cursor_pagination');
            $table->dropIndex('idx_bookings_status_cursor');
            $table->dropIndex('idx_bookings_user_date');
        });
    }
};
