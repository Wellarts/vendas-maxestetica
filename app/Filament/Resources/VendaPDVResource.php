<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendaPDVResource\Pages;
use App\Filament\Resources\VendaPDVResource\RelationManagers\PDVRelationManager;
use App\Models\Venda;
use App\Models\VendaPDV;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VendaPDVResource extends Resource
{
    protected static ?string $model = VendaPDV::class;

    protected static ?string $navigationIcon = 'heroicon-s-shopping-cart';

    protected static ?string $navigationGroup = 'Ponto de Venda';

    protected static ?string $navigationLabel = 'Histórico de Vendas (PDV)';

    protected static ?string $title = 'Vendas PDV';

    protected static ?int $navigationSort = 3;



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Dados da Venda')
                    ->columns([
                        'xl'  => 3,
                        '2xl' => 3,
                    ])
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->label('ID')
                            ->disabled(),
                        Forms\Components\Radio::make('tipo_registro')
                            ->label('Tipo de Registro')
                            ->options([
                                'venda'     => 'Venda',
                                'orcamento' => 'Orçamento',
                            ])
                            ->default('venda')
                            ->disabled()
                            ->required(),
                        Forms\Components\Select::make('cliente_id')
                            ->label('Cliente')
                            ->native(false)
                            ->searchable()
                            ->relationship('cliente', 'nome')
                            ->required(),
                        Forms\Components\Select::make('funcionario_id')
                            ->label('Vendedor')
                            ->native(false)
                            ->searchable()
                            ->relationship('funcionario', 'nome')
                            ->required(),
                        Forms\Components\DatePicker::make('data_venda')
                           ->required(),
                        Forms\Components\Select::make('forma_pgmto_id')
                            ->label('Forma de Pagamento')
                            ->native(false)
                            ->searchable()
                            ->relationship('formaPgmto', 'nome')
                            ->required(),

                        Section::make('Descontos e Acréscimos')
                            ->columns([
                                'xl'  => 4,
                                '2xl' => 4,
                            ])
                            ->schema([
                                Forms\Components\Radio::make('tipo_acres_desc')
                                    ->label('Forma')
                                    ->hint('Porcentagem ou Valor')
                                    ->live()
                                    ->options([
                                        'Valor'       => 'Valor',
                                        'Porcentagem' => 'Porcentagem',
                                    ])
                                    ->required(false)
                                    ->afterStateUpdated(function ($state, callable $set, \Filament\Forms\Get $get) {
                                        $set('percent_acres_desc', null);
                                        $set('valor_acres_desc', null);
                                        $set('valor_total_desconto', $get('valor_total'));
                                    }),
                                Forms\Components\TextInput::make('percent_acres_desc')
                                    ->label('Percentual')
                                    ->visible(fn (callable $get) => $get('tipo_acres_desc') === 'Porcentagem')
                                    ->numeric()
                                    ->hint('Para desconto Ex. -10')
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
                                    ->label('Valor Desc/Acres')
                                    ->hint('Para desconto Ex. -10')
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
                                Forms\Components\TextInput::make('valor_total')
                                    ->label('Valor Total')
                                    ->extraInputAttributes(['style' => 'font-weight: bolder; font-size: 1.3rem; color: #9f0d03ff; text-align: right;'])
                                    ->numeric(),
                                
                                Forms\Components\TextInput::make('valor_total_desconto')
                                    ->numeric()
                                    ->label('Valor Total c/ Desconto/Acréscimo')
                                    ->readOnly()
                                    ->extraInputAttributes(['style' => 'font-weight: bolder; font-size: 1.3rem; color: #32CD32; text-align: right;']),
                                    
                            ]),
                       
                         Forms\Components\Radio::make('financeiro')
                                ->label('Lançamento Financeiro')
                               // ->visible(fn (callable $get) => $get('tipo_registro') === 'venda')
                                ->live()
                                ->options([
                                    '1' => 'Direto no Caixa',
                                    '2' => 'Conta a Receber',
                                ])->default('1'),
                         Forms\Components\TextInput::make('parcelas')
                                ->numeric()
                                ->required()
                                ->label('Qtd de Parcelas')
                                ->visible(fn (\Filament\Forms\Get $get): bool => $get('financeiro') == 2),
                               
                         

                        Forms\Components\Textarea::make('obs')
                            ->columnSpanFull()
                            ->label('Observações'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('tipo_registro')
                    ->label('Tipo')
                    ->badge()
                    ->colors([
                        'success' => 'venda',
                        'warning' => 'orcamento',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'venda'     => 'Venda',
                        'orcamento' => 'Orçamento',
                    })
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('id')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cliente.nome')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('data_venda')
                    ->label('Data da Venda')
                    ->searchable()
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('valor_total')
                    ->label('Valor Total')
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('valor_total_desconto')
                    ->label('Valor Total c/ Desconto')
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
                SelectFilter::make('tipo_registro')
                    ->label('Tipo de Registro')
                    ->options([
                        'orcamento' => 'Orçamento',
                        'venda'     => 'Venda',
                    ]),
                SelectFilter::make('cliente_id')
                    ->label('Cliente')
                    ->relationship('cliente', 'nome')
                    ->multiple()
                    ->searchable(),
                Tables\Filters\Filter::make('data_vencimento')
                    ->form([
                        Forms\Components\DatePicker::make('data_de')
                            ->label('Data de:'),
                        Forms\Components\DatePicker::make('data_ate')
                            ->label('Data até:'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['data_de'],
                                fn ($query) => $query->whereDate('data_venda', '>=', $data['data_de'])
                            )
                            ->when(
                                $data['data_ate'],
                                fn ($query) => $query->whereDate('data_venda', '<=', $data['data_ate'])
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading('Vendas PDV'),
                Tables\Actions\Action::make('Imprimir')
                    ->icon('heroicon-s-printer')
                    ->label('Comprovante de Venda')
                    ->url(fn (VendaPDV $record): string => route('comprovantePDV', $record))
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
            PDVRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVendaPDVS::route('/'),
            'create' => Pages\CreateVendaPDV::route('/create'),
            'edit'   => Pages\EditVendaPDV::route('/{record}/edit'),
        ];
    }
}
