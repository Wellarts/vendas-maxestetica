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
                ->disabled(fn ($record) => PDV::where('venda_p_d_v_id', $record->id)->count())
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

            Actions\Action::make('converter_venda')
                ->label('Converter em Venda')
                ->icon('heroicon-o-arrow-right')
                ->requiresConfirmation()
                ->modalHeading('Converter em Venda')
                ->modalDescription('Caso tenha feito alterações no formulário é necesssário salvar para depois converter em venda. Tem certeza que deseja converter este orçamento em venda?')
                ->visible(fn ($record) => $record->tipo_registro === 'orcamento')
                ->action(function ($record) {
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
                                'obs'   => 'Recebido da venda nº: ' . $record->id,
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
