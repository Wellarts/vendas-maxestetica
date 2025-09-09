<?php

namespace App\Livewire;

use Filament\Widgets\ChartWidget;

class TotalVendasPorCliente extends ChartWidget
{
    protected static ?string $heading = 'Clientes 10+';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Total Vendas por Cliente',
                    'data'  => \App\Models\VwTotalVendasPorCliente::query()
                        ->selectRaw('cliente_nome, valor_total_desconto as valor_total')
                        ->limit(10)
                        ->orderBy('valor_total', 'desc')
                        ->get()
                        ->pluck('valor_total')
                        ->toArray(),
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 205, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(201, 203, 207, 0.2)',
                    ],
                    'borderColor' => 'rgb(68, 70, 70)',
                ],
            ],
            'labels' => \App\Models\VwTotalVendasPorCliente::query()
                ->selectRaw('cliente_nome')
                ->limit(10)
                ->get()
                ->pluck('cliente_nome')
                ->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
