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
            // Add sport_type_id foreign key
            $table->foreignId('sport_type_id')->nullable()->after('id')->constrained('sport_types')->onDelete('restrict');
            
            // Drop old category enum column (will be replaced by relationship)
            $table->dropColumn('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lapangan', function (Blueprint $table) {
            // Restore category enum
            $table->enum('category', ['Futsal', 'Badminton', 'Basket', 'Volly', 'Tennis'])->after('title');
            
            // Drop foreign key and column
            $table->dropForeign(['sport_type_id']);
            $table->dropColumn('sport_type_id');
        });
    }
};
