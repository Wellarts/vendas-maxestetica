<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdutoResource\Pages;
use App\Filament\Resources\ProdutoResource\RelationManagers\ProdutoFornecedorRelationManager;
use App\Models\Produto;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Notifications\Notification;

class ProdutoResource extends Resource
{
    protected static ?string $model = Produto::class;

    protected static ?string $navigationIcon = 'heroicon-s-shopping-bag';

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?string $label = 'Produtos';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Cadastro')
                    ->columns([
                        'xl'  => 3,
                        '2xl' => 3,
                    ])
                    ->schema([
                        Forms\Components\Hidden::make('tipo')
                            ->default(1),
                        Forms\Components\TextInput::make('nome')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('codbar')
                            ->label('Código de Barras')
                            ->hidden(function (Get $get) {
                                if ($get('tipo') == 1) {
                                    return false;
                                } elseif ($get('tipo') == 2) {
                                    return true;
                                }
                            })
                            ->required(false),
                        Forms\Components\TextInput::make('estoque')
                            ->numeric()
                            ->integer()
                            ->hidden(function (Get $get) {
                                if ($get('tipo') == 1) {
                                    return false;
                                } elseif ($get('tipo') == 2) {
                                    return true;
                                }
                            }),
                        Forms\Components\TextInput::make('valor_compra')
                            ->label('Valor Compra')
                            ->hidden(function (Get $get) {
                                if ($get('tipo') == 1) {
                                    return false;
                                } elseif ($get('tipo') == 2) {
                                    return true;
                                }
                            })
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                if ($get('tipo') == 1 && (float)$get('lucratividade') > 0) {
                                    $set('valor_venda', ((((float)$get('valor_compra') * (float)$get('lucratividade')) / 100) + (float)$get('valor_compra')));
                                }
                            }),                               
                        Forms\Components\TextInput::make('lucratividade')
                            ->label('Lucratividade (%)')
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                if ($get('tipo') == 1 && (float)$get('valor_compra') > 0) {
                                     $set('valor_venda', ((((float)$get('valor_compra') * (float)$get('lucratividade')) / 100) + (float)$get('valor_compra')));
                                }                               
                            }),
                        Forms\Components\TextInput::make('valor_venda')
                            ->label('Valor Venda')
                            ->numeric()
                            // ->disabled(),
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                if ($get('tipo') == 1 && (float)$get('valor_compra') > 0) {
                                    $set('lucratividade', (((((float)$get('valor_venda') - (float)$get('valor_compra')) / (float)$get('valor_compra')) * 100)));
                                }
                            }),
                        FileUpload::make('foto')
                            ->label('Fotos')
                            ->downloadable()
                            ->directory('fotos-produtos')
                            ->maxSize(1024)
                            ->maxFiles(1)
                            ->hidden(function (Get $get) {
                                if ($get('tipo') == 1) {
                                    return false;
                                } elseif ($get('tipo') == 2) {
                                    return true;
                                }
                            }),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('codbar')
                    ->label('Cód. Barras')
                    ->alignCenter()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('estoque')
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('valor_compra')
                    ->label('Valor Compra')
                    ->alignCenter()
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('lucratividade')
                    ->label('Lucratividade (%)')
                    ->alignCenter()
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('valor_venda')
                    ->label('Valor Venda')
                    ->alignCenter()
                    ->money('BRL'),
                ImageColumn::make('foto')
                    ->label('Fotos')
                    ->alignCenter()
                    ->circular()
                    ->stacked()
                    ->limit(2)
                    ->limitedRemainingText(),
                Tables\Columns\TextColumn::make('created_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (\Filament\Tables\Actions\DeleteAction $action, Produto $record) {
                        if ($record->itensVenda()->exists() || $record->pdv()->exists()) {

                            Notification::make()
                                ->title('Ação cancelada')
                                ->body('Este produto não pode ser excluído porque está vinculado a uma ou mais vendas.')
                                ->danger()
                                ->send();
                            $action->cancel();

                        }
                    }),
            ])
            ->bulkActions([
              //  Tables\Actions\DeleteBulkAction::make(),
                ExportBulkAction::make(),



            ]);
    }

    public static function getRelations(): array
    {
        return [
            ProdutoFornecedorRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProdutos::route('/'),
            'create' => Pages\CreateProduto::route('/create'),
            'edit'   => Pages\EditProduto::route('/{record}/edit'),

        ];
    }
}
