<?php

namespace App\Livewire;

use App\Models\Compra;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ComprasMesChart extends ChartWidget
{
    protected static ?string $heading = 'Compras Mensal';



    protected function getData(): array
    {
        $data = Trend::model(Compra::class)
        ->between(
            start: now()->startOfYear(),
            end: now()->endOfYear(),
        )
        ->perMonth()
        ->sum('valor_total');

        return [
            'datasets' => [
                [
                    'label' => 'Compras Mensal',
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
