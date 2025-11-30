<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->boot();

use App\Models\Lapangan;

echo "=== CHECKING IMAGES ===\n\n";

// Check public images (galeri)
echo "1. PUBLIC IMAGES:\n";
$publicImages = [
    'images/galeri-1.jpg',
    'images/galeri-2.jpg', 
    'images/lapangan-1.png',
    'images/header.png'
];

foreach ($publicImages as $img) {
    $path = public_path($img);
    $exists = file_exists($path) ? '✅' : '❌';
    echo "  $exists $img\n";
}

echo "\n2. STORAGE IMAGES:\n";
$storagePath = storage_path('app/public');
echo "  Storage path: $storagePath\n";
$storagePublicPath = public_path('storage');
echo "  Public storage link: $storagePublicPath\n";
echo "  Link exists: " . (is_link($storagePublicPath) ? '✅' : '❌') . "\n";

if (file_exists($storagePath)) {
    $files = glob($storagePath . '/*');
    echo "  Files in storage/app/public:\n";
    foreach ($files as $file) {
        echo "    - " . basename($file) . "\n";
    }
    
    $lapanganImages = glob($storagePath . '/lapangan-images/*');
    echo "  Files in lapangan-images:\n";
    foreach ($lapanganImages as $file) {
        echo "    - " . basename($file) . "\n";
    }
}

echo "\n3. LAPANGAN DATABASE DATA:\n";
$lapangans = Lapangan::all();
foreach ($lapangans as $lapangan) {
    echo "  ID: {$lapangan->id}, Title: {$lapangan->title}\n";
    echo "  Image data: " . json_encode($lapangan->image) . "\n";
    echo "  Images accessor: " . json_encode($lapangan->images) . "\n";
    echo "  ---\n";
}