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

        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status !== '' && $status !== null) {
                $query->where('status', (int)$status);
            }
        }

        $contas = $query->with('cliente')->orderBy('data_vencimento')->get();

        // Filtros com nomes
        $clienteNome = null;
        if ($request->filled('cliente_id')) {
            $cliente = Cliente::find($request->cliente_id);
            $clienteNome = $cliente ? $cliente->nome : $request->cliente_id;
        }
        // Exibe o nome do status mesmo se for 0
        $statusFiltro = null;
        if ($request->has('status') && $request->input('status') !== '' && $request->input('status') !== null) {
            $statusInt = (int)$request->input('status');
            if ($statusInt === 1) {
                $statusFiltro = 'Recebido';
            } elseif ($statusInt === 0) {
                $statusFiltro = 'Em aberto';
            } else {
                $statusFiltro = 'Todos';
            }
        } else {
            $statusFiltro = 'Todos';
        }
        
        $filtrosNomes = [
            'Cliente' => $clienteNome,
            'Data Inicial' => $request->filled('data_de') ? $request->data_de : null,
            'Data Final' => $request->filled('data_ate') ? $request->data_ate : null,
            'Status' => $statusFiltro,
        ];
      
        $pdf = Pdf::loadView('relatorios.contas-receber-pdf', [
            'contas' => $contas,
            'filtrosNomes' => $filtrosNomes,
            'clientes' => Cliente::pluck('nome', 'id'),
        ])->setPaper('a4', 'landscape');
        return $pdf->stream('contas-a-receber.pdf');
    }
}
