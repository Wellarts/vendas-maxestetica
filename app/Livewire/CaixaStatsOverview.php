<?php

namespace App\Livewire;

use App\Models\FluxoCaixa;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CaixaStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Saldo', number_format(FluxoCaixa::all()->sum('valor'), 2, ',', '.'))
                ->description('Valor atual')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),
             Stat::make('Débitos', number_format(FluxoCaixa::all()->where('valor', '<', 0)->sum('valor'), 2, ',', '.'))
                ->description('Valor atual')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('Crétidos', number_format(FluxoCaixa::all()->where('valor', '>', 0)->sum('valor'), 2, ',', '.'))
                ->description('Valor atual')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
         //   Card::make('Total de Vendas do Mês', DB::table('vendas')->whereDay('data_venda', $dia)->sum('valor_total'))
        ];
    }
}
