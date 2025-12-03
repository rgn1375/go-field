<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'code' => 'cash',
                'name' => 'Cash',
                'description' => 'Pembayaran tunai langsung di tempat',
                'logo' => null,
                'is_active' => true,
                'config' => null,
                'admin_fee' => 0,
                'admin_fee_percentage' => 0,
                'sort_order' => 1,
            ],
            [
                'code' => 'bank_transfer',
                'name' => 'Transfer Bank',
                'description' => 'Transfer ke rekening bank',
                'logo' => null,
                'is_active' => true,
                'config' => [
                    'bank_name' => 'BCA',
                    'account_number' => '1234567890',
                    'account_name' => 'GoField Sport Center',
                ],
                'admin_fee' => 0,
                'admin_fee_percentage' => 0,
                'sort_order' => 2,
            ],
            [
                'code' => 'qris',
                'name' => 'QRIS',
                'description' => 'Pembayaran via QRIS (Quick Response Code Indonesian Standard)',
                'logo' => null,
                'is_active' => true,
                'config' => [
                    'qr_code_path' => 'payment/qris-gofield.png',
                ],
                'admin_fee' => 0,
                'admin_fee_percentage' => 0.7, // 0.7% fee for QRIS
                'sort_order' => 3,
            ],
            [
                'code' => 'e_wallet',
                'name' => 'E-Wallet',
                'description' => 'Pembayaran via e-wallet (GoPay, OVO, DANA, ShopeePay)',
                'logo' => null,
                'is_active' => true,
                'config' => [
                    'gopay' => '081234567890',
                    'ovo' => '081234567890',
                    'dana' => '081234567890',
                ],
                'admin_fee' => 0,
                'admin_fee_percentage' => 1, // 1% fee for e-wallet
                'sort_order' => 4,
            ],
            [
                'code' => 'credit_card',
                'name' => 'Kartu Kredit',
                'description' => 'Pembayaran via kartu kredit (Visa, Mastercard, JCB)',
                'logo' => null,
                'is_active' => false, // Inactive by default, can be enabled later
                'config' => null,
                'admin_fee' => 5000, // Rp 5.000 fixed fee
                'admin_fee_percentage' => 2.9, // 2.9% fee for credit card
                'sort_order' => 5,
            ],
        ];

        foreach ($paymentMethods as $paymentMethod) {
            PaymentMethod::updateOrCreate(
                ['code' => $paymentMethod['code']],
                $paymentMethod
            );
        }
    }
}
