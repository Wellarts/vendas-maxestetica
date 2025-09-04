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
                        $addFluxoCaixa = [
                            'valor' => ($record->valor_total * -1),
                            'tipo'  => 'DEBITO',
                            'obs'   => 'Pagamento da conta: ' . $record->fornecedor->nome . '',
                        ];

                        FluxoCaixa::create($addFluxoCaixa);
                    }


                }
            ),
        ];
    }
}
