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
                    ->summarize(
                        Summarizer::make()
                            ->label('Total')
                            ->money('BRL')
                            // Soma feita no banco, sem carregar todos os registros no PHP
                            ->using(fn (QueryBuilder $query) => DB::query()
                                ->fromSub($query->clone()->reorder(), 'v')
                                ->selectRaw('SUM(COALESCE(valor_total_desconto, 0) - COALESCE(total_custo_produtos, 0)) as lucro')
                                ->value('lucro'))
                    )
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
                    ->searchable()
                    ->label('Cliente'),

                // Filtro por funcionário
                SelectFilter::make('funcionario')
                    ->relationship('funcionario', 'nome')
                    ->searchable()
                    ->label('Funcionário'),

                // Filtro por forma de pagamento
                SelectFilter::make('forma_pgmto_id')
                    ->relationship('formaPgmto', 'nome')
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
                        ->options(fn () => \App\Models\Cliente::orderBy('nome')->pluck('nome', 'id')->toArray())
                        ->searchable()
                        ->placeholder('Todos'),
                    \Filament\Forms\Components\Select::make('funcionario_id')
                        ->label('Funcionário')
                        ->options(fn () => \App\Models\Funcionario::orderBy('nome')->pluck('nome', 'id')->toArray())
                        ->searchable()
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
                    \Filament\Forms\Components\Toggle::make('detalhado')
                        ->label('Incluir produtos de cada venda')
                        ->helperText('Deixe desligado para períodos longos: o relatório fica bem mais leve.')
                        ->default(false),
                ])
                ->requiresConfirmation()
                ->action(function (array $data, $livewire) {
                    $params = [];
                    if (!empty($data['cliente_id'])) $params['cliente_id'] = $data['cliente_id'];
                    if (!empty($data['funcionario_id'])) $params['funcionario_id'] = $data['funcionario_id'];
                    if (!empty($data['forma_pgmto_id'])) $params['forma_pgmto_id'] = $data['forma_pgmto_id'];
                    if (!empty($data['data_de'])) $params['data_de'] = $data['data_de'];
                    if (!empty($data['data_ate'])) $params['data_ate'] = $data['data_ate'];
                    if (!empty($data['detalhado'])) $params['detalhado'] = 1;
                    $queryString = http_build_query($params);
                    $url = route('relatorio.lucratividade.pdv') . ($queryString ? ('?' . $queryString) : '');
                    $livewire->js("window.open('{$url}', '_blank')");
                }),

            \Filament\Actions\Action::make('exportar_csv')
                ->label('Exportar Excel (CSV)')
                ->icon('heroicon-o-table-cells')
                ->color('info')
                ->form([
                    \Filament\Forms\Components\Select::make('cliente_id')
                        ->label('Cliente')
                        ->options(fn () => \App\Models\Cliente::orderBy('nome')->pluck('nome', 'id')->toArray())
                        ->searchable()
                        ->placeholder('Todos'),
                    \Filament\Forms\Components\Select::make('funcionario_id')
                        ->label('Funcionário')
                        ->options(fn () => \App\Models\Funcionario::orderBy('nome')->pluck('nome', 'id')->toArray())
                        ->searchable()
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
                    \Filament\Forms\Components\Toggle::make('detalhado')
                        ->label('Incluir produtos de cada venda')
                        ->default(false),
                ])
                ->action(function (array $data, $livewire) {
                    $params = array_filter([
                        'cliente_id' => $data['cliente_id'] ?? null,
                        'funcionario_id' => $data['funcionario_id'] ?? null,
                        'forma_pgmto_id' => $data['forma_pgmto_id'] ?? null,
                        'data_de' => $data['data_de'] ?? null,
                        'data_ate' => $data['data_ate'] ?? null,
                        'detalhado' => !empty($data['detalhado']) ? 1 : null,
                    ]);
                    $url = route('relatorio.lucratividade.pdv.csv') . ($params ? ('?' . http_build_query($params)) : '');
                    $livewire->js("window.open('{$url}', '_blank')");
                }),
        ];
    }
}