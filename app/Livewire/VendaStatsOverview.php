<?php

namespace App\Livewire;

use App\Models\Venda;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class VendaStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $ano = date('Y');
        $mes = date('m');
        $dia = date('d');

        // dd($ano);
        return [
            Stat::make('Total de Vendas', number_format(Venda::all()->sum('valor_total'), 2, ',', '.'))
                ->description('Todo Perído')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Total de Vendas', number_format(DB::table('vendas')->whereYear('data_venda', $ano)->whereMonth('data_venda', $mes)->sum('valor_total'), 2, ',', '.'))
                ->description('Este mês')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Total de Vendas', number_format(DB::table('vendas')->whereYear('data_venda', $ano)->whereMonth('data_venda', $mes)->whereDay('data_venda', $dia)->sum('valor_total'), 2, ',', '.'))
                ->description('Hoje')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
        ];
    }
}
