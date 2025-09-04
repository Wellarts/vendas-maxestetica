<?php

namespace App\Livewire;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class PagarHojeStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $mes = date('m');
        $dia = date('d');

        return [
            Stat::make('Total a Pagar', number_format(DB::table('contas_pagars')->where('status', 0)->whereDay('data_vencimento', $dia)->sum('valor_parcela'), 2, ',', '.'))
                ->description('Hoje')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('Total a Pagar', number_format(DB::table('contas_pagars')->where('status', 0)->whereMonth('data_vencimento', $mes)->sum('valor_parcela'), 2, ',', '.'))
                ->description('Este mês')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('Total Pago', number_format(DB::table('contas_pagars')->where('status', 1)->sum('valor_parcela'), 2, ',', '.'))
                ->description('Todo Período')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
        ];
    }
}
