<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Lapangan;
use App\Models\Booking;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // STEP 1: Seed sport types and payment methods first
        $this->call([
            SportTypeSeeder::class,
            PaymentMethodSeeder::class,
        ]);

        // Create Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('admin123'),
                'phone' => '081234567890',
                'address' => 'Jl. Admin No. 123, Jakarta',
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

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

        // Seed Lapangan
        // Get sport types for reference
        $futsalType = \App\Models\SportType::where('code', 'futsal')->first();
        $basketballType = \App\Models\SportType::where('code', 'basketball')->first();
        $volleyballType = \App\Models\SportType::where('code', 'volleyball')->first();
        $badmintonType = \App\Models\SportType::where('code', 'badminton')->first();
        $tennisType = \App\Models\SportType::where('code', 'tennis')->first();

        $lapangan = [
            // Futsal Courts
            [
                'title' => 'Lapangan Futsal Premium A',
                'sport_type_id' => $futsalType->id,
                'description' => '<p>Lapangan futsal premium dengan fasilitas lengkap dan rumput sintetis berkualitas tinggi. Cocok untuk pertandingan resmi dan turnamen.</p>',
                'price' => 300000,
                'image' => json_encode(['lapangan-images/01K5P4DDNHJ8QF2EQP22H47WGB.jpg']),
                'status' => 1,
            ],
            [
                'title' => 'Lapangan Futsal Standard B',
                'sport_type_id' => $futsalType->id,
                'description' => '<p>Lapangan futsal standard dengan fasilitas dasar yang memadai. Harga terjangkau untuk bermain santai bersama teman.</p>',
                'price' => 200000,
                'image' => json_encode(['lapangan-images/01K5P4DDNMWD6H5H3W69WKP463.jpg']),
                'status' => 1,
            ],
            // Basketball Courts
            [
                'title' => 'Lapangan Basket Indoor',
                'sport_type_id' => $basketballType->id,
                'description' => '<p>Lapangan basket indoor dengan lantai kayu berkualitas profesional. Dilengkapi ring standar dan sistem pencahayaan optimal untuk permainan kompetitif.</p>',
                'price' => 350000,
                'image' => json_encode(['lapangan-images/01K5P6FQYV037RKX8GJESZ3SC1.png']),
                'status' => 1,
            ],
            // Volleyball Courts
            [
                'title' => 'Lapangan Volly Outdoor',
                'sport_type_id' => $volleyballType->id,
                'description' => '<p>Lapangan voli outdoor dengan net standar internasional. Permukaan lantai berkualitas tinggi untuk kenyamanan bermain maksimal.</p>',
                'price' => 150000,
                'image' => json_encode(['lapangan-images/01K5P6FQYW9YRNQTHEX0PBVPQW.png']),
                'status' => 1,
            ],
            // Badminton Courts
            [
                'title' => 'Lapangan Badminton Premium',
                'sport_type_id' => $badmintonType->id,
                'description' => '<p>Lapangan badminton indoor dengan lantai kayu dan sistem ventilasi terbaik. Net dan perlengkapan standar BWF untuk pengalaman bermain profesional.</p>',
                'price' => 100000,
                'image' => json_encode(['lapangan-images/01K5P4DDNHJ8QF2EQP22H47WGB.jpg']),
                'status' => 1,
            ],
            // Tennis Courts
            [
                'title' => 'Lapangan Tennis Hard Court',
                'sport_type_id' => $tennisType->id,
                'description' => '<p>Lapangan tenis hard court dengan permukaan akrilik berkualitas tinggi. Dilengkapi net profesional dan pencahayaan untuk sesi malam hari.</p>',
                'price' => 400000,
                'image' => json_encode(['lapangan-images/01K5P4DDNMWD6H5H3W69WKP463.jpg']),
                'status' => 1,
            ],
        ];

        foreach ($lapangan as $lap) {
            \App\Models\Lapangan::updateOrCreate(
                ['title' => $lap['title']],
                $lap
            );
        }

        // Create Test Users
        $newUser = User::updateOrCreate(
            ['email' => 'user@test.com'],
            [
                'name' => 'New User',
                'password' => bcrypt('password'),
                'phone' => '081234567891',
                'address' => 'Jl. User Baru No. 1, Bandung',
                
                'email_verified_at' => now(),
            ]
        );

        $regularUser = User::updateOrCreate(
            ['email' => 'regular@test.com'],
            [
                'name' => 'Regular User',
                'password' => bcrypt('password'),
                'phone' => '081234567892',
                'address' => 'Jl. Pemain Rutin No. 5, Surabaya',
                
                'email_verified_at' => now(),
            ]
        );

        $vipUser = User::updateOrCreate(
            ['email' => 'vip@test.com'],
            [
                'name' => 'VIP User',
                'password' => bcrypt('password'),
                'phone' => '081234567893',
                'address' => 'Jl. Pelanggan Setia No. 10, Yogyakarta',
                
                'email_verified_at' => now(),
            ]
        );

        // Get lapangan for bookings
        $lapanganFutsal = Lapangan::where('sport_type_id', $futsalType->id)->first();
        $lapanganBasket = Lapangan::where('sport_type_id', $basketballType->id)->first();
        $lapanganBadminton = Lapangan::where('sport_type_id', $badmintonType->id)->first();

        // Create sample bookings
        
        // PAST COMPLETED BOOKING - Regular User
        $pastBooking = Booking::create([
            'lapangan_id' => $lapanganFutsal->id,
            'user_id' => $regularUser->id,
            'tanggal' => Carbon::now()->subDays(7)->format('Y-m-d'),
            'jam_mulai' => '10:00',
            'jam_selesai' => '11:00',
            'nama_pemesan' => $regularUser->name,
            'nomor_telepon' => $regularUser->phone,
            'email' => $regularUser->email,
            'harga' => 300000,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        // UPCOMING CONFIRMED BOOKING - Regular User
        $upcomingBooking = Booking::create([
            'lapangan_id' => $lapanganBasket->id,
            'user_id' => $regularUser->id,
            'tanggal' => Carbon::now()->addDays(3)->format('Y-m-d'),
            'jam_mulai' => '15:00',
            'jam_selesai' => '16:00',
            'nama_pemesan' => $regularUser->name,
            'nomor_telepon' => $regularUser->phone,
            'email' => $regularUser->email,
            'harga' => 350000,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        // CANCELLED BOOKING - Regular User
        $cancelledBooking = Booking::create([
            'lapangan_id' => $lapanganBadminton->id,
            'user_id' => $regularUser->id,
            'tanggal' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'jam_mulai' => '18:00',
            'jam_selesai' => '19:00',
            'nama_pemesan' => $regularUser->name,
            'nomor_telepon' => $regularUser->phone,
            'email' => $regularUser->email,
            'harga' => 200000,
            'status' => 'cancelled',
            'cancellation_reason' => 'User request',
        ]);

        // VIP USER BOOKINGS (multiple past bookings)
        $vipBookings = [
            [
                'date' => Carbon::now()->subDays(30),
                'lapangan' => $lapanganFutsal,
                'time' => '08:00-09:00',
                'harga' => 300000,
            ],
            [
                'date' => Carbon::now()->subDays(20),
                'lapangan' => $lapanganBasket,
                'time' => '10:00-11:00',
                'harga' => 350000,
            ],
            [
                'date' => Carbon::now()->subDays(10),
                'lapangan' => $lapanganFutsal,
                'time' => '14:00-15:00',
                'harga' => 300000,
            ],
        ];

        foreach ($vipBookings as $vipData) {
            Booking::create([
                'lapangan_id' => $vipData['lapangan']->id,
                'user_id' => $vipUser->id,
                'tanggal' => $vipData['date']->format('Y-m-d'),
                'jam_mulai' => explode('-', $vipData['time'])[0],
                'jam_selesai' => explode('-', $vipData['time'])[1],
                'nama_pemesan' => $vipUser->name,
                'nomor_telepon' => $vipUser->phone,
                'email' => $vipUser->email,
                'harga' => $vipData['harga'],
                'status' => 'completed',
                'payment_status' => 'paid',
            ]);
        }

        // VIP User - Upcoming booking
        Booking::create([
            'lapangan_id' => $lapanganBasket->id,
            'user_id' => $vipUser->id,
            'tanggal' => Carbon::now()->addDays(2)->format('Y-m-d'),
            'jam_mulai' => '16:00',
            'jam_selesai' => '17:00',
            'nama_pemesan' => $vipUser->name,
            'nomor_telepon' => $vipUser->phone,
            'email' => $vipUser->email,
            'harga' => 350000,
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        // Guest bookings (no user_id) - showing system works for both guest and authenticated
        Booking::create([
            'lapangan_id' => $lapanganFutsal->id,
            'user_id' => null,
            'tanggal' => Carbon::now()->addDays(4)->format('Y-m-d'),
            'jam_mulai' => '12:00',
            'jam_selesai' => '13:00',
            'nama_pemesan' => 'Guest Booking Test',
            'nomor_telepon' => '081999999999',
            'email' => 'guest@example.com',
            'harga' => 300000,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            
        ]);

        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('📊 Created:');
        $this->command->info('   - 1 Admin user (admin@admin.com / admin123)');
        $this->command->info('   - 3 Test users:');
        $this->command->info('     • user@test.com (no bookings)');
        $this->command->info('     • regular@test.com (3 bookings)');
        $this->command->info('     • vip@test.com (4 bookings)');
        $this->command->info('   - 6 Sports facilities (Futsal, Basket, Volly, Badminton, Tennis)');
        $this->command->info('   - 8 Sample bookings (past, upcoming, cancelled)');
    }
}
