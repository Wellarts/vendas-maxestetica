<?php

namespace App\Livewire;

use App\Models\VendaPDV;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class VendasPDVMesChart extends ChartWidget
{
    protected static ?string $heading = 'Vendas Mensal - PDV';

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {

        $query = VendaPDV::where('tipo_registro', 'VENDA');

        $data = Trend::query($query)
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->sum('valor_total_desconto');

        return [
            'datasets' => [
                [
                    'label' => 'Vendas Mensal - PDV',
                    'data'  => $data->map(fn(TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
