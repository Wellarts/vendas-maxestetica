<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormaPgmtoResource\Pages;
use App\Models\FormaPgmto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class FormaPgmtoResource extends Resource
{
    protected static ?string $model = FormaPgmto::class;

    protected static ?string $navigationIcon = 'heroicon-s-credit-card';

    protected static ?string $navigationLabel = 'Formas de Pagamento';

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?int $navigationSort = 12;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Editar forma de pagamento'),
                Tables\Actions\DeleteAction::make()
                    ->before(function (\Filament\Tables\Actions\DeleteAction $action, FormaPgmto $record) {
                        if ($record->venda()->exists() || $record->vendasPdv()->exists()) {
                            Notification::make()
                                ->title('Ação cancelada')
                                ->body('Esta forma de pagamento não pode ser excluído porque está vinculado a uma ou mais vendas.')
                                ->danger()
                                ->send();
                            $action->cancel();

                        }
                    }),
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
            'index' => Pages\ManageFormaPgmtos::route('/'),
        ];
    }
}
