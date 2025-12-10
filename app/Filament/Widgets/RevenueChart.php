<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChart extends ChartWidget
{
    protected ?string $heading = 'Pendapatan';

    protected static ?int $sort = 2;

    public ?string $filter = '7';

    protected function getData(): array
    {
        $days = (int) $this->filter;
        $data = [];
        $labels = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = Booking::where('payment_status', 'paid')
                ->whereDate('created_at', $date)
                ->sum('harga');

            $data[] = $revenue;
            $labels[] = $date->format('d M');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan (Rp)',
                    'data' => $data,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            '7' => '7 Hari',
            '14' => '14 Hari',
            '30' => '30 Hari',
            '90' => '90 Hari',
        ];
    }
}
