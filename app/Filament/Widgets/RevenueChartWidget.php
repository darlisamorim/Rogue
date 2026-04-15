<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Receita dos últimos 30 dias';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $days = collect(range(29, 0))->map(fn ($i) => Carbon::today()->subDays($i));

        $revenue = $days->map(fn ($day) =>
            Transaction::confirmed()
                ->whereDate('confirmed_at', $day)
                ->sum('net_amount')
        );

        return [
            'datasets' => [
                [
                    'label'           => 'Receita (R$)',
                    'data'            => $revenue->values()->toArray(),
                    'fill'            => true,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor'     => 'rgb(59, 130, 246)',
                    'tension'         => 0.3,
                ],
            ],
            'labels' => $days->map(fn ($d) => $d->format('d/m'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
