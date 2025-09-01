<?php

namespace App\Filament\Resources\VendaResource\RelationManagers;

use App\Models\ContasReceber;
use App\Models\FluxoCaixa;
use App\Models\Venda;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContasReceberRelationManager extends RelationManager
{
    protected static string $relationship = 'ContasReceber';

    protected static ?string $title = 'Contas a Receber';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make('4')
                    ->schema([
                        Forms\Components\TextInput::make('venda_id')
                            ->hidden()
                            ->required(),
                        Forms\Components\Select::make('cliente_id')
                            ->columnSpan([
                                'xl' => 2,
                                '2xl' => 2,
                            ])
                            ->label('Cliente')
                            ->default((function ($livewire): int {
                                return $livewire->ownerRecord->cliente_id;
                            }))

                            ->options(function (RelationManager $livewire): array {
                                return $livewire->ownerRecord
                                    ->cliente()
                                    ->pluck('nome', 'id')
                                    ->toArray();
                            })
                            ->required(),
                        Forms\Components\TextInput::make('ordem_parcela')
                            ->label('Parcela Nº')
                            ->readOnly()
                            ->default('1')
                            ->required(),

                        Forms\Components\TextInput::make('parcelas')
                            ->default('1')
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                if ($get('parcelas') != 1) {
                                    $set('valor_parcela', (($get('valor_total') / $get('parcelas'))));
                                    $set('status', 0);
                                    $set('valor_recebido', 0);
                                    $set('data_pagamento', null);
                                    $set('data_vencimento',  Carbon::now()->addDays(30)->format('Y-m-d'));
                                } else {
                                    $set('valor_parcela', $get('valor_total'));
                                    $set('status', 1);
                                    $set('valor_recebido', $get('valor_total'));
                                    $set('data_pagamento', Carbon::now()->format('Y-m-d'));
                                    $set('data_vencimento',  Carbon::now()->format('Y-m-d'));
                                }
                            })
                            ->required(),
                        Forms\Components\DatePicker::make('data_pagamento')
                            ->default(now())
                            ->displayFormat('d/m/Y')
                            ->label("Data do Pagamento"),

                        Forms\Components\DatePicker::make('data_vencimento')
                            ->default(now())
                            ->label("Data do Vencimento")
                            ->displayFormat('d/m/Y')
                            ->required(),
                        Forms\Components\TextInput::make('valor_total')
                            ->numeric()
                            ->label('Valor Total')
                            ->default(function ($livewire): float {
                                return !empty($livewire->ownerRecord->tipo_acres_desc)
                                    ? $livewire->ownerRecord->valor_total_desconto
                                    : $livewire->ownerRecord->valor_total;
                            })
                            ->readOnly()
                            ->required(),


                        Forms\Components\TextInput::make('valor_parcela')
                            ->numeric()
                            ->label('Valor da Parcela')
                            ->default(function ($livewire): float {
                                return !empty($livewire->ownerRecord->tipo_acres_desc)
                                    ? $livewire->ownerRecord->valor_total_desconto
                                    : $livewire->ownerRecord->valor_total;
                            })
                            ->required()
                            ->readOnly(),
                        Forms\Components\TextInput::make('valor_recebido')
                            ->numeric()
                            ->default(function ($livewire): float {
                                return !empty($livewire->ownerRecord->tipo_acres_desc)
                                    ? $livewire->ownerRecord->valor_total_desconto
                                    : $livewire->ownerRecord->valor_total;
                            }),
                        Forms\Components\Textarea::make('obs')
                            ->columnSpan([
                                'xl' => 3,
                                '2xl' => 3,
                            ])
                            ->label('Observações'),
                        Forms\Components\Toggle::make('status')
                            ->default('true')
                            ->label('Recebido')
                            ->required()
                            ->live(debounce: 500)
                            // ->hidden(fn (Get $get): bool => $get('parcelas') != '1')
                            ->afterStateUpdated(
                                function (Get $get, Set $set) {
                                    if ($get('status') == 1) {
                                        $set('valor_recebido', $get('valor_parcela'));
                                        $set('data_pagamento', Carbon::now()->format('Y-m-d'));
                                    } else {

                                        $set('valor_recebido', 0);
                                        $set('data_pagamento', null);
                                    }
                                }
                            ),
                    ])


            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('venda_id')
            ->columns([
                Tables\Columns\TextColumn::make('cliente.nome')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('ordem_parcela')
                    ->label('Parcela Nº'),
                Tables\Columns\TextColumn::make('valor_total')
                    ->label('Data do Vencimento')
                    ->badge()
                    ->color('warning')
                    ->label('Valor Total')
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('data_vencimento')
                    ->label('Data do Vencimento')
                    ->badge()
                    ->color('danger')
                    ->sortable()
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('valor_parcela')
                    ->summarize(Sum::make()->money('BRL')->label('Total Parcelas'))
                    ->badge()
                    ->color('danger')
                    ->label('Valor da Parcela')
                    ->money('BRL'),
                Tables\Columns\IconColumn::make('status')
                    ->alignCenter()
                    ->label('Recebido')
                    ->boolean(),
                Tables\Columns\TextColumn::make('data_pagamento')
                    ->label('Data do Recebimento')
                    ->badge()
                    ->color('success')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('valor_recebido')
                    ->summarize(Sum::make()->money('BRL')->label('Total Pago'))
                    ->label('Recebido')
                    ->badge()
                    ->color('success')
                    ->label('Valor Pago'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Lançar Recebimento')
                    ->after(
                        function ($data, $record, $livewire) {
                            if ($record->parcelas > 1) {
                                $valor_parcela = (!empty($data['valor_total_desconto']) ? $data['valor_total_desconto'] : $record->valor_total) / $record->parcelas;
                                $vencimentos = Carbon::create($record->data_vencimento);
                                for ($cont = 1; $cont < $data['parcelas']; $cont++) {
                                    $dataVencimentos = $vencimentos->addDays(30);
                                    $parcelas = [
                                        'venda_id' => $record->venda_id,
                                        'cliente_id' => $data['cliente_id'],
                                        'valor_total' => !empty($data['valor_total_desconto']) ? $data['valor_total_desconto'] : $data['valor_total'],
                                        'parcelas' => $data['parcelas'],
                                        'ordem_parcela' => $cont + 1,
                                        'data_vencimento' => $dataVencimentos,
                                        'valor_recebido' => 0.00,
                                        'status' => 0,
                                        'obs' => $data['obs'],
                                        'valor_parcela' => $valor_parcela,
                                    ];
                                    ContasReceber::create($parcelas);
                                }
                            } else {
                                $addFluxoCaixa = [
                                    'valor' => !empty($data['valor_total_desconto']) ? $data['valor_total_desconto'] : $record->valor_total,
                                    'tipo'  => 'CREDITO',
                                    'obs'   => 'Recebido da venda nº: ' . $record->venda_id . '',
                                ];

                                FluxoCaixa::create($addFluxoCaixa);
                            }

                            $venda = Venda::find($livewire->ownerRecord->id);
                            $venda->status_caixa = 1;
                            $venda->save();
                        }


                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(fn($record) => $record->status == 1)
                    ->after(function ($data, $record) {

                        if ($record->status = 1) {
                            $addFluxoCaixa = [
                                'valor' => ($record->valor_parcela),
                                'tipo'  => 'CREDITO',
                                'obs'   => 'Recebido da venda nº: ' . $record->venda_id . '',
                            ];

                            FluxoCaixa::create($addFluxoCaixa);
                        }
                    }),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn($record) => $record->status == 1),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
