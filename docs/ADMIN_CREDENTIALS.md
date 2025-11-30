# Admin Credentials

## Login Information

**URL Admin Panel:** http://127.0.0.1:8000/admin

**Email:** admin@admin.com  
**Password:** admin123

---

## Reset Password

If you forget the admin password, run this command:

```bash
cd booking-lapangan
php artisan db:seed --class=DatabaseSeeder
```

This will create or update the admin user with the default credentials above.

---

## Create New Admin User

To create a new admin user manually, use Laravel Tinker:

```bash
php artisan tinker
```

Then run:

```php
$user = new \App\Models\User();
$user->name = 'Your Name';
$user->email = 'your@email.com';
$user->password = bcrypt('your_password');
$user->save();
```

---

## Change Password via Database

If you need to change the password directly in the database:

1. Access SQLite database at: `booking-lapangan/database/database.sqlite`
2. Update the password using bcrypt hash

Or use tinker:

```bash
php artisan tinker
```

```php
$user = \App\Models\User::where('email', 'admin@admin.com')->first();
$user->password = bcrypt('new_password');
$user->save();
```
