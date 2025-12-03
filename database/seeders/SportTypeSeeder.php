<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SportType;

class SportTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sportTypes = [
            [
                'code' => 'futsal',
                'name' => 'Futsal',
                'description' => 'Lapangan futsal indoor/outdoor dengan ukuran standar FIFA',
                'icon' => 'ai-soccer',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'code' => 'basketball',
                'name' => 'Basket',
                'description' => 'Lapangan basket indoor/outdoor dengan ukuran standar FIBA',
                'icon' => 'ai-basketball',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'code' => 'volleyball',
                'name' => 'Voli',
                'description' => 'Lapangan voli indoor/outdoor dengan ukuran standar FIVB',
                'icon' => 'ai-volleyball',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'code' => 'badminton',
                'name' => 'Badminton',
                'description' => 'Lapangan badminton indoor dengan ukuran standar BWF',
                'icon' => 'ai-badminton',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'code' => 'tennis',
                'name' => 'Tenis',
                'description' => 'Lapangan tenis indoor/outdoor dengan ukuran standar ITF',
                'icon' => 'ai-tennis',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($sportTypes as $sportType) {
            SportType::updateOrCreate(
                ['code' => $sportType['code']],
                $sportType
            );
        }
    }
}
