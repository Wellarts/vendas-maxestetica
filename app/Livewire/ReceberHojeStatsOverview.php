<?php

namespace App\Livewire;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ReceberHojeStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $ano = date('Y');
        $mes = date('m');
        $dia = date('d');

        return [

         Stat::make('Total a Receber', number_format(DB::table('contas_recebers')->where('status', 0)->whereYear('data_vencimento', $ano)->whereMonth('data_vencimento', $mes)->sum('valor_parcela'), 2, ',', '.'))
            ->description('Este mês')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success'),
        Stat::make('Total a Receber', number_format(DB::table('contas_recebers')->where('status', 0)->whereYear('data_vencimento', $ano)->whereMonth('data_vencimento', $mes)->whereDay('data_vencimento', $dia)->sum('valor_parcela'), 2, ',', '.'))
            ->description('Hoje')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success'),
        Stat::make('Total Recebido', number_format(DB::table('contas_recebers')->where('status', 1)->sum('valor_parcela'), 2, ',', '.'))
            ->description('Todo Período')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success'),

        ];
    }
}
