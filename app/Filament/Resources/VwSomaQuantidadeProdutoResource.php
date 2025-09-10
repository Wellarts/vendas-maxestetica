<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VwSomaQuantidadeProdutoResource\Pages;
use App\Models\VwSomaQuantidadeProduto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VwSomaQuantidadeProdutoResource extends Resource
{
    protected static ?string $model = VwSomaQuantidadeProduto::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Rentabilidade';

    protected static ?string $navigationGroup = 'Consultas';

    protected static ?int $navigationSort = 19;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('total_vendido_qtd')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('total_vendido_valor')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('total_vendido_custo')
                    ->required()
                    ->numeric()
                    ->default(0.00),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('pontuacao_rentabilidade', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('tipo')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'success',
                        '2' => 'warning',
                    })
                    ->formatStateUsing(function ($state) {
                        if ($state == 1) {
                            return 'Produto';
                        }
                        if ($state == 2) {
                            return 'Serviço';
                        }
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('nome')
                    ->label('Produto/Serviço')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_vendido_qtd')
                    ->label('Qtd')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_vendido_custo')
                    ->label('Total Compra')
                    ->money('BRL')
                    ->badge()
                    ->color('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_vendido_valor')
                    ->label('Total Vendido')
                    ->badge()
                    ->color('warning')
                    ->money('BRL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_vendido_lucro')
                    // ->getStateUsing(function(VwSomaQuantidadeProduto $record):float {
                    //    // dd($record->total_vendido_compra);
                    //     return ($record->total_vendido_valor - $record->total_vendido_custo);
                    // })
                    ->badge()
                    ->color('success')
                    ->label('Total Lucro')
                    ->money('BRL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pontuacao_rentabilidade')
                    // ->getStateUsing(function(VwSomaQuantidadeProduto $record,):float {
                    //    // dd($record->total_vendido_compra);
                    //     return ($record->total_vendido_qtd * ($record->total_vendido_valor - $record->total_vendido_custo));
                    // })
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' pontos')
                    ->color('info')
                    ->badge()
                    ->label('Total Lucro')
                    ->label('Rentabilidade')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('Produto')
                    ->query(fn (Builder $query): Builder => $query->where('tipo', 1)),
                Filter::make('Serviço')
                    ->query(fn (Builder $query): Builder => $query->where('tipo', 2)),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageVwSomaQuantidadeProdutos::route('/'),
        ];
    }
}
