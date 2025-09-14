<?php

namespace App\Filament\Pages;

use App\Models\Cliente;
use App\Models\ContasReceber;
use App\Models\Estado;
use App\Models\FluxoCaixa;
use App\Models\FormaPgmto;
use App\Models\Funcionario;
use App\Models\Produto;
use App\Models\PDV as PDVs;
use App\Models\VendaPDV;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Columns\TextInputColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\RawJs;
use Filament\Tables\Actions\DeleteAction;

class PDV extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-s-shopping-cart';

    protected static ?string $model = Produto::class;

    protected static string $view = 'filament.pages.p-d-v';

    protected static ?string $title = 'PDV';

    protected static ?string $navigationLabel = 'Efetuar Venda (PDV)';

    protected static ?string $navigationGroup = 'Ponto de Venda';

    protected static ?int $navigationSort = 2;

    public ?array $data = [];

    public $produto_id;
    public $produto_nome;
    public $qtd;
    public $pdv;
    public $venda;

    // public static function shouldRegisterNavigation(): bool
    // {
    //     /** @var \App\Models\User */
    //     $authUser =  auth()->user();

    //     if ($authUser->hasRole('TI')) {
    //         return true;
    //     } else {
    //         return false;
    //     }
    // }


    public function mount(): void
    {
        $this->form->fill();
        $this->venda = random_int(0000000000, 9999999999);
    }

    public function form(Form $form): Form
    {
        return $form
            ->model(Produto::class)
            ->schema([
                Section::make('Ponto de Venda')
                    ->columns(4)
                    ->schema([
                        //     TextInput::make('produto_id')
                        //         ->numeric()
                        //         ->label('Produto')
                        //         ->autocomplete()
                        //         ->autofocus()
                        //         ->extraInputAttributes(['tabindex' => 1])
                        //         ->live(debounce: 900)
                        //         ->afterStateUpdated(function ($state, Get $get, Set $set) {
                        //           //  dd($get('produto_id'));
                        //           //  $produto = Produto::where('codbar','=', $state)->first();

                        //           //  $set('produto_nome', $produto->nome);
                        //             $this->updated($state, $state);
                        //         }),
                        //  //   TextInput::make('produto_nome')
                        Select::make('produto_id')
                            ->columnSpan(2)
                            ->label('Produto')
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) {
                                return Produto::query()
                                    ->where('codbar', 'like', "%{$search}%")
                                    ->orWhere('nome', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(function (Produto $product) {
                                        // Exibe código de barras e nome no select
                                        return [$product->id => "[{$product->codbar}] {$product->nome}"];
                                    });
                            })
                            ->getOptionLabelUsing(fn ($value): ?string => Produto::where('id', $value)->first()?->nome)
                            ->autofocus()
                            ->extraInputAttributes(['tabindex' => 1])
                            ->live(debounce: 900)
                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                $this->updated($state, $state);
                            }),




            ]),
        ]);
    }

    public function updated($name, $value): void
    {
        if ($name === 'produto_id') {
            $produto = Produto::where('id', '=', $value)->first();
            if ($produto !== null) {
                Notification::make()
                    ->title('Produto selecionado:')
                    ->body('<b>Produto: </b> ' . $produto->nome . '<br> <b>Estoque Atual: </b> ' . $produto->estoque . ' <br> <b>Imagem: </b> <img src="' . (is_array($produto->foto) ? asset('storage/' . ($produto->foto[0] ?? '')) : asset('storage/' . $produto->foto)) . '" alt="' . $produto->nome . '" style="max-width:100px;max-height:100px;">')
                    ->success()
                    ->duration(5000)
                    ->send();
                $addProduto = [
                    'produto_id'        => $produto->id,
                    'venda_p_d_v_id'    => $this->venda,
                    'valor_venda'       => $produto->valor_venda,
                    'pdv_id'            => '',
                    'acres_desc'        => 0,
                    'qtd'               => 1,
                    'sub_total'         => $produto->valor_venda * 1,
                    'valor_custo_atual' => $produto->valor_compra,
                    'total_custo_atual' => $produto->valor_compra,
                ];
                PDVs::create($addProduto);
                $this->produto_id   = '';
                $this->qtd          = '';
                $this->produto_nome = '';
            } else {
                Notification::make()
                    ->title('Produto não cadastrado')
                    ->warning()
                    ->send();
            }
        }
    }

    protected function getTableQuery(): Builder
    {
        return PDVs::query()->where('venda_p_d_v_id', $this->venda);
    }

    protected function getTableColumns(): array
    {
        return [

            TextColumn::make('produto.nome'),
            TextInputColumn::make('qtd')
                ->alignCenter()
                ->summarize(Sum::make()->label('Qtd Produtos'))
                ->updateStateUsing(function (Model $record, $state) {
                    $record->sub_total         = ($state * $record->valor_venda);
                    $record->qtd               = $state;
                    $record->total_custo_atual = ($record->valor_custo_atual * $state);
                    $record->save();
                })

                ->label('Quantidade'),
            TextColumn::make('valor_venda')
                ->alignCenter()
                ->label('Valor Unitário')
                ->money('BRL'),
            // TextInputColumn::make('acres_desc')
            //     ->alignCenter()
            //     ->label('Acres/Desc')
            //     ->updateStateUsing(function (Model $record, $state) {
            //         // Aceita vírgula como separador decimal
            //         $valor = str_replace(',', '.', $state);
            //         $valor = floatval($valor);
            //         $record->sub_total = (((float)$record->qtd * $record->valor_venda) + $valor);
            //         $record->acres_desc = $valor;
            //         $record->save();
            //     })
            //     ->label('Acres/Desc'),
            TextColumn::make('sub_total')
                ->alignCenter()
                ->label('Sub-Total')
                ->money('BRL')
                ->summarize(Sum::make()->label('TOTAL')->money('BRL')),
        ];
    }
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Finalizar (Ctrl+F7)')
                ->icon('heroicon-o-document-currency-dollar')
                ->color('success')
                ->keyBindings('ctrl+f7')
                ->modalHeading('Finalizar Venda - PDV')
                ->model(VendaPDV::class)
                ->createAnother(false)
                ->successNotificationTitle('Venda em PDV finalizada com sucesso!')
                // ->keyBindings(['keypress', 'f7'])
                // ->keyBindings(['command+s', 'ctrl+s'])
                ->form([
                    Grid::make([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 4,
                        '2xl' => 4,
                    ])
                        ->schema([
                            TextInput::make('id')
                                ->label('Código da Venda')
                                ->readOnly()
                                ->default($this->venda)
                                ->columnSpan([
                                    'default' => 1,
                                    'md' => 1,
                                    'xl' => 1,
                                    '2xl' => 1,
                                ]),
                            Radio::make('tipo_registro')
                                ->label('Tipo de Registro')
                                ->live()
                                ->options([
                                    'venda'     => 'Venda',
                                    'orcamento' => 'Orçamento',
                                ])
                                ->default('venda')
                                ->required()
                                ->columnSpan([
                                    'default' => 1,
                                    'md' => 1,
                                    'xl' => 1,
                                    '2xl' => 1,
                                ]),
                            Select::make('funcionario_id')
                                ->label('Vendedor')
                               // ->default('1')
                                ->searchable()
                                ->required()
                                ->columnSpan([
                                    'default' => 1,
                                    'md' => 2,
                                    'xl'  => 2,
                                    '2xl' => 2,
                                ])
                                ->options(Funcionario::all()->pluck('nome', 'id')->toArray()),
                            Select::make('cliente_id')
                                ->label('Cliente')
                                ->required()
                                ->columnSpan([
                                    'default' => 1,
                                    'md' => 2,
                                    'xl'  => 2,
                                    '2xl' => 2,
                                ])
                                ->searchable()
                                ->relationship(name: 'cliente', titleAttribute: 'nome')
                                ->createOptionForm([
                                    Grid::make([
                                        'default' => 1,
                                        'md' => 2,
                                        'xl'  => 4,
                                        '2xl' => 4,
                                    ])
                                        ->schema([
                                            TextInput::make('nome')
                                                ->columnSpan([
                                                    'default' => 1,
                                                    'md' => 2,
                                                    'xl'  => 2,
                                                    '2xl' => 2,
                                                ])
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('cpf_cnpj')
                                                ->label('CPF/CNPJ')
                                                ->mask(RawJs::make(<<<'JS'
                                                        $input.length > 14 ? '99.999.999/9999-99' : '999.999.999-99'
                                                    JS))
                                                ->rule('cpf_ou_cnpj'),
                                            TextInput::make('telefone')
                                                ->minLength(11)
                                                ->maxLength(11)
                                                ->mask('(99)99999-9999')
                                                ->tel()
                                                ->maxLength(255),
                                            Textarea::make('endereco')
                                                ->columnSpan([
                                                    'default' => 1,
                                                    'md' => 2,
                                                    'xl'  => 2,
                                                    '2xl' => 2,
                                                ])
                                                ->label('Endereço'),
                                            TextInput::make('profissao')
                                                ->label('Profissão'),
                                            TextInput::make('email')
                                                ->columnSpan([
                                                    'default' => 1,
                                                    'md' => 2,
                                                    'xl'  => 2,
                                                    '2xl' => 2,
                                                ])
                                                ->email()
                                                ->maxLength(255),
                                        ]),
                                ]),
                            
                            Select::make('forma_pgmto_id')
                                ->label('Forma de Pagamento')
                                ->default('1')
                                ->native(false)
                                ->required()
                                ->searchable()
                                ->columnSpan([
                                    'default' => 1,
                                    'md' => 2,
                                    'xl'  => 2,
                                    '2xl' => 2,
                                ])
                                ->options(FormaPgmto::all()->pluck('nome', 'id')->toArray()),
                            DatePicker::make('data_venda')
                                ->label('Data da Venda')
                                ->required()
                                ->default(now())
                                ->columnSpan([
                                    'default' => 1,
                                    'md' => 1,
                                    'xl' => 1,
                                    '2xl' => 1,
                                ]),
                            TextInput::make('valor_total')
                                ->numeric()
                                ->label('Valor Total')
                                ->readOnly()
                                ->default(function () {
                                    $valorTotal = PDVs::where('venda_p_d_v_id', $this->venda)->sum('sub_total');
                                    return $valorTotal;
                                })
                                ->extraInputAttributes(['style' => 'font-weight: bolder; font-size: 1.3rem; color: #32CD32; text-align: right;'])
                                ->columnSpan([
                                    'default' => 1,
                                    'md' => 2,
                                    'xl'  => 2,
                                    '2xl' => 2,
                                ])
                                ->columnStart([
                                    'xl' => 3,
                                    '2xl' => 3,
                                ]),
                            Section::make('Descontos e Acréscimos')
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                    'xl'  => 2,
                                    '2xl' => 2,
                                ])
                                ->schema([
                                    Radio::make('tipo_acres_desc')
                                        ->label('Tipo de Desconto/Acréscimo')
                                        ->hint('Porcentagem ou Valor')
                                        ->live()
                                        ->options([
                                            'Valor'       => 'Valor',
                                            'Porcentagem' => 'Porcentagem',
                                        ])
                                        ->required(false)
                                        ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                            $set('percent_acres_desc', null);
                                            $set('valor_acres_desc', null);
                                            $set('valor_total_desconto', $get('valor_total'));
                                        }),
                                    TextInput::make('percent_acres_desc')
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
                                    TextInput::make('valor_acres_desc')
                                        ->label('Valor Desconto/Acréscimo')
                                        ->hint('Para desconto use um valor negativo Ex. -10')
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
                            Radio::make('financeiro')
                                ->label('Lançamento Financeiro')
                                ->visible(fn (callable $get) => $get('tipo_registro') === 'venda')
                                ->live()
                                ->options([
                                    '1' => 'Direto no Caixa',
                                    '2' => 'Conta a Receber',
                                ])->default('1')
                                ->columnSpan([
                                    'default' => 1,
                                    'md' => 2,
                                    'xl' => 2,
                                    '2xl' => 2,
                                ]),
                            TextInput::make('parcelas')
                                ->numeric()
                                ->required()
                                ->label('Qtd de Parcelas')
                                ->hidden(fn (Get $get): bool => $get('financeiro') != '2' or $get('tipo_registro') === 'orcamento')
                                ->columnSpan([
                                    'default' => 1,
                                    'md' => 1,
                                    'xl' => 1,
                                    '2xl' => 1,
                                ]),
                            TextInput::make('valor_total_desconto')
                                ->numeric()
                                ->label('Valor Total c/ Desconto/Acréscimo')
                                ->readOnly()
                                ->default(function () {
                                    $valorTotal = PDVs::where('venda_p_d_v_id', $this->venda)->sum('sub_total');
                                    return $valorTotal;
                                })
                                ->extraInputAttributes(['style' => 'font-weight: bolder; font-size: 1.3rem; color: #32CD32; text-align: right;'])
                                ->columnSpan([
                                    'default' => 1,
                                    'md' => 2,
                                    'xl'  => 2,
                                    '2xl' => 2,
                                ])
                                ->columnStart([
                                    'xl' => 3,
                                    '2xl' => 3,
                                ]),
                        ]),
                ])
                ->after(function ($data) {

                    if ($data['tipo_registro'] === 'venda') {
                        $itensPDV = PDVs::where('venda_p_d_v_id', $this->venda)->get();

                        foreach ($itensPDV as $itens) {
                            $updProduto = Produto::find($itens->produto_id);
                            $updProduto->estoque -= $itens->qtd;
                            $updProduto->save();
                        }
                    }
                })
                ->successRedirectUrl(function ($data, $record) {
                    // Gere o comprovante redirecionando para uma rota que exibe o comprovante.
                    // Certifique-se de criar a rota 'comprovantePDV' no seu web.php e uma página/controle para exibir o comprovante.
                    // Exemplo de redirecionamento:

                    if ($data['tipo_registro'] === 'venda') {
                        if ($data['financeiro'] == 1) {

                            $addFluxoCaixa = [
                                'valor' => ($data['valor_total_desconto']),
                                'tipo'  => 'CREDITO',
                                'id_lancamento' => $record->id,
                                'obs'   => 'Recebido da venda nº: ' . $this->venda . '',
                            ];
                            Notification::make()
                                ->title('Valor lançado no fluxo de caixa!')
                                ->body('R$ ' . number_format($data['valor_total_desconto'], 2, ',', '.') . '')
                                ->success()
                                ->send();
                            FluxoCaixa::create($addFluxoCaixa);
                        } else {
                            $valor_parcela = ($record->valor_total_desconto / $data['parcelas']);
                            $vencimentos   = Carbon::now();
                            for ($cont = 0; $cont < $data['parcelas']; $cont++) {
                                $dataVencimentos = $vencimentos->addDays(30);
                                $parcelas        = [
                                    'vendapdv_id'     => $this->venda,
                                    'cliente_id'      => $data['cliente_id'],
                                    'valor_total'     => $data['valor_total_desconto'],
                                    'parcelas'        => $data['parcelas'],
                                    'ordem_parcela'   => $cont + 1,
                                    'data_vencimento' => $dataVencimentos,
                                    'valor_recebido'  => 0.00,
                                    'status'          => 0,
                                    'obs'             => 'Venda em PDV - Nº ' . $this->venda,
                                    'valor_parcela'   => $valor_parcela,
                                ];
                                ContasReceber::create($parcelas);
                            }
                            Notification::make()
                                ->title('Valor lançado no contas a receber!')
                                ->body('Valor de R$ ' . number_format($data['valor_total_desconto'], 2, ',', '.') . ' lançado no contas a receber para o cliente <b>' . ($record->cliente?->nome ?? '') . '</b>, em  <b>' . $data['parcelas'] . '</b> parcelas.')
                                ->success()
                                ->duration(20000)
                                ->send();

                        } // <-- Adicione esta chave de fechamento aqui

                        return route('filament.admin.resources.venda-p-d-vs.index');
                    }

                    return route('filament.admin.resources.venda-p-d-vs.index');






                }),

        ];
    }

    protected function getTableActions(): array
    {
        return [
            DeleteAction::make('Excluir'),
        ];
    }
}
