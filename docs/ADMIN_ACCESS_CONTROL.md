# Admin Access Control

## ✅ Implementasi

Sistem GoField sekarang memiliki kontrol akses admin yang ketat. Hanya user dengan `is_admin = true` yang bisa mengakses panel admin Filament.

## 🔐 Cara Kerja

### 1. **Database**
Kolom `is_admin` (boolean) ditambahkan ke tabel `users`:
- `true` = Bisa akses `/admin`
- `false` = Tidak bisa akses `/admin` (redirect ke login)

### 2. **Model User**
Method `canAccessPanel()` di `app/Models/User.php`:
```php
public function canAccessPanel(\Filament\Panel $panel): bool
{
    return $this->is_admin === true;
}
```

### 3. **Default Admin**
Seeder otomatis set admin:
```php
'email' => 'admin@admin.com'
'is_admin' => true
```

## 👤 Admin Saat Ini

**Administrator**
- Email: admin@admin.com
- Password: admin123
- is_admin: ✅ true

**User Lainnya**
- is_admin: ❌ false (tidak bisa akses admin panel)

## 🛠️ Menambah Admin Baru

### Opsi 1: Via Script
```bash
php set-admin.php
```
Edit script untuk tambah email lain.

### Opsi 2: Via Tinker
```bash
php artisan tinker
```
```php
$user = User::where('email', 'newemail@example.com')->first();
$user->is_admin = true;
$user->save();
```

### Opsi 3: Via Database
```sql
UPDATE users SET is_admin = 1 WHERE email = 'newemail@example.com';
```

## 🧪 Testing

1. **Login sebagai admin** (`admin@admin.com`) → ✅ Bisa akses `/admin`
2. **Login sebagai user biasa** (`user@test.com`) → ❌ Redirect/forbidden di `/admin`

## 📝 Migration

File: `database/migrations/2025_11_20_122843_add_is_admin_to_users_table.php`

Rollback jika perlu:
```bash
php artisan migrate:rollback --step=1
```

## 🔒 Security

- ✅ Filament otomatis check `canAccessPanel()` sebelum izinkan akses
- ✅ Semua user baru default `is_admin = false`
- ✅ Hanya admin yang bisa manage bookings, users, settings, dll
- ✅ User biasa tetap bisa akses dashboard mereka di `/dashboard`

## ⚠️ Important Notes

- Jangan set `is_admin = true` untuk user random
- Backup database sebelum edit access control
- Test dengan user non-admin setelah implementasi
- Default seeder selalu create 1 admin (admin@admin.com)
