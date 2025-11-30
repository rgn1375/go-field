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
            // Pricing fields
            $table->decimal('weekday_price', 10, 2)->nullable()->after('price');
            $table->decimal('weekend_price', 10, 2)->nullable()->after('weekday_price');
            
            // Peak hours configuration
            $table->time('peak_hour_start')->nullable()->after('weekend_price');
            $table->time('peak_hour_end')->nullable()->after('peak_hour_start');
            $table->decimal('peak_hour_multiplier', 3, 2)->default(1.5)->after('peak_hour_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lapangan', function (Blueprint $table) {
            $table->dropColumn([
                'weekday_price',
                'weekend_price',
                'peak_hour_start',
                'peak_hour_end',
                'peak_hour_multiplier',
            ]);
        });
    }
};
