<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FornecedorResource\Pages;
use App\Models\Estado;
use App\Models\Fornecedor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Forms\Components\Grid;
use Filament\Support\RawJs;
use Filament\Notifications\Notification;

class FornecedorResource extends Resource
{
    protected static ?string $model = Fornecedor::class;

    protected static ?string $navigationIcon = 'heroicon-s-user-group';

    protected static ?string $navigationLabel = 'Fornecedores';

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'xl'  => 4,
                    '2xl' => 4,
                ])
                    ->schema([
                        Forms\Components\TextInput::make('nome')
                        ->columnSpan([
                            'xl'  => 2,
                            '2xl' => 2,
                        ])
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('cpf_cnpj')
                            ->label('CPF/CNPJ')
                            ->mask(RawJs::make(<<<'JS'
                                    $input.length > 14 ? '99.999.999/9999-99' : '999.999.999-99'
                                JS))
                            ->rule('cpf_ou_cnpj')
                            ->label('CPF/CNPJ'),
                        Forms\Components\TextInput::make('telefone')
                            ->minLength(11)
                            ->maxLength(11)
                            ->mask('(99)99999-9999')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('endereco')
                            ->columnSpan([
                                'xl'  => 2,
                                '2xl' => 2,
                            ])
                            ->label('Endereço'),
                        Forms\Components\Select::make('estado_id')
                            ->label('Estado')
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->options(Estado::all()->pluck('nome', 'id')->toArray())
                            ->reactive(),
                        Forms\Components\Select::make('cidade_id')
                            ->label('Cidade')
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->options(function (callable $get) {
                                $estado = Estado::find($get('estado_id'));
                                if (!$estado) {
                                    return Estado::all()->pluck('nome', 'id');
                                }

                                return $estado->cidade->pluck('nome', 'id');
                            })
                            ->reactive(),

                        Forms\Components\TextInput::make('email')
                            ->columnSpan([
                                'xl'  => 2,
                                '2xl' => 2,
                            ])
                            ->email()
                            ->maxLength(255),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('endereco')
                    ->label('Endereço'),
                Tables\Columns\TextColumn::make('estado.nome')
                    ->label('Estado'),
                Tables\Columns\TextColumn::make('cidade.nome')
                    ->label('Cidade'),
                Tables\Columns\TextColumn::make('telefone')
                    ->formatStateUsing(fn (string $state) => vsprintf('(%d%d)%d%d%d%d%d-%d%d%d%d', str_split($state)))
                    ->label('Telefone'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email'),
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
                Tables\Actions\EditAction::make()
                    ->modalHeading('Fornecedores'),
               Tables\Actions\DeleteAction::make()
                    ->before(function (\Filament\Tables\Actions\DeleteAction $action, Fornecedor $record) {
                        if ($record->compra()->exists() || $record->contasPagar()->exists()) {
                            Notification::make()
                                ->title('Ação cancelada')
                                ->body('Este fornecedor não pode ser excluído porque está vinculado a uma ou mais compras.')
                                ->danger()
                                ->send();
                            $action->cancel();

                        }
                    }),
            ])
            ->bulkActions([
               // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageFornecedors::route('/'),
        ];
    }
}
