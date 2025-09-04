<?php

namespace App\Http\Controllers;

use App\Models\contasPagar;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class ControllerNovaParcelaPagar extends Controller
{
    public function novaParcelaPagar($id)
    {
        //  dd(ContasReceber::find($id));

        $parcela = contasPagar::find($id);
        // dd($parcela);

        $addNovaParcela = [
            'fornecedor_id'   => ($parcela->fornecedor_id),
            'compra_id'       => ($parcela->compra_id),
            'parcelas'        => 1,
            'ordem_parcela'   => 1,
            'data_vencimento' => Carbon::now()->addDays(30),
            'status'          => 0,
            'valor_total'     => ($parcela->valor_total),
            'valor_parcela'   => ($parcela->valor_parcela - $parcela->valor_pago),
            'obs'             => 'Parcela restante referente ao pagamento parcial da parcela
                                                ' . $parcela->ordem_parcela . ' da venda ' . $parcela->compra_id . '',
        ];
        contasPagar::create($addNovaParcela);
        Notification::make()
            ->title('Nova parcela gereda com sucesso!')
            ->success()
            ->send();

        return redirect()->route('filament.admin.resources.contas-pagars.index');
    }
}
