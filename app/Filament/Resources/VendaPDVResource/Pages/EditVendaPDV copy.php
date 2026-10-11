<?php

namespace App\Filament\Resources\VendaPDVResource\Pages;

use App\Filament\Resources\VendaPDVResource;
use App\Models\PDV;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVendaPDV extends EditRecord
{

    protected static string $resource = VendaPDVResource::class;

    protected static ?string $title = 'Venda PDV';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->disabled(fn($record) => PDV::where('venda_p_d_v_id', $record->id)->count())
                ->after(function ($record) {
                    if ($record->financeiro == 1) {
                        // Excluir do fluxo de caixa
                        \App\Models\FluxoCaixa::where('id_lancamento', $record->id)->delete();

                        \Filament\Notifications\Notification::make()
                            ->title('Lançamento removido do fluxo de caixa!')
                            ->body('O lançamento referente à venda nº ' . $record->id . ' foi removido do fluxo de caixa.')
                            ->success()
                            ->send();
                    } else {
                        // Excluir parcelas do contas a receber
                        \App\Models\ContasReceber::where('vendapdv_id', $record->id)->delete();

                        \Filament\Notifications\Notification::make()
                            ->title('Parcelas removidas do contas a receber!')
                            ->body('As parcelas referentes à venda nº ' . $record->id . ' foram removidas do contas a receber.')
                            ->success()
                            ->send();
                    }
                }),
            Actions\Action::make('ajustar_valores')
                ->label('Ajustar Valores')
                ->icon('heroicon-o-currency-dollar')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->valor_total = $record->pdv->sum('sub_total');
                    $record->valor_total_desconto = $record->valor_total;
                    if ($record->tipo_acres_desc == 'Valor') {
                        $record->valor_total_desconto += $record->valor_acres_desc;
                    } elseif ($record->tipo_acres_desc == 'Porcentagem') {
                        $record->valor_total_desconto += $record->valor_total * ($record->percent_acres_desc / 100);
                    }
                    if ($record->save()) {
                        // Define a linha de desconto/acréscimo conforme o tipo
                        $linhaDesconto = match ($record->tipo_acres_desc) {
                            'Valor'       => 'Valor de Desconto/Acréscimo: R$ ' . number_format($record->valor_acres_desc, 2, ',', '.'),
                            'Porcentagem' => 'Valor de Desconto/Acréscimo: ' . $record->percent_acres_desc . '%',
                            default       => 'Sem Desconto ou Acréscimo',
                        };

                        $corpo = 'Os valores da venda foram ajustados: <br>' .
                            'Valor Total dos produtos: R$ ' . number_format($record->pdv->sum('sub_total'), 2, ',', '.') . '<br>' .
                            $linhaDesconto . '<br>' .
                            'Valor com Desconto: R$ ' . number_format($record->valor_total_desconto, 2, ',', '.') .
                            '<br>Atualize a página para ver as alterações (F5).';

                        \Filament\Notifications\Notification::make()
                            ->title('Valores ajustados!')
                            ->body($corpo)
                            ->success()
                            ->persistent()
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('Erro ao ajustar valores!')
                            ->body('Ocorreu um erro ao ajustar os valores da venda. Tente novamente.')
                            ->danger()
                            ->send();
                    }
                }),
            Actions\Action::make('converter_venda')
                ->label('Converter em Venda')
                ->icon('heroicon-o-arrow-right')
                ->requiresConfirmation()
                ->modalHeading('Converter em Venda')
                ->modalDescription('Caso tenha feito alterações no formulário é necesssário salvar para depois converter em venda. Tem certeza que deseja converter este orçamento em venda?')
                ->visible(fn($record) => $record->tipo_registro === 'orcamento')
                ->action(function ($record) {
                    // Verifica se algum item do estoque é menor que a quantidade vendida
                    foreach ($record->pdv as $item) {
                        $produto = $item->Produto;
                        if ($produto && $produto->estoque < $item->qtd) {
                            \Filament\Notifications\Notification::make()
                                ->title('CONVERÇÃO NÃO REALIZADA - Estoque insuficiente!')
                                ->body('O produto <b>' . ($produto->nome ?? '') . '</b> tem estoque insuficiente para a quantidade vendida. Estoque atual: ' . $produto->estoque . ' - Quantidade vendida: ' . $item->qtd)
                                ->danger()
                                ->send();
                            return;
                        }
                    }

                    // Converte o orçamento em venda    
                    $record->tipo_registro = 'venda';
                    $record->data_venda    = now();
                    $record->save();

                    // Atualiza o estoque dos produtos
                    foreach ($record->pdv as $item) {
                        $produto = $item->Produto;
                        if ($produto) {
                            $produto->estoque -= $item->qtd;
                            $produto->save();
                        }
                    }

                    // Lógica de lançamento financeiro
                    $parcelas             = $record->parcelas             ?? 1;
                    $financeiro           = $record->financeiro           ?? 1;
                    $valor_total_desconto = $record->valor_total_desconto ?? 0;
                    $cliente_id           = $record->cliente_id           ?? null;

                    if ($record->tipo_registro === 'venda') {
                        if ($financeiro == 1) {
                            $addFluxoCaixa = [
                                'valor' => $valor_total_desconto,
                                'tipo'  => 'CREDITO',
                                'id_lancamento' => $record->id,
                                'obs'   => 'Venda nº: ' . ($record->id ?? '') . ' - Cliente: ' . ($record->cliente->nome ?? '') . ' - Forma de Pgto: ' . ($record->formaPgmto->nome ?? ''),
                            ];

                            \Filament\Notifications\Notification::make()
                                ->title('Valor lançado no fluxo de caixa!')
                                ->body('R$ ' . number_format($valor_total_desconto, 2, ',', '.'))
                                ->success()
                                ->send();

                            \App\Models\FluxoCaixa::create($addFluxoCaixa);
                        } else {
                            $valor_parcela = $valor_total_desconto / $parcelas;
                            $vencimentos   = \Carbon\Carbon::now();

                            for ($cont = 0; $cont < $parcelas; $cont++) {
                                $dataVencimentos = $vencimentos->copy()->addDays(30 * $cont);

                                $parcelasData = [
                                    'vendapdv_id'     => $record->id,
                                    'cliente_id'      => $cliente_id,
                                    'valor_total'     => $valor_total_desconto,
                                    'parcelas'        => $parcelas,
                                    'ordem_parcela'   => $cont + 1,
                                    'data_vencimento' => $dataVencimentos,
                                    'valor_recebido'  => 0.00,
                                    'status'          => 0,
                                    'obs'             => 'Venda em PDV - Nº ' . $record->id,
                                    'valor_parcela'   => $valor_parcela,
                                ];

                                \App\Models\ContasReceber::create($parcelasData);
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Valor lançado no contas a receber!')
                                ->body('Valor de R$ ' . number_format($valor_total_desconto, 2, ',', '.') . ' lançado no contas a receber para o cliente <b>' . ($record->cliente?->nome ?? '') . '</b>, em <b>' . $parcelas . '</b> parcelas.')
                                ->success()
                                ->duration(20000)
                                ->send();
                        }
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Orçamento convertido em venda e estoque atualizado!')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function afterSave(): void
    {

        if ($this->record->tipo_registro !== 'orcamento') {
            \Filament\Notifications\Notification::make()
                ->title('Atenção')
                ->body('Se houve alterações de valores. Faça os ajustes nas parcelas ou fluxo de caixa.')
                ->danger()
                ->persistent()
                ->send();
        }
    }
}
