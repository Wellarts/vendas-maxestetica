<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ProdutoResource;
use App\Models\Produto;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Filament\Actions\Action;

class EstoqueContabil extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    // protected static string $resource = ProdutoResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.estoque-contabil';

    protected static ?string $navigationGroup = 'Consultas';

    protected static ?string $navigationLabel = 'Estoque Financeiro';

    protected static ?string $title = 'Estoque Financeiro';

    protected static ?int $navigationSort = 16;

    // Removido o mount para evitar processamento desnecessário a cada acesso da página

    public function exportarPdf(): Response
    {
    $produtos = Produto::where('tipo', 1)->orderBy('nome', 'asc')->get();
    $totais = Produto::where('tipo', 1)
        ->selectRaw('
            SUM(estoque) as somaEstoque,
            SUM(valor_compra) as somaValorCompra,
            SUM(valor_venda) as somaValorVenda,
            SUM(estoque * valor_compra) as somaTotalCompra,
            SUM(estoque * valor_venda) as somaTotalVenda,
            SUM((estoque * valor_venda) - (estoque * valor_compra)) as somaTotalLucratividade
        ')
        ->first();

    $pdf = Pdf::loadView('relatorios.estoque-contabil-pdf', compact('produtos', 'totais'))
        ->setPaper('a4', 'landscape');

    return $pdf->stream('estoque-contabil.pdf');
    }

    protected function getTableQuery(): Builder
    {
        return Produto::query()->where('tipo', 1);
    }

    protected function getTableColumns(): array
    {
        return [
                TextColumn::make('nome')
                    ->label('Produto')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('codbar')
                    ->label('Código de Barras')
                    ->sortable()
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('estoque')
                    ->alignCenter(),
                TextColumn::make('valor_compra')
                    ->money('BRL'),
                TextColumn::make('lucratividade')
                    ->alignCenter()
                    ->label('Lucratividade (%)'),
                TextColumn::make('valor_venda')
                    ->alignCenter()
                    ->money('BRL'),
                TextColumn::make('total_compra')
                    ->badge()
                    ->alignCenter()
                    ->getStateUsing(function (Produto $record): float {
                        return (($record->estoque * $record->valor_compra));
                    })
                    ->money('BRL')
                    ->summarize(Sum::make()->label('Total')->money('BRL'))
                    ->color('danger'),
                TextColumn::make('total_venda')
                    ->badge()
                    ->alignCenter()
                    ->getStateUsing(function (Produto $record): float {
                        return ($record->estoque * $record->valor_venda);
                    })
                    ->money('BRL')
                    ->summarize(Sum::make()->label('Total')->money('BRL'))
                    ->color('warning'),
                TextColumn::make('total_lucratividade')
                    ->badge()
                    ->alignCenter()
                    ->getStateUsing(function (Produto $record): float {
                        return ((($record->estoque * $record->valor_venda)) - (($record->estoque * $record->valor_compra)));
                    })
                    ->summarize(Sum::make()->label('Total')->money('BRL'))
                    ->color('success')
                    ->money('BRL'),

        ];
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('Exportar PDF')
                ->label('Exportar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(route('relatorio.estoque.contabil'))
                ->openUrlInNewTab()
                ->color('success'),
        ];
    }
}
