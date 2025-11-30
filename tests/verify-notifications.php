#!/usr/bin/env php
<?php
/**
 * Notification System Verification Script
 * 
 * Run this script to verify all notification components are properly installed.
 * Usage: php verify-notifications.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "🔍 SportBooking Notification System Verification\n";
echo "================================================\n\n";

$errors = [];
$warnings = [];
$success = [];

// Check 1: Environment Configuration
echo "1️⃣  Checking Environment Configuration...\n";
if (config('mail.mailer') === 'smtp') {
    $success[] = "✅ MAIL_MAILER configured as 'smtp'";
} else {
    $warnings[] = "⚠️  MAIL_MAILER is '" . config('mail.mailer') . "' (expected: smtp)";
}

if (config('services.fonnte.api_key')) {
    $success[] = "✅ FONNTE_API_KEY configured";
} else {
    $warnings[] = "⚠️  FONNTE_API_KEY not configured";
}

if (config('queue.default') === 'database') {
    $success[] = "✅ Queue driver is 'database'";
} else {
    $warnings[] = "⚠️  Queue driver is '" . config('queue.default') . "'";
}

// Check 2: Database Schema
echo "\n2️⃣  Checking Database Schema...\n";
try {
    if (Schema::hasColumn('bookings', 'email')) {
        $success[] = "✅ 'email' column exists in bookings table";
    } else {
        $errors[] = "❌ 'email' column missing in bookings table";
    }
} catch (Exception $e) {
    $errors[] = "❌ Database connection failed: " . $e->getMessage();
}

// Check 3: Notification Classes
echo "\n3️⃣  Checking Notification Classes...\n";
$notificationClasses = [
    'App\Notifications\BookingConfirmed',
    'App\Notifications\BookingCancelled',
    'App\Notifications\BookingReminder',
];

foreach ($notificationClasses as $class) {
    if (class_exists($class)) {
        $success[] = "✅ {$class} exists";
        
        // Check if implements ShouldQueue
        $reflection = new ReflectionClass($class);
        if ($reflection->implementsInterface('Illuminate\Contracts\Queue\ShouldQueue')) {
            $success[] = "  └─ Implements ShouldQueue";
        } else {
            $warnings[] = "⚠️  {$class} doesn't implement ShouldQueue";
        }
    } else {
        $errors[] = "❌ {$class} not found";
    }
}

// Check 4: Custom Channel
echo "\n4️⃣  Checking Custom WhatsApp Channel...\n";
if (class_exists('App\Channels\WhatsAppChannel')) {
    $success[] = "✅ WhatsAppChannel exists";
} else {
    $errors[] = "❌ WhatsAppChannel not found";
}

// Check 5: Booking Model Configuration
echo "\n5️⃣  Checking Booking Model...\n";
try {
    $booking = new App\Models\Booking();
    
    if (in_array('Illuminate\Notifications\Notifiable', class_uses_recursive($booking))) {
        $success[] = "✅ Booking model uses Notifiable trait";
    } else {
        $errors[] = "❌ Booking model missing Notifiable trait";
    }
    
    if (method_exists($booking, 'routeNotificationForMail')) {
        $success[] = "✅ routeNotificationForMail() method exists";
    } else {
        $warnings[] = "⚠️  routeNotificationForMail() method missing";
    }
    
    if (method_exists($booking, 'routeNotificationForWhatsApp')) {
        $success[] = "✅ routeNotificationForWhatsApp() method exists";
    } else {
        $warnings[] = "⚠️  routeNotificationForWhatsApp() method missing";
    }
} catch (Exception $e) {
    $errors[] = "❌ Booking model error: " . $e->getMessage();
}

// Check 6: Console Command
echo "\n6️⃣  Checking Console Commands...\n";
if (class_exists('App\Console\Commands\SendBookingReminders')) {
    $success[] = "✅ SendBookingReminders command exists";
} else {
    $errors[] = "❌ SendBookingReminders command not found";
}

// Check 7: Queue Tables
echo "\n7️⃣  Checking Queue Tables...\n";
try {
    if (Schema::hasTable('jobs')) {
        $jobCount = DB::table('jobs')->count();
        $success[] = "✅ 'jobs' table exists (pending: {$jobCount})";
    } else {
        $errors[] = "❌ 'jobs' table missing";
    }
    
    if (Schema::hasTable('failed_jobs')) {
        $failedCount = DB::table('failed_jobs')->count();
        if ($failedCount > 0) {
            $warnings[] = "⚠️  {$failedCount} failed jobs in queue";
        } else {
            $success[] = "✅ No failed jobs";
        }
    } else {
        $errors[] = "❌ 'failed_jobs' table missing";
    }
} catch (Exception $e) {
    $errors[] = "❌ Queue tables check failed: " . $e->getMessage();
}

// Check 8: Storage Link
echo "\n8️⃣  Checking Storage Link...\n";
if (file_exists(public_path('storage'))) {
    $success[] = "✅ Storage link exists";
} else {
    $warnings[] = "⚠️  Storage link missing (run: php artisan storage:link)";
}

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 SUMMARY\n";
echo str_repeat("=", 50) . "\n\n";

if (count($success) > 0) {
    echo "✅ SUCCESS (" . count($success) . "):\n";
    foreach ($success as $msg) {
        echo "   {$msg}\n";
    }
    echo "\n";
}

if (count($warnings) > 0) {
    echo "⚠️  WARNINGS (" . count($warnings) . "):\n";
    foreach ($warnings as $msg) {
        echo "   {$msg}\n";
    }
    echo "\n";
}

if (count($errors) > 0) {
    echo "❌ ERRORS (" . count($errors) . "):\n";
    foreach ($errors as $msg) {
        echo "   {$msg}\n";
    }
    echo "\n";
    echo "🔧 Please fix the errors above before testing notifications.\n\n";
    exit(1);
} else {
    if (count($warnings) > 0) {
        echo "⚠️  System functional but has warnings. Check configuration.\n\n";
        exit(0);
    } else {
        echo "🎉 All checks passed! Notification system ready.\n\n";
        echo "Next steps:\n";
        echo "  1. Configure SMTP credentials in .env\n";
        echo "  2. Configure Fonnte API key in .env\n";
        echo "  3. Run: composer dev (or php artisan queue:work)\n";
        echo "  4. Test booking via frontend: http://127.0.0.1:8000\n";
        echo "  5. Read NOTIFICATION_TESTING_GUIDE.md for full testing\n\n";
        exit(0);
    }
}
