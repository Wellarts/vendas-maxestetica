<?php

namespace App\Filament\Resources\ContasPagarResource\Pages;

use App\Filament\Resources\ContasPagarResource;
use App\Models\contasPagar;
use App\Models\FluxoCaixa;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageContasPagars extends ManageRecords
{
    protected static string $resource = ContasPagarResource::class;

    protected static ?string $title = 'Contas a Pagar/Pagas';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo')
                ->after(
                    function ($data, $record, $livewire) {
                        if ($record->parcelas > 1) {
                            $valor_parcela = ($record->valor_total / $record->parcelas);
                            $vencimentos   = Carbon::create($record->data_vencimento);
                            for ($cont = 1; $cont < $data['parcelas']; $cont++) {
                                $dataVencimentos = $vencimentos->addDays(30);
                                $parcelas        = [
                                    'compra_id'       => $record->compra_id,
                                    'fornecedor_id'   => $data['fornecedor_id'],
                                    'valor_total'     => $data['valor_total'],
                                    'parcelas'        => $data['parcelas'],
                                    'ordem_parcela'   => $cont + 1,
                                    'data_vencimento' => $dataVencimentos,
                                    'valor_pago'      => 0.00,
                                    'status'          => 0,
                                    'obs'             => $data['obs'],
                                    'valor_parcela'   => $valor_parcela,
                                ];
                                contasPagar::create($parcelas);
                            }
                        } else {
                            if (($data['status'] == 1)) {
                                  // 1. Pegue a data da variável (formato esperado: 'YYYY-MM-DD')
                            $data_apenas = date('Y-m-d', strtotime($record->data_pagamento));

                            // 2. Pegue a hora atual
                            $hora_apenas = date('H:i:s');

                            // 3. Combine a data e a hora (resulta em: 'YYYY-MM-DD H:i:s')
                            $created_at_combinado = $data_apenas . ' ' . $hora_apenas;
                                $addFluxoCaixa = [
                                    'id_lancamento' => $record->id,
                                    'valor' => ($record->valor_total * -1),
                                    'created_at' => $created_at_combinado,
                                    'updated_at' => $created_at_combinado,
                                    'tipo'  => 'DEBITO',
                                    'obs'   => 'Pagamento da conta: ' . $record->fornecedor->nome . '',
                                ];

                                FluxoCaixa::create($addFluxoCaixa);
                            }
                        }
                    }
                ),
            Actions\Action::make('exportar_pdf')
                ->label('Relatório de Pagamentos')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('data_de')->label('Vencimento de'),
                    \Filament\Forms\Components\DatePicker::make('data_ate')->label('Vencimento até'),
                    \Filament\Forms\Components\Select::make('fornecedor_id')
                        ->label('Fornecedor')
                        ->options(\App\Models\Fornecedor::all()->pluck('nome', 'id')->toArray())
                        ->searchable(),
                    \Filament\Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            '' => 'Todos',
                            '0' => 'Em aberto',
                            '1' => 'Pago',
                        ]),
                ])
                ->action(function (array $data, $livewire) {
                    // Só envia o filtro de status se for 0 ou 1
                    if (array_key_exists('status', $data) && ($data['status'] === 0 || $data['status'] === 1 || $data['status'] === '0' || $data['status'] === '1')) {
                        $data['status'] = (int) $data['status'];
                    } else {
                        unset($data['status']); // Remove para exibir todos
                    }

                    // Remove apenas valores nulos ou string vazia, mas mantém 0
                    $query = http_build_query(array_filter($data, function($v) {
                        return $v !== null && $v !== '';
                    }));
                    $url = route('relatorio.contas.pagar.pdf') . '?' . $query;
                    $livewire->js("window.open('{$url}', '_blank')");
                }),
        ];
    }
}
