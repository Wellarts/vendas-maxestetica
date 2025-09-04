<?php

namespace App\Livewire;

use App\Models\Compra;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class CompraStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $mes = date('m');
        $dia = date('d');

        return [
            Stat::make('Total da Compra', number_format(Compra::all()->sum('valor_total'), 2, ',', '.'))
                ->description('Todo Perído')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Total da Compra', number_format(DB::table('compras')->whereMonth('data_compra', $mes)->sum('valor_total'), 2, ',', '.'))
                ->description('Este mês')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Total da Compra', number_format(DB::table('compras')->whereDay('data_compra', $dia)->sum('valor_total'), 2, ',', '.'))
                ->description('Hoje')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

        ];
    }
}
