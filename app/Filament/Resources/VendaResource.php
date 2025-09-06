<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendaResource\Pages;
use App\Filament\Resources\VendaResource\RelationManagers\ContasReceberRelationManager;
use App\Filament\Resources\VendaResource\RelationManagers\ItensVendaRelationManager;
use App\Models\FormaPgmto;
use App\Models\Funcionario;
use App\Models\Venda;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Filament\Forms\Components\Actions\Action;
use Filament\Support\Enums\Alignment;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Grid;
use Filament\Support\RawJs;
use App\Models\Estado;

class VendaResource extends Resource
{
    protected static ?string $model = Venda::class;


    protected static ?string $navigationIcon = 'heroicon-s-shopping-cart';

    protected static ?string $navigationGroup = 'Saídas';

    protected static ?int $navigationSort = 1;

        protected static bool $shouldRegisterNavigation = false; // Definido como false para ocultar do menu

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columns([
                        'xl'  => 2,
                        '2xl' => 2,
                    ])
                    ->schema([
                        Forms\Components\Select::make('cliente_id')
                            ->label('Cliente')
                            ->default(1)
                            ->native(false)
                            ->searchable()
                            ->relationship('cliente', 'nome')
                            ->required()
                            ->createOptionForm([
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
                                        ->required()
                                        ->options(Estado::all()->pluck('nome', 'id')->toArray())
                                        ->reactive(),
                                    Forms\Components\Select::make('cidade_id')
                                        ->label('Cidade')
                                        ->native(false)
                                        ->searchable()
                                        ->required()
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
                                    Forms\Components\TextInput::make('numero_conselho')
                                        ->placeholder('Ex: CRM-12345')
                                        ->label('Número do Conselho'),
                                ]),
                            ]),
            Forms\Components\Select::make('funcionario_id')
                ->default(1)
                ->label('Vendedor(a)')
                ->native(false)
                            ->searchable()
                            ->options(Funcionario::all()->pluck('nome', 'id')->toArray())
                            ->required(),
                        Forms\Components\Select::make('forma_pgmto_id')
                            ->default(1)
                            ->label('Forma de Pagamento')
                            ->native(false)
                            ->searchable()
                            ->options(FormaPgmto::all()->pluck('nome', 'id')->toArray())
                            ->required(),
                        Forms\Components\DatePicker::make('data_venda')
                            ->label('Data da Venda')
                            ->default(now())
                            ->required(),
                        Section::make('Descontos e Acréscimos')
                            ->visible(fn ($context) => $context == 'edit')
                            ->columns([
                                'xl'  => 2,
                                '2xl' => 2,
                            ])
                            ->schema([
                                Forms\Components\Select::make('tipo_acres_desc')
                                    ->label('Tipo de Desconto/Acréscimo')
                                    ->hint('Porcentagem ou Valor')
                                    ->live()
                                    ->options([
                                        'Valor'       => 'Valor',
                                        'Porcentagem' => 'Porcentagem',
                                    ])
                                    ->required(false)
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('percent_acres_desc', null);
                                        $set('valor_acres_desc', null);
                                        $set('valor_total_desconto', null);
                                    }),
                                Forms\Components\TextInput::make('percent_acres_desc')
                                    ->label('Percentual')
                                    ->visible(fn (callable $get) => $get('tipo_acres_desc') === 'Porcentagem')
                                    ->numeric()
                                    ->hint('Para desconto use um valor negativo Ex. -10')
                                    ->extraInputAttributes(['style' => 'font-weight: bolder; font-size: 1.3rem; color: #a39b07ff;'])
                                    ->suffix('%')
                                    ->required(false)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $valorTotal     = (float) $get('valor_total');
                                        $percentual     = (float) $state;
                                        $tipo           = $get('tipo_acres_desc');
                                        $valorAcresDesc = (float) $get('valor_acres_desc');
                                        $novoValor      = $valorTotal;
                                        if ($tipo === 'Porcentagem' && $percentual != 0) {
                                            $novoValor = $valorTotal + ($valorTotal * ($percentual / 100));
                                        } elseif ($tipo === 'Valor' && $valorAcresDesc != 0) {
                                            $novoValor = $valorTotal + $valorAcresDesc;
                                        }
                                        $set('valor_total_desconto', $novoValor);
                                    }),
                                Forms\Components\TextInput::make('valor_acres_desc')
                                    ->label('Valor Desconto/Acréscimo')
                                    ->hint('Para desconto use um valor negativo Ex. -10')
                                    // ->visible(fn (callable $get) => $get('tipo_acres_desc') === 'Valor')
                                    ->hidden(fn (callable $get) => $get('tipo_acres_desc') !== 'Valor')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->extraInputAttributes(['style' => 'font-weight: bolder; font-size: 1.3rem; color: #a39b07ff;'])
                                    ->required(false)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $valorTotal     = (float) $get('valor_total');
                                        $tipo           = $get('tipo_acres_desc');
                                        $percentual     = (float) $get('percent_acres_desc');
                                        $valorAcresDesc = (float) $state;
                                        $novoValor      = $valorTotal;
                                        if ($tipo === 'Porcentagem' && $percentual > 0) {
                                            $novoValor = $valorTotal + ($valorTotal * ($percentual / 100));
                                        } elseif ($tipo === 'Valor' && $valorAcresDesc != 0) {
                                            $novoValor = $valorTotal + $valorAcresDesc;
                                        }
                                        $set('valor_total_desconto', $novoValor);
                                    }),
                            ]),
                        Section::make('Valores Totais')
                            ->visible(fn ($context) => $context == 'edit')
                            ->columns([
                                'xl'  => 2,
                                '2xl' => 2,
                            ])
                            ->schema([
                                Forms\Components\TextInput::make('valor_total')
                                    ->label('Valor Total')
                                    ->numeric()
                                    ->minValue(0)
                                    ->extraInputAttributes(['style' => 'font-weight: bolder; font-size: 1.3rem; color: #ff1e29ff;'])
                                    ->prefix('R$')
                                    ->readOnly()
                                    ->required(),
                                Forms\Components\TextInput::make('valor_total_desconto')
                                    ->label('Valor Total com Desconto/Acréscimo')
                                    ->visible(fn (callable $get) => $get('tipo_acres_desc') != '')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->minValue(0)
                                    ->readOnly()
                                    ->required(false)
                                    ->extraInputAttributes(['style' => 'font-weight: bolder; font-size: 1.3rem; color: #1E90FF;']),

                            ])
                            ->footerActions([
                                Action::make('recarregar_valores')
                                    ->label('Recarregar Valores')
                                    // ->icon('heroicon-o-refresh')
                                        ->action(function ($livewire) {
                                            $livewire->form->fill([
                                                'cliente_id'           => $livewire->record->cliente_id,
                                                'data_venda'           => $livewire->record->data_venda,
                                                'funcionario_id'       => $livewire->record->funcionario_id,
                                                'forma_pgmto_id'       => $livewire->record->forma_pgmto_id,
                                                'valor_total'          => $livewire->record->valor_total,
                                                'valor_total_desconto' => $livewire->record->valor_total_desconto,
                                            ]);
                                            Notification::make()
                                                ->title('Valores recarregados')
                                                ->success()
                                                ->duration(3000)
                                                ->send();
                                        }),
                            ])
                            ->footerActionsAlignment(Alignment::End),
                        Forms\Components\Textarea::make('obs')
                            ->columnSpanFull()
                            ->label('Observações'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cliente.nome')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('data_venda')
                    ->searchable()
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('funcionario.nome')
                    ->label('Vendedor'),
                Tables\Columns\TextColumn::make('valor_total')
                    ->summarize(Sum::make()->money('BRL')->label('Total'))
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('valor_total_desconto')
                    ->label('Valor Total com Desconto')
                    ->alignCenter()
                    ->money('BRL'),
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
                Tables\Actions\Action::make('Imprimir')
                ->url(fn (Venda $record): string => route('comprovanteNormal', $record))
                ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                  //  Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ItensVendaRelationManager::class,
            ContasReceberRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVendas::route('/'),
            'create' => Pages\CreateVenda::route('/create'),
            'edit'   => Pages\EditVenda::route('/{record}/edit'),
        ];
    }



}
