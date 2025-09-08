<?php

namespace App\Http\Controllers;

use App\Models\ContasReceber;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ContasReceberReportController extends Controller
{
    public function pdf(Request $request)
    {
        $query = ContasReceber::query();

        if ($request->filled('data_de')) {
            $query->whereDate('data_vencimento', '>=', $request->data_de);
        }
        if ($request->filled('data_ate')) {
            $query->whereDate('data_vencimento', '<=', $request->data_ate);
        }
        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $contas = $query->with('cliente')->orderBy('data_vencimento')->get();

        $pdf = Pdf::loadView('relatorios.contas-receber-pdf', [
            'contas' => $contas,
            'filtros' => $request->all(),
            'clientes' => Cliente::pluck('nome', 'id'),
        ])->setPaper('a4', 'landscape');
        return $pdf->stream('contas-a-receber.pdf');
    }
}
