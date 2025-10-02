<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FluxoCaixaResource\Pages;
use App\Models\FluxoCaixa;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;

class FluxoCaixaResource extends Resource
{
    protected static ?string $model = FluxoCaixa::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Fluxo de Caixa';

    protected static ?string $navigationGroup = 'Financeiro';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make('4')
                    ->schema([
                        Forms\Components\Select::make('tipo')
                            ->options([
                                'CREDITO' => 'CRÉDITO',
                                'DEBITO'  => 'DÉBITO',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('valor')
                            ->numeric()
                            ->prefix('R$')
                          //  ->hint('Use (-) no Débito')
                            ->required(),

                        Forms\Components\Textarea::make('obs')
                            ->label('Descrição')
                            ->columnSpan([
                                'xl'  => 2,
                                '2xl' => 2,
                            ])
                            ->required(),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Data/Hora')
                            ->default(now())
                            ->required(),
                        
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('tipo')
                    ->badge()
                    ->color(static function ($state): string {
                        if ($state === 'CREDITO') {
                            return 'success';
                        }

                        return 'danger';
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('valor')
                    ->summarize(Sum::make()->money('BRL')->label('Total'))
                    ->alignCenter()
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('obs')
                    ->label('Descrição')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i:s')
                    ->label('Criado')
                    ->sortable(),
                  //  ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i:s')
                    ->label('Atualizado')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Data Inicial')
                            ->default(now()->toDateString()),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Data Final')
                            ->default(now()->toDateString()),
                    ])
                    ->query(function ($query, $data) {
                        if ($data['created_from']) {
                            $query->whereDate('created_at', '>=', $data['created_from']);
                        }
                        if ($data['created_until']) {
                            $query->whereDate('created_at', '<=', $data['created_until']);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageFluxoCaixas::route('/'),
        ];
    }
}
