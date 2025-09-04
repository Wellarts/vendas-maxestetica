<?php

namespace App\Livewire;

use App\Models\Venda;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class VendasMesChart extends ChartWidget
{
    protected static ?string $heading = 'Vendas Mensal';

    protected function getData(): array
    {
        $data = Trend::model(Venda::class)
        ->between(
            start: now()->startOfYear(),
            end: now()->endOfYear(),
        )
        ->perMonth()
        ->sum('valor_total');

        return [
            'datasets' => [
                [
                    'label' => 'Vendas Mensal',
                    'data'  => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
