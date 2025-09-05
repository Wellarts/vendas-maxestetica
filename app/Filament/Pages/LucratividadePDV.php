<?php

namespace App\Filament\Pages;

use App\Models\VendaPDV;
use Filament\Pages\Page;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;

class LucratividadePDV extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.lucratividade-p-d-v';

    protected static ?string $navigationGroup = 'Consultas';

    protected static ?string $navigationLabel = 'Lucratividade PDV';

    protected static ?string $title = 'Lucratividade PDV';

    protected static ?int $navigationSort = 18;

    // public static function shouldRegisterNavigation(): bool
    // {
    //      /** @var \App\Models\User */
    //      $authUser =  auth()->user();

    //      if ($authUser->hasRole('TI')) {
    //          return true;
    //      } else {
    //          return false;
    //      }
    // }


    // Removido processamento desnecessário de lucro no mount()

    public function table(Table $table): Table
    {
        // Otimiza a query para já trazer o sum do custo dos itens
        return $table
            ->defaultSort('data_venda', 'desc')
            ->query(
                VendaPDV::query()->withSum('itensVenda as total_custo_produtos', 'total_custo_atual')
            )
            // ->defaultGroup('data_venda','year')
            ->columns([
                TextColumn::make('id')
                    ->alignCenter()
                    ->searchable()
                    ->label('Venda'),
                TextColumn::make('cliente.nome')
                    ->sortable()
                    ->searchable(),
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
                    ->label('Venda com Desconto')
                    ->money('BRL')
                    ->color('warning'),
                TextColumn::make('lucro_venda')
                    ->summarize(Sum::make()->money('BRL')->label('Total'))
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
                SelectFilter::make('cliente')->relationship('cliente', 'nome'),

                Filter::make('data_vencimento')
                    ->form([
                        DatePicker::make('venda_de')
                            ->label('Data da Venda de:'),
                        DatePicker::make('venda_ate')
                            ->label('Data da Venda até:'),
                    ])
                    ->query(function ($query, array $data) {
                        $query
                            ->when(
                                $data['venda_de'],
                                fn ($query) => $query->whereDate('data_venda', '>=', $data['venda_de'])
                            )
                            ->when(
                                $data['venda_ate'],
                                fn ($query) => $query->whereDate('data_venda', '<=', $data['venda_ate'])
                            );
                    }),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('exportar_pdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->url(route('relatorio.lucratividade.pdv'), true)
                ->openUrlInNewTab(),
        ];
    }


}
