<?php

namespace App\Http\Controllers;

use App\Models\ContasReceber;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class ControllerNovaParcela extends Controller
{
    public function novaParcela($id)
    {
        //  dd(ContasReceber::find($id));

        $parcela = ContasReceber::find($id);
        // dd($parcela);

        $addNovaParcela = [
            'cliente_id'      => ($parcela->cliente_id),
            'venda_id'        => ($parcela->venda_id),
            'vendapdv_id'     => ($parcela->vendapdv_id),
            'parcelas'        => 1,
            'ordem_parcela'   => 1,
            'data_vencimento' => Carbon::now()->addDays(30),
            'status'          => 0,
            'valor_total'     => ($parcela->valor_total),
            'valor_parcela'   => ($parcela->valor_parcela - $parcela->valor_recebido),
            'obs'             => 'Parcela restante referente ao pagamento parcial da parcela
                                                ' . $parcela->ordem_parcela . ' da venda ' . $parcela->venda_id . '',
        ];
        ContasReceber::create($addNovaParcela);
        Notification::make()
            ->title('Nova parcela gereda com sucesso!')
            ->success()
            ->send();

        return redirect()->route('filament.admin.resources.contas-recebers.index');
    }
}
