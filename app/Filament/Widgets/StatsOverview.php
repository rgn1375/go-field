<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Lapangan;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Total Revenue (Paid bookings only)
        $totalRevenue = Booking::where('payment_status', 'paid')
            ->sum('harga');

        // Revenue this month
        $revenueThisMonth = Booking::where('payment_status', 'paid')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('harga');

        // Revenue last month
        $revenueLastMonth = Booking::where('payment_status', 'paid')
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->sum('harga');

        // Calculate revenue trend
        $revenueTrend = $revenueLastMonth > 0
            ? (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100
            : 0;

        // Total Bookings
        $totalBookings = Booking::count();
        $bookingsThisMonth = Booking::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        $bookingsLastMonth = Booking::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->count();

        $bookingsTrend = $bookingsLastMonth > 0
            ? (($bookingsThisMonth - $bookingsLastMonth) / $bookingsLastMonth) * 100
            : 0;

        // Active Users (users with bookings)
        $activeUsers = User::whereHas('bookings')->count();

        // Pending Confirmations
        $pendingPayments = Booking::where('payment_status', 'waiting_confirmation')->count();

        return [
            Stat::make('Total Pendapatan', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('Bulan ini: Rp ' . number_format($revenueThisMonth, 0, ',', '.'))
                ->descriptionIcon($revenueTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueTrend >= 0 ? 'success' : 'danger')
                ->chart($this->getRevenueChartData()),

            Stat::make('Total Booking', $totalBookings)
                ->description(($bookingsTrend >= 0 ? '+' : '') . number_format($bookingsTrend, 1) . '% dari bulan lalu')
                ->descriptionIcon($bookingsTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($bookingsTrend >= 0 ? 'success' : 'warning')
                ->chart($this->getBookingsChartData()),

            Stat::make('Pengguna Aktif', $activeUsers)
                ->description('User yang pernah booking')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Menunggu Konfirmasi', $pendingPayments)
                ->description('Pembayaran perlu dikonfirmasi')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url(route('filament.admin.resources.bookings.index', ['tableFilters[payment_status][value]' => 'waiting_confirmation'])),
        ];
    }

    private function getRevenueChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = Booking::where('payment_status', 'paid')
                ->whereDate('created_at', $date)
                ->sum('harga');
            $data[] = $revenue / 1000; // Convert to thousands for better chart scale
        }
        return $data;
    }

    private function getBookingsChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = Booking::whereDate('created_at', $date)->count();
            $data[] = $count;
        }
        return $data;
    }
}
