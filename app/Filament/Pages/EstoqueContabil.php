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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;


class EstoqueContabil extends Page implements HasForms, HasTable
{
    
    use InteractsWithTable;
    use InteractsWithForms;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['Administrador','TI']);
    }

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
        return Produto::query()
            ->where('tipo', 1)
            ->select([
                '*',
                \DB::raw('(estoque * valor_compra) as total_compra_calc'),
                \DB::raw('(estoque * valor_venda) as total_venda_calc'),
                \DB::raw('((estoque * valor_venda) - (estoque * valor_compra)) as total_lucratividade_calc'),
            ]);
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
                TextColumn::make('total_compra_calc')
                    ->label('Total Compra')
                    ->badge()
                    ->alignCenter()
                    ->money('BRL')
                    ->summarize(Sum::make()->label('Total')->money('BRL'))
                    ->color('danger'),
                TextColumn::make('total_venda_calc')
                    ->label('Total Venda')
                    ->badge()
                    ->alignCenter()
                    ->money('BRL')
                    ->summarize(Sum::make()->label('Total')->money('BRL'))
                    ->color('warning'),
                TextColumn::make('total_lucratividade_calc')
                    ->label('Total Lucratividade')
                    ->badge()
                    ->alignCenter()
                    ->money('BRL')
                    ->summarize(Sum::make()->label('Total')->money('BRL'))
                    ->color('success'),

        ];
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('Exportar PDF')
                ->label('Estoque Financeiro - PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(route('relatorio.estoque.contabil'))
                ->openUrlInNewTab()
                ->color('success'),
            Action::make('relatorio_venda_produtos')
               ->label('Relatório de Vendas por Produto')
                ->icon('heroicon-o-arrow-down-tray')
               ->color('success')
               ->form([ 
             Select::make('produto_id')
                 ->label('Produto')
                 ->options(\App\Models\Produto::where('tipo', 1)->pluck('nome', 'id'))
                 ->searchable()
                 ->preload()
                 ->required(false),
                    DatePicker::make('data_inicial')
                          ->label('Data Inicial')
                          ->required(false),
                    DatePicker::make('data_final')
                          ->label('Data Final')
                          ->required(false),
               ])
                ->action(function(array $data, $livewire) {
                    $produtoId = $data['produto_id'];
                    $dataInicial = $data['data_inicial'];
                    $dataFinal = $data['data_final'];

                    $params = [
                        'produto_id' => $produtoId,
                        'data_inicial' => $dataInicial,
                        'data_final' => $dataFinal,
                    ];

                    $queryString = http_build_query($params);
                    $url = route('relatorio.venda.produtos') . ($queryString ? ('?' . $queryString) : '');
                    $livewire->js("window.open('{$url}', '_blank')");
                })
            
        ];
    }
}
