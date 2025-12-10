<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Lapangan;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class PopularLapanganChart extends ChartWidget
{
    protected ?string $heading = 'Lapangan Terpopuler';

    protected static ?int $sort = 3;

    public ?string $filter = '30';

    protected function getData(): array
    {
        $days = (int) $this->filter;

        $popularLapangan = Booking::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->selectRaw('lapangan_id, COUNT(*) as total_bookings')
            ->groupBy('lapangan_id')
            ->orderByDesc('total_bookings')
            ->limit(10)
            ->get();

        $labels = [];
        $data = [];

        foreach ($popularLapangan as $booking) {
            $lapangan = Lapangan::find($booking->lapangan_id);
            if ($lapangan) {
                $labels[] = $lapangan->name;
                $data[] = $booking->total_bookings;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Booking',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(14, 165, 233, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return [
            '7' => '7 Hari',
            '30' => '30 Hari',
            '90' => '90 Hari',
            '365' => '1 Tahun',
        ];
    }
}

