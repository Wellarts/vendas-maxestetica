<?php

namespace App\Filament\Resources\VendaResource\RelationManagers;

use App\Models\FluxoCaixa;
use App\Models\ItensVenda;
use App\Models\Produto;
use App\Models\Venda;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;

class ItensVendaRelationManager extends RelationManager
{
    protected static string $relationship = 'ItensVenda';

    protected static ?string $title = 'Itens da Venda';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make('3')
                    ->schema([
                        Forms\Components\Hidden::make('id'),

                        Forms\Components\Hidden::make('venda_id')
                            ->default((function ($livewire): int {
                                return $livewire->ownerRecord->id;
                            })),

                        Forms\Components\Select::make('produto_id')
                            ->relationship(name: 'produto', titleAttribute: 'nome')
                            ->searchable(['nome', 'codbar'])
                            ->disableOptionWhen(fn ($context) => $context == 'edit')
                            ->columnSpan([
                                'xl'  => 2,
                                '2xl' => 2,
                            ])
                            ->live(debounce: 200)
                            ->required()
                            ->label('Produto')
                            ->afterStateUpdated(
                                function ($state, callable $set, Get $get) {
                                    $produto = Produto::find($state);

                                    if ($produto) {
                                        $set('valor_venda', $produto->valor_venda);
                                        $set('valor_custo_atual', $produto->valor_compra);
                                        $set('sub_total', (($get('qtd') * $get('valor_venda')) + (float)$get('acres_desc')));
                                        $set('estoque_atual', $produto->estoque);
                                        $set('total_custo_atual', $get('valor_custo_atual') * $get('qtd'));

                                        // Exibe notificação com nome e foto do produto
                                        Notification::make()
                                            ->title('Produto selecionado:')
                                            ->body('Produto: ' . $produto->nome . '<br> Estoque Atual: ' . $produto->estoque . ' <br> <img src="' . (is_array($produto->foto) ? asset('storage/' . ($produto->foto[0] ?? '')) : asset('storage/' . $produto->foto)) . '" alt="' . $produto->nome . '" style="max-width:100px;max-height:100px;">')
                                            ->success()
                                            ->duration(5000)
                                            ->send();
                                    }
                                }
                            ),


                        Forms\Components\TextInput::make('qtd')
                            ->default('1')
                            ->required()
                            ->live(debounce: 500)
                            ->afterStateUpdated(
                                function ($state, callable $set, Get $get) {
                                    $set('sub_total', (((float)$get('qtd') * (float)$get('valor_venda')) + (float)$get('acres_desc')));
                                    $set('total_custo_atual', $get('valor_custo_atual') * $get('qtd'));
                                }
                            ),
                        Forms\Components\TextInput::make('valor_venda')
                            ->label('Valor Unitário')
                            ->numeric()
                            ->required()
                            ->readOnly(),
                        Forms\Components\TextInput::make('sub_total')
                            ->numeric()
                            ->readOnly()
                            ->label('Total'),
                        Forms\Components\Hidden::make('valor_custo_atual'),

                        Forms\Components\Hidden::make('total_custo_atual'),


                    ]),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('venda_id')
            ->columns([
                Tables\Columns\TextColumn::make('produto.nome')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('qtd')
                    ->summarize(Sum::make()->label('Qtd de Produtos')),
                Tables\Columns\TextColumn::make('valor_venda')
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('sub_total')
                    ->summarize(Sum::make()->money('BRL')->label('Total'))
                    ->money('BRL'),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('Itens da Venda')
                    ->label('Adicionar Produtos')
                    ->hidden(fn ($livewire) => $livewire->ownerRecord->status_caixa == 1)
                    ->icon('heroicon-o-plus')
                    ->after(function ($data, $record, $livewire, Set $set) {
                        $produto = Produto::find($data['produto_id']);
                        $produto->estoque -= $data['qtd'];
                        $venda = Venda::find($data['venda_id']);
                        $venda->valor_total += $data['sub_total'];
                        $venda->save();
                        $produto->save();


                    }),
                Tables\Actions\Action::make('fluxo_caixa')
                    ->label(('Lançar no Caixa'))
                    ->icon('heroicon-o-currency-dollar')
                    ->hidden(fn ($livewire) => $livewire->ownerRecord->status_caixa == 1)
                    ->color('success')
                    ->action(function ($livewire) {
                        if ($livewire->ownerRecord->valor_total > 0) {
                            $addFluxoCaixa = [
                                'valor' => ($livewire->ownerRecord->valor_total),
                                'tipo'  => 'CREDITO',
                                'obs'   => 'Recebido da venda nº: ' . $livewire->ownerRecord->id . '',
                            ];
                            $venda               = Venda::find($livewire->ownerRecord->id);
                            $venda->status_caixa = 1;
                            $venda->save();


                            Notification::make()
                                ->title('Valor de R$' . $livewire->ownerRecord->valor_total . ' lançado no caixa com sucesso!')
                                ->success()
                                ->send();
                            FluxoCaixa::create($addFluxoCaixa);

                            return redirect()->route('filament.admin.resources.vendas.create');
                        } else {
                            Notification::make()
                                ->title('Atenção')
                                ->body('Valor da venda zerado. Adicione os produtos para depois lançar no caixa.')
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    })

                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-currency-dollar'),
                           ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function ($data) {

                        $produto      = Produto::find($data['produto_id']);
                        $idItemCompra = ItensVenda::find($data['id']);
                        $venda        = Venda::find($data['venda_id']);
                        $produto->estoque -= ($data['qtd'] - $idItemCompra->qtd);
                        $venda->valor_total += ($data['sub_total'] - $idItemCompra->sub_total);
                        $venda->save();
                        $produto->save();

                        return $data;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->after(function ($data, $record) {
                        $produto = Produto::find($record->produto_id);
                        $venda   = Venda::find($record->venda_id);
                        $venda->valor_total -= $record->sub_total;
                        $produto->estoque += ($record->qtd);
                        $venda->save();
                        $produto->save();
                    }),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                   // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
