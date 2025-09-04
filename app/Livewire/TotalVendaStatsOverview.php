<?php

namespace App\Livewire;

use App\Models\Venda;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TotalVendaStatsOverview extends BaseWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        $mes = date('m');
        $dia = date('d');

        return [
            Stat::make('Quantidade de Itens', DB::table('itens_vendas')->where('venda_id', $this->record->id)->sum('qtd'))
                ->description('Total')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Valor Total da Venda', number_format(Venda::all()->where('id', $this->record->id)->sum('valor_total'), 2, ',', '.'))
                ->description('Itens da Venda')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Valor Total da Venda - Desconto', number_format(Venda::all()->where('id', $this->record->id)->sum('valor_total_desconto'), 2, ',', '.'))
                ->description('Itens da Venda')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
        ];

    }
}
