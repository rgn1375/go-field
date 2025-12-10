<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;

class BookingStatusChart extends ChartWidget
{
    protected ?string $heading = 'Status Booking';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $statuses = [
            'pending' => 'Pending',
            'confirmed' => 'Dikonfirmasi',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        $data = [];
        $labels = [];
        $colors = [];

        $colorMap = [
            'pending' => 'rgba(251, 191, 36, 0.8)',
            'confirmed' => 'rgba(59, 130, 246, 0.8)',
            'completed' => 'rgba(16, 185, 129, 0.8)',
            'cancelled' => 'rgba(239, 68, 68, 0.8)',
        ];

        foreach ($statuses as $status => $label) {
            $count = Booking::where('status', $status)->count();
            if ($count > 0) {
                $data[] = $count;
                $labels[] = $label;
                $colors[] = $colorMap[$status];
            }
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
