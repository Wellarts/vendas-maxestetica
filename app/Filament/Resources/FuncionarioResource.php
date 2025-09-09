<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FuncionarioResource\Pages;
use App\Models\Estado;
use App\Models\Funcionario;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FuncionarioResource extends Resource
{
    protected static ?string $model = Funcionario::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?string $navigationLabel = 'Funcionários';

    protected static ?int $navigationSort = 11;


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
                        Forms\Components\TextInput::make('cpf')
                            ->label('CPF')
                            ->mask('999.999.999-99')
                            ->rule('cpf_ou_cnpj'),
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
                            ->native(false)
                            ->searchable()
                            ->required(false)
                            ->options(Estado::all()->pluck('nome', 'id')->toArray())
                            ->reactive(),
                        Forms\Components\Select::make('cidade_id')
                            ->label('Cidade')
                            ->required(false)
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
                ->dateTime()
                ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            'index' => Pages\ManageFuncionarios::route('/'),
        ];
    }
}
