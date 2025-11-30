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
        // Lapangan indexes
        Schema::table('lapangan', function (Blueprint $table) {
            $table->index('status'); // Filter active lapangan
            $table->index('category'); // Filter by category
            $table->index(['status', 'category']); // Composite for filtered queries
            $table->index('created_at'); // Sorting
        });

        // Bookings indexes
        Schema::table('bookings', function (Blueprint $table) {
            $table->index('user_id'); // User's bookings
            $table->index('lapangan_id'); // Lapangan bookings
            $table->index('tanggal'); // Date filtering
            $table->index('status'); // Status filtering
            $table->index('payment_status'); // Payment filtering
            $table->index(['lapangan_id', 'tanggal', 'status']); // Composite for availability check
            $table->index(['user_id', 'status']); // User bookings by status
            $table->index(['user_id', 'created_at']); // User bookings sorted
        });

        // Users indexes (if needed for points)
        Schema::table('users', function (Blueprint $table) {
            $table->index('email'); // Login queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lapangan', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['category']);
            $table->dropIndex(['status', 'category']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['lapangan_id']);
            $table->dropIndex(['tanggal']);
            $table->dropIndex(['status']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['lapangan_id', 'tanggal', 'status']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['user_id', 'created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
        });
    }
};
