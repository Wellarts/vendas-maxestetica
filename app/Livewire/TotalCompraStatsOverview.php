<?php

namespace App\Livewire;

use App\Models\Compra;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class TotalCompraStatsOverview extends BaseWidget
{
    public $record;

    protected function getStats(): array
    {
        $mes = date('m');
        $dia = date('d');

        return [
            Stat::make('Quantidade de Itens', number_format(DB::table('itens_compras')->where('compra_id', $this->record->id)->sum('qtd'), 2, ',', '.'))
                ->description('total')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Valor Total da Compra', number_format(Compra::all()->where('id', $this->record->id)->sum('valor_total'), 2, ',', '.'))
                ->description('Itens da Venda')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

        ];
    }

}
