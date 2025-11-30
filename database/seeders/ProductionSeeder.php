<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Lapangan;
use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Seed production database with essential data only.
     * Safe to run in production environment.
     */
    public function run(): void
    {
        $this->command->info('🚀 Seeding production database...');

        // Create Admin User (if not exists)
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('admin123'),
                'phone' => '081234567890',
                'address' => 'GoField Admin',
                'points_balance' => 0,
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        if ($admin->wasRecentlyCreated) {
            $this->command->info('✅ Admin user created');
        } else {
            $this->command->info('ℹ️  Admin user already exists');
        }

        // Seed Settings
        $settings = [
            ['key' => 'jam_buka', 'value' => '06:00', 'description' => 'Jam buka operasional lapangan'],
            ['key' => 'jam_tutup', 'value' => '21:00', 'description' => 'Jam tutup operasional lapangan'],
        ];
        
        foreach ($settings as $setting) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'description' => $setting['description']]
            );
        }
        $this->command->info('✅ Settings configured');

        // Seed Lapangan (Sports Facilities)
        $lapangan = [
            [
                'title' => 'Lapangan Futsal Premium A',
                'category' => 'Futsal',
                'description' => '<p>Lapangan futsal premium dengan fasilitas lengkap dan rumput sintetis berkualitas tinggi.</p>',
                'price' => 300000,
                'weekday_price' => 250000,
                'weekend_price' => 350000,
                'peak_hour_start' => '17:00',
                'peak_hour_end' => '21:00',
                'peak_hour_multiplier' => 1.5,
                'image' => json_encode([]),
                'status' => 1,
            ],
            [
                'title' => 'Lapangan Futsal Standard B',
                'category' => 'Futsal',
                'description' => '<p>Lapangan futsal standard dengan fasilitas dasar yang memadai.</p>',
                'price' => 200000,
                'weekday_price' => 180000,
                'weekend_price' => 220000,
                'image' => json_encode([]),
                'status' => 1,
            ],
            [
                'title' => 'Lapangan Basket Indoor',
                'category' => 'Basket',
                'description' => '<p>Lapangan basket indoor dengan lantai kayu berkualitas profesional.</p>',
                'price' => 350000,
                'weekday_price' => 300000,
                'weekend_price' => 400000,
                'peak_hour_start' => '18:00',
                'peak_hour_end' => '22:00',
                'peak_hour_multiplier' => 1.3,
                'image' => json_encode([]),
                'status' => 1,
            ],
            [
                'title' => 'Lapangan Volly Outdoor',
                'category' => 'Volly',
                'description' => '<p>Lapangan voli outdoor dengan net standar internasional.</p>',
                'price' => 150000,
                'weekday_price' => 120000,
                'weekend_price' => 180000,
                'image' => json_encode([]),
                'status' => 1,
            ],
            [
                'title' => 'Lapangan Badminton Premium',
                'category' => 'Badminton',
                'description' => '<p>Lapangan badminton indoor dengan lantai kayu dan sistem ventilasi terbaik.</p>',
                'price' => 100000,
                'weekday_price' => 80000,
                'weekend_price' => 120000,
                'peak_hour_start' => '17:00',
                'peak_hour_end' => '20:00',
                'peak_hour_multiplier' => 1.4,
                'image' => json_encode([]),
                'status' => 1,
            ],
            [
                'title' => 'Lapangan Tennis Hard Court',
                'category' => 'Tennis',
                'description' => '<p>Lapangan tenis hard court dengan permukaan akrilik berkualitas tinggi.</p>',
                'price' => 400000,
                'weekday_price' => 350000,
                'weekend_price' => 450000,
                'peak_hour_start' => '16:00',
                'peak_hour_end' => '20:00',
                'peak_hour_multiplier' => 1.5,
                'image' => json_encode([]),
                'status' => 1,
            ],
        ];

        $createdCount = 0;
        foreach ($lapangan as $lap) {
            $facility = Lapangan::firstOrCreate(
                ['title' => $lap['title']],
                $lap
            );
            
            if ($facility->wasRecentlyCreated) {
                $createdCount++;
            }
        }
        
        if ($createdCount > 0) {
            $this->command->info("✅ Created {$createdCount} sports facilities");
        } else {
            $this->command->info('ℹ️  Sports facilities already exist');
        }

        $this->command->newLine();
        $this->command->info('🎉 Production seeding completed successfully!');
        $this->command->newLine();
        $this->command->info('📋 Summary:');
        $this->command->info('   ✅ Admin account: admin@admin.com / admin123');
        $this->command->info('   ✅ Settings: Operational hours configured');
        $this->command->info('   ✅ Facilities: 6 sports facilities available');
        $this->command->newLine();
        $this->command->warn('⚠️  IMPORTANT: Change admin password after first login!');
        $this->command->info('   Login at: https://your-domain.com/admin');
    }
}
