<?php

namespace App\Filament\Resources\VendaPDVResource\Pages;

use App\Filament\Resources\VendaPDVResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms;

class ListVendaPDVS extends ListRecords
{
    protected static string $resource = VendaPDVResource::class;

    protected static ?string $title = 'Vendas PDV';

    protected function getHeaderActions(): array
    {
        return [
           // Actions\CreateAction::make()
           //     ->modalHeading('Vendas PDV'),
        //    Actions\Action::make('relatorio_venda_produtos')
        //        ->label('Relatório de Vendas por Produto')
        //        ->icon('heroicon-o-document-text')
        //        ->color('success')
        //        ->form([ 
        //             Forms\Components\Select::make('produto_id')
        //                   ->label('Produto')
        //                   ->relationship('itensVenda.produto', 'nome')
        //                   ->searchable()
        //                   ->preload()
        //                   ->required(false),
        //              Forms\Components\DatePicker::make('data_inicial')
        //                   ->label('Data Inicial')
        //                   ->required(false),
        //              Forms\Components\DatePicker::make('data_final')
        //                   ->label('Data Final')
        //                   ->required(false),
        //        ])
        //         ->action(function(array $data, $livewire) {
        //             $produtoId = $data['produto_id'];
        //             $dataInicial = $data['data_inicial'];
        //             $dataFinal = $data['data_final'];

        //             $params = [
        //                 'produto_id' => $produtoId,
        //                 'data_inicial' => $dataInicial,
        //                 'data_final' => $dataFinal,
        //             ];

        //             $queryString = http_build_query($params);
        //             $url = route('relatorio.venda.produtos') . ($queryString ? ('?' . $queryString) : '');
        //             $livewire->js("window.open('{$url}', '_blank')");
        //         })
            

        ];
    }
}
