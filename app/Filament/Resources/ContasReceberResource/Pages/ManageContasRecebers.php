<?php

namespace App\Filament\Resources\ContasReceberResource\Pages;

use App\Filament\Resources\ContasReceberResource;
use App\Models\ContasReceber;
use App\Models\FluxoCaixa;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageContasRecebers extends ManageRecords
{
    protected static string $resource = ContasReceberResource::class;

    protected static ?string $title = 'Contas a Receber/Recebidas';

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
                              'venda_id'        => $record->venda_id,
                              'cliente_id'      => $data['cliente_id'],
                              'valor_total'     => $data['valor_total'],
                              'parcelas'        => $data['parcelas'],
                              'ordem_parcela'   => $cont + 1,
                              'data_vencimento' => $dataVencimentos,
                              'valor_recebido'  => 0.00,
                              'status'          => 0,
                              'obs'             => $data['obs'],
                              'valor_parcela'   => $valor_parcela,
                            ];
                            ContasReceber::create($parcelas);
                        }
                    } else {
                        if (($data['status'] == 1)) {
                            $addFluxoCaixa = [
                                'id_lancamento' => $record->id,
                                'valor' => ($record->valor_total),
                                'tipo'  => 'CREDITO',
                                'obs'   => 'Recebimento de conta: ' . $record->cliente->nome . '',
                            ];                      
                            FluxoCaixa::create($addFluxoCaixa);
                        }
                    }
                }
            ),

        ];
    }
}
