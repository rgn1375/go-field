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
        Schema::table('lapangan', function (Blueprint $table) {
            // Operational hours per field (nullable = use global settings)
            $table->time('jam_buka')->nullable()->after('price');
            $table->time('jam_tutup')->nullable()->after('jam_buka');
            
            // Days of operation (JSON array: [1,2,3,4,5,6,7] where 1=Monday, 7=Sunday)
            // Null = operate all days
            $table->json('hari_operasional')->nullable()->after('jam_tutup');
            
            // Maintenance schedule
            $table->boolean('is_maintenance')->default(false)->after('hari_operasional');
            $table->date('maintenance_start')->nullable()->after('is_maintenance');
            $table->date('maintenance_end')->nullable()->after('maintenance_start');
            $table->text('maintenance_reason')->nullable()->after('maintenance_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lapangan', function (Blueprint $table) {
            $table->dropColumn([
                'jam_buka',
                'jam_tutup',
                'hari_operasional',
                'is_maintenance',
                'maintenance_start',
                'maintenance_end',
                'maintenance_reason',
            ]);
        });
    }
};
