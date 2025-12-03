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
            // Add payment_method_id foreign key (nullable because booking can be created without payment first)
            $table->foreignId('payment_method_id')->nullable()->after('harga')->constrained('payment_methods')->onDelete('restrict');
            
            // Drop old payment_method enum column
            $table->dropColumn('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Restore payment_method enum
            $table->enum('payment_method', ['cash', 'bank_transfer', 'qris', 'e_wallet'])->nullable()->after('harga');
            
            // Drop foreign key and column
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn('payment_method_id');
        });
    }
};
