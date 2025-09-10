<?php

namespace App\Filament\Resources\VendaPDVResource\RelationManagers;

use App\Models\Produto;
use App\Models\VendaPDV;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;

class PDVRelationManager extends RelationManager
{
    protected static string $relationship = 'PDV';

    protected static ?string $title = 'Itens da Venda PDV';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
               Forms\Components\TextInput::make('produto_id')
                    ->required(),
                Forms\Components\TextInput::make('qtd')
                    ->required(),
                Forms\Components\TextInput::make('sub_total')
                    ->numeric()
                    ->required(),


            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('vendapdv_id')
            ->columns([
                Tables\Columns\TextColumn::make('venda_p_d_v_id')
                    ->label('Venda PDV'),
                Tables\Columns\TextColumn::make('produto.nome'),
                Tables\Columns\TextColumn::make('produto.codbar')
                    ->label('Código do Produto'),
                Tables\Columns\TextColumn::make('qtd')
                    ->summarize(Sum::make()->label('Qtd de Produtos')),
                Tables\Columns\TextColumn::make('valor_venda')
                    ->label('Valor Unit.'),
                Tables\Columns\TextColumn::make('sub_total')
                    ->summarize(Sum::make()->money('BRL')->label('Total'))
                    ->label('Sub-Total'),
            ])
            ->filters([
                Tables\Filters\Filter::make('produto_nome')
                    ->form([
                        Forms\Components\TextInput::make('produto_nome')->label('Nome do Produto'),
                    ])
                    ->query(function ($query, $data) {
                        if ($data['produto_nome']) {
                            $query->whereHas('produto', function ($q) use ($data) {
                                $q->where('nome', 'like', '%' . $data['produto_nome'] . '%');
                            });
                        }
                    }),
                Tables\Filters\Filter::make('data_venda')
                    ->form([
                        Forms\Components\DatePicker::make('data_venda')->label('Data da Venda'),
                    ])
                    ->query(function ($query, $data) {
                        if ($data['data_venda']) {
                            $query->whereDate('created_at', $data['data_venda']);
                        }
                    }),
            ])
            ->headerActions([
                // Nenhuma ação personalizada de relatório aqui
            ])
            ->actions([
              //  Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                ->before(function ($data, $record) {
                    $produto = Produto::find($record->produto_id);
                    $venda   = VendaPDV::find($record->venda_p_d_v_id);
                    $venda->valor_total -= $record->sub_total;
                    $produto->estoque += ($record->qtd);
                    $venda->save();
                    $produto->save();
                })
                ->after(function () {
                    return redirect(request()->header('Referer'));
                }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                $produto = Produto::find($record->produto_id);
                                $venda   = VendaPDV::find($record->venda_p_d_v_id);
                                $venda->valor_total -= $record->sub_total;
                                $produto->estoque += ($record->qtd);
                                $venda->save();
                                $produto->save();
                            }

                        })
                        ->after(function () {
                            return redirect(request()->header('Referer'));
                        }),

                ]),
            ]);
    }
}
