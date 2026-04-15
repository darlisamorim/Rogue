<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $totalUsers = User::count();
        $newUsersToday = User::whereDate('created_at', $today)->count();

        $revenueToday = Transaction::confirmed()
            ->whereDate('confirmed_at', $today)
            ->sum('net_amount');

        $revenueMonth = Transaction::confirmed()
            ->where('confirmed_at', '>=', $monthStart)
            ->sum('net_amount');

        $downloadsToday = Transaction::confirmed()
            ->whereIn('type', ['download', 'redownload'])
            ->whereDate('confirmed_at', $today)
            ->count();

        $pendingTransactions = Transaction::pending()->count();

        return [
            Stat::make('Usuários cadastrados', number_format($totalUsers))
                ->description("+{$newUsersToday} hoje")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info')
                ->icon('heroicon-o-users'),

            Stat::make('Receita hoje', 'R$ ' . number_format($revenueToday, 2, ',', '.'))
                ->description('Mês: R$ ' . number_format($revenueMonth, 2, ',', '.'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Downloads hoje', $downloadsToday)
                ->description("{$pendingTransactions} pagamentos pendentes")
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color($pendingTransactions > 0 ? 'warning' : 'gray')
                ->icon('heroicon-o-arrow-down-tray'),
        ];
    }
}
