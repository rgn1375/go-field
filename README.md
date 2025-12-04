# 🏟️ GoField - Multi-Sport Field Booking System

A modern, production-ready sports facility booking platform built with Laravel 12, Filament 4, and Livewire 2. Features include user authentication, point rewards system, real-time booking, and comprehensive admin panel.

![Laravel](https://img.shields.io/badge/Laravel-12.x-red?logo=laravel)
![Filament](https://img.shields.io/badge/Filament-4.x-orange?logo=laravel)
![Livewire](https://img.shields.io/badge/Livewire-2.x-blue?logo=laravel)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.x-teal?logo=tailwindcss)

---

## ✨ Features

### 🎯 User Features
- ✅ **Multi-Sport Booking**: Futsal, Basketball, Volleyball, Badminton, Tennis
- ✅ **Authentication System**: Laravel Breeze with enhanced profile management
- ✅ **Point Rewards**: Earn 1% points from every booking, redeem for discounts
- ✅ **Real-Time Availability**: Live slot checking with Livewire
- ✅ **User Dashboard**: Track upcoming, past, and cancelled bookings
- ✅ **Profile Management**: Update personal info and view point history
- ✅ **Auto-fill Booking**: Seamless experience for authenticated users
- ✅ **Guest Booking**: Book without account registration
- ✅ **Booking Cancellation**: Cancel with automatic point refund

### 🔧 Admin Features
- ✅ **Filament Admin Panel**: Modern, intuitive interface at `/admin`
- ✅ **User Management**: 
  - View all users with point balances
  - Manually adjust points (add/deduct with reason)
  - View user booking history
  - Track point transaction history
  - Filter by verified status and booking activity
- ✅ **Booking Management**:
  - Complete CRUD operations
  - Status management (pending/confirmed/completed/cancelled)
  - Cancel with reason and auto-refund
  - Search and filter capabilities
- ✅ **Facility Management**: Manage lapangan with image gallery
- ✅ **Settings**: Configure operating hours dynamically
- ✅ **Multi-channel Notifications**: Email + WhatsApp via Fonnte

### 📊 Point System
- 🎁 **Earn**: 1% of booking price as points
- 💰 **Redeem**: 100 points = Rp 1,000 discount
- 🎯 **Max Discount**: 50% of booking price
- 🔄 **Auto-Refund**: Points returned on cancellation
- 📝 **History**: Complete transaction tracking

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+ & npm
- SQLite (or MySQL/PostgreSQL)

### Installation

```bash
# Clone repository
git clone https://github.com/yourusername/gofield.git
cd gofield

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
touch database/database.sqlite
php artisan migrate:fresh --seed

# Storage link
php artisan storage:link

# Build assets
npm run build

# Start server
php artisan serve
```

### Development Mode

```bash
# Run all services (server, queue, logs, vite)
composer dev

# OR run individually:
php artisan serve          # Server at http://localhost:8000
npm run dev                # Vite dev server
php artisan queue:listen   # Queue worker for notifications
php artisan pail           # Real-time logs
```

## 🎨 Tech Stack

### Backend
- **Laravel 12**: PHP framework
- **Filament 4**: Admin panel builder
- **Livewire 2**: Real-time components
- **Laravel Breeze**: Authentication scaffolding
- **SQLite**: Default database (MySQL/PostgreSQL supported)

### Frontend
- **Tailwind CSS 4**: Utility-first CSS
- **Alpine.js**: Minimal JavaScript framework
- **Akar Icons**: Icon library
- **Google Fonts (Inter)**: Typography

---

## 📖 Documentation

- **[Testing Guide](TESTING_GUIDE.md)**: Comprehensive testing instructions
- **[Notification Testing](NOTIFICATION_TESTING_GUIDE.md)**: Test email & WhatsApp
- **[Copilot Instructions](.github/copilot-instructions.md)**: Development context

---

## 🧪 Testing

### Run Tests
```bash
composer test
```

### Manual Testing
See [TESTING_GUIDE.md](TESTING_GUIDE.md) for detailed testing scenarios covering:
- User registration and authentication
- Booking flow (guest & authenticated)
- Point earning and redemption
- Admin panel features
- Notification system

---

## 🔔 Notifications

### Email Setup
Configure SMTP in `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS="noreply@gofield.com"
MAIL_FROM_NAME="GoField"
```

### WhatsApp Setup (Fonnte)
```env
FONNTE_API_KEY=your_api_key_here
```

### Scheduled Notifications
```bash
# Add to crontab for H-24 reminders
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🎯 Key Features Explained

### Point System Flow
1. **Booking Created** → User books facility
2. **Booking Confirmed** → Admin confirms (or auto-confirmed)
3. **Booking Completed** → User plays, earns 1% points
4. **Point Redemption** → Use points for discount on next booking
5. **Cancellation Refund** → Redeemed points automatically refunded

### Real-Time Slot Checking
- Livewire component loads booked slots from database
- Generates available time slots (1-hour intervals)
- Visual feedback: Green (available), Red (booked), Gray (out of hours)
- Prevents double-booking with overlap detection

### Admin Point Management
- **View Balance**: See current points for any user
- **Adjust Points**: Add bonus or deduct with reason
- **Transaction History**: Complete audit trail
- **Booking Integration**: Link points to specific bookings

---

## 🐛 Troubleshooting

### Common Issues

**1. Blank Admin Login**
```bash
php artisan config:clear
php artisan view:clear
```

**2. Assets Not Loading**
```bash
npm run build
php artisan storage:link
```

**3. Queue Jobs Not Processing**
```bash
php artisan queue:listen
# OR set QUEUE_CONNECTION=sync in .env for sync processing
```

**4. Notification Not Sending**
- Check `.env` configuration
- Verify queue worker is running
- Check `storage/logs/laravel.log`

---

## 🚀 Deployment

### Deploy to Laravel Cloud (Recommended)

Laravel Cloud provides one-click deployment with auto-scaling, managed database, and queue workers.

```bash
# Quick deploy
./deploy-laravel-cloud.ps1   # Windows
./deploy-laravel-cloud.sh    # Linux/Mac
```

**Complete Guide**: See [LARAVEL_CLOUD_DEPLOYMENT.md](LARAVEL_CLOUD_DEPLOYMENT.md)

**Quick Steps**:
1. Create account at [cloud.laravel.com](https://cloud.laravel.com)
2. Connect GitHub repository: `rgn1375/go-field`
3. Configure environment variables
4. Click "Deploy Now"

**What Laravel Cloud handles automatically**:
- ✅ Database (MySQL)
- ✅ Queue Workers (2 processes)
- ✅ Cron Jobs (Laravel Scheduler)
- ✅ SSL/TLS certificates
- ✅ Auto-scaling
- ✅ Monitoring & Logs
- ✅ Automatic backups

### Manual Production Deployment

<details>
<summary>Click to expand manual deployment guide</summary>

#### Production Checklist
- Set `APP_ENV=production` in `.env`
- Set `APP_DEBUG=false`
- Run `php artisan config:cache`
- Run `php artisan route:cache`
- Run `php artisan view:cache`
- Run `npm run build`
- Set up queue worker (Supervisor recommended)
- Configure cron for scheduler
- Set up database backups
- Configure proper SMTP/WhatsApp credentials

#### Environment Variables
```env
APP_NAME=GoField
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gofield
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
```

</details>

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📜 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 🙏 Acknowledgments

- **Laravel**: The PHP framework for web artisans
- **Filament**: Beautiful admin panel framework
- **Livewire**: Magical frontend framework
- **Tailwind CSS**: Utility-first CSS framework

---

**Built with ❤️ using Laravel & Filament**

