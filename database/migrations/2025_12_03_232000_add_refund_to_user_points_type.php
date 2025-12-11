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
        // This migration is no longer needed since we added 'refund'
        // directly to the create_user_points_table migration.
        // Keeping it for existing databases that already ran the original migration.

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE user_points MODIFY COLUMN type ENUM('earned', 'redeemed', 'adjusted', 'refund') DEFAULT 'earned'");
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: Add new enum value if not exists
            DB::statement("
                DO $$
                BEGIN
                    IF NOT EXISTS (
                        SELECT 1 FROM pg_enum
                        WHERE enumlabel = 'refund'
                        AND enumtypid = (
                            SELECT oid FROM pg_type WHERE typname = 'user_points_type_enum'
                        )
                    ) THEN
                        ALTER TYPE user_points_type_enum ADD VALUE 'refund';
                    END IF;
                END
                $$;
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot remove enum values in PostgreSQL without recreating the type
        // For MySQL only
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE user_points MODIFY COLUMN type ENUM('earned', 'redeemed', 'adjusted') DEFAULT 'earned'");
        }
        // PostgreSQL: Cannot safely remove enum value, skip
    }
};
