<?php

namespace App\Filament\Resources\ProdutoResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProdutoFornecedorRelationManager extends RelationManager
{
    protected static string $relationship = 'ProdutoFornecedor';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('produto_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('produto_id')
            ->columns([
                Tables\Columns\TextColumn::make('compra_id')
                    ->label('Compra'),
                Tables\Columns\TextColumn::make('compra.fornecedor.nome'),
                Tables\Columns\TextColumn::make('compra.data_compra')
                    ->label('Data')
                    ->date('d/m/y'),
                Tables\Columns\TextColumn::make('qtd'),
                Tables\Columns\TextColumn::make('valor')
                    ->money('BRL'),


            ])
            ->filters([
                //
            ])
            ->headerActions([
                //   Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                //   Tables\Actions\EditAction::make(),
                  Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //      Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
