<?php

namespace App\Http\Controllers;

use App\Models\contasPagar;
use App\Models\Fornecedor;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ContasPagarReportController extends Controller
{
    public function pdf(Request $request)
    {
        $query = contasPagar::query();

        if ($request->filled('data_de')) {
            $query->whereDate('data_vencimento', '>=', $request->data_de);
        }
        if ($request->filled('data_ate')) {
            $query->whereDate('data_vencimento', '<=', $request->data_ate);
        }
        if ($request->filled('fornecedor_id')) {
            $query->where('fornecedor_id', $request->fornecedor_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $contas = $query->with('fornecedor')->orderBy('data_vencimento')->get();

        $pdf = Pdf::loadView('relatorios.contas-pagar-pdf', [
            'contas' => $contas,
            'filtros' => $request->all(),
            'fornecedores' => Fornecedor::pluck('nome', 'id'),
        ])->setPaper('a4', 'landscape');
        return $pdf->stream('contas-a-pagar.pdf');
    }
}
