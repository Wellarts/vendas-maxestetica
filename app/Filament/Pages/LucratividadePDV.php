<?php

namespace App\Filament\Pages;

use App\Models\VendaPDV;
use Filament\Pages\Page;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class LucratividadePDV extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.lucratividade-p-d-v';

    protected static ?string $navigationGroup = 'Consultas';

    protected static ?string $navigationLabel = 'Lucratividade PDV';

    protected static ?string $title = 'Lucratividade PDV';

    protected static ?int $navigationSort = 18;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['Administrador', 'TI']);
    }

    public function table(Table $table): Table
    {
        // Otimiza a query para já trazer o sum do custo dos itens
        return $table
            // Página abre rápido; a tabela só carrega depois via Livewire
            ->deferLoading()
            // Sem a opção "todos", que derruba o servidor
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(25)
            ->persistFiltersInSession()
            ->defaultSort('data_venda', 'desc')
            ->query(
                VendaPDV::query()
                    ->where('tipo_registro', 'venda')
                    ->with(['cliente:id,nome', 'funcionario:id,nome', 'formaPgmto:id,nome'])
                    ->withSum('itensVenda as total_custo_produtos', 'total_custo_atual')
            )
            ->columns([
                TextColumn::make('id')
                    ->alignCenter()
                    ->searchable()
                    ->label('Venda'),
                TextColumn::make('cliente.nome')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('funcionario.nome')
                    ->label('Vendedor')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('formaPgmto.nome')
                    ->label('Forma de Pagamento')
                    ->alignCenter(),
                TextColumn::make('data_venda')
                    ->date('d/m/Y')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('total_custo_produtos')
                    ->badge()
                    ->alignCenter()
                    ->label('Custo Produtos')
                    ->money('BRL')
                    ->color('danger'),
                TextColumn::make('valor_total')
                    ->summarize(Sum::make()->money('BRL')->label('Total'))
                    ->badge()
                    ->alignCenter()
                    ->label('Valor da Venda')
                    ->money('BRL')
                    ->color('warning'),
                TextColumn::make('valor_total_desconto')
                    ->summarize(Sum::make()->money('BRL')->label('Total'))
                    ->badge()
                    ->alignCenter()
                    ->label('Venda com Desc/Acres')
                    ->money('BRL')
                    ->color('warning'),
                TextColumn::make('lucro_venda')
                    ->summarize(new class extends \Filament\Tables\Columns\Summarizers\Summarizer {
                        public function summarize(\Illuminate\Database\Query\Builder $query, string $attribute): mixed {
                            $records = $query->get();
                            $total = $records->sum(function ($record) {
                                return ($record->valor_total_desconto - ($record->total_custo_produtos ?? 0));
                            });
                            return 'R$ ' . number_format($total, 2, ',', '.');
                        }
                    })
                    ->badge()
                    ->alignCenter()
                    ->label('Lucro por Venda')
                    ->money('BRL')
                    ->color('success')
                    ->getStateUsing(function (VendaPDV $record): float {
                        // Usa o valor já carregado do withSum
                        return ($record->valor_total_desconto - ($record->total_custo_produtos ?? 0));
                    }),
            ])
            ->filters([
                // Filtro por cliente
                SelectFilter::make('cliente')
                    ->relationship('cliente', 'nome')
                    ->label('Cliente'),

                // Filtro por funcionário
                SelectFilter::make('funcionario')
                    ->relationship('funcionario', 'nome')
                    ->label('Funcionário'),

                // Filtro por forma de pagamento
                SelectFilter::make('forma_pgmto_id')
                    ->relationship('formaPgmto', 'nome', fn ($query) => $query->select('id', 'nome'))
                    ->label('Forma de Pagamento'),

                // Filtro por data (padrão: mês atual, para não somar o histórico inteiro ao abrir)
                Filter::make('data_venda')
                    ->form([
                        DatePicker::make('venda_de')
                            ->label('Data da Venda de:')
                            ->default(now()->startOfMonth()),
                        DatePicker::make('venda_ate')
                            ->label('Data da Venda até:')
                            ->default(now()),
                    ])
                    ->query(function ($query, array $data) {
                        // Sem whereDate(): comparar direto a coluna permite usar índice
                        $query
                            ->when(
                                $data['venda_de'],
                                fn ($query) => $query->where('data_venda', '>=', $data['venda_de'])
                            )
                            ->when(
                                $data['venda_ate'],
                                fn ($query) => $query->where('data_venda', '<=', $data['venda_ate'] . ' 23:59:59')
                            );
                    }),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('exportar_pdf')
                ->label('Relatório de Vendas - PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->form([
                    // Closures: as consultas só rodam ao abrir o modal, não a cada render da página
                    \Filament\Forms\Components\Select::make('cliente_id')
                        ->label('Cliente')
                        ->options(\App\Models\Cliente::orderBy('nome')->pluck('nome', 'id')->toArray())
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $search) => \App\Models\Cliente::query()
                            ->where('nome', 'like', "%{$search}%")
                            ->orderBy('nome')
                            ->limit(50)
                            ->pluck('nome', 'id'))
                        ->getOptionLabelUsing(fn ($value) => \App\Models\Cliente::find($value)?->nome)
                        ->placeholder('Todos'),
                    \Filament\Forms\Components\Select::make('funcionario_id')
                        ->label('Funcionário')
                        ->options(\App\Models\Funcionario::orderBy('nome')->pluck('nome', 'id')->toArray())
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $search) => \App\Models\Funcionario::query()
                            ->where('nome', 'like', "%{$search}%")
                            ->orderBy('nome')
                            ->limit(50)
                            ->pluck('nome', 'id'))
                        ->getOptionLabelUsing(fn ($value) => \App\Models\Funcionario::find($value)?->nome)
                        ->placeholder('Todos'),
                    \Filament\Forms\Components\Select::make('forma_pgmto_id')
                        ->label('Forma de Pagamento')
                        ->options(fn () => \App\Models\FormaPgmto::orderBy('nome')->pluck('nome', 'id')->toArray())
                        ->searchable()
                        ->placeholder('Todas'),
                    \Filament\Forms\Components\DatePicker::make('data_de')
                        ->label('Data de'),
                    \Filament\Forms\Components\DatePicker::make('data_ate')
                        ->label('Data até'),
                ])
                ->requiresConfirmation()
                ->action(function (array $data, $livewire) {
                    $params = [];
                    if (!empty($data['cliente_id'])) $params['cliente_id'] = $data['cliente_id'];
                    if (!empty($data['funcionario_id'])) $params['funcionario_id'] = $data['funcionario_id'];
                    if (!empty($data['forma_pgmto_id'])) $params['forma_pgmto_id'] = $data['forma_pgmto_id'];
                    if (!empty($data['data_de'])) $params['data_de'] = $data['data_de'];
                    if (!empty($data['data_ate'])) $params['data_ate'] = $data['data_ate'];
                    $queryString = http_build_query($params);
                    $url = route('relatorio.lucratividade.pdv') . ($queryString ? ('?' . $queryString) : '');
                    $livewire->js("window.open('{$url}', '_blank')");
                }),
        ];
    }
}