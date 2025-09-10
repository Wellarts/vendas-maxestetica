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

        // Filtros com nomes
        $clienteNome = null;
        if ($request->filled('cliente_id')) {
            $cliente = Cliente::find($request->cliente_id);
            $clienteNome = $cliente ? $cliente->nome : $request->cliente_id;
        }
        $filtrosNomes = [
            'Cliente' => $clienteNome,
            'Data Inicial' => $request->filled('data_de') ? $request->data_de : null,
            'Data Final' => $request->filled('data_ate') ? $request->data_ate : null,
            'Status' => $request->filled('status') ? ($request->status == 1 ? 'Recebido' : 'Em aberto') : null,
        ];
        $pdf = Pdf::loadView('relatorios.contas-receber-pdf', [
            'contas' => $contas,
            'filtrosNomes' => $filtrosNomes,
            'clientes' => Cliente::pluck('nome', 'id'),
        ])->setPaper('a4', 'landscape');
        return $pdf->stream('contas-a-receber.pdf');
    }
}
