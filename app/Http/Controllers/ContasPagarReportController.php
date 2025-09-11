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
        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status !== '' && $status !== null) {
                $query->where('status', (int)$status);
            }
        }

        $contas = $query->with('fornecedor')->orderBy('data_vencimento')->get();

        // Filtros com nomes
        $fornecedorNome = null;
        if ($request->filled('fornecedor_id')) {
            $fornecedor = Fornecedor::find($request->fornecedor_id);
            $fornecedorNome = $fornecedor ? $fornecedor->nome : $request->fornecedor_id;
        }
        // Exibe o nome do status mesmo se for 0
        $statusFiltro = null;
        if ($request->has('status') && $request->input('status') !== '' && $request->input('status') !== null) {
            $statusInt = (int)$request->input('status');
            if ($statusInt === 1) {
                $statusFiltro = 'Pago';
            } elseif ($statusInt === 0) {
                $statusFiltro = 'Em aberto';
            } else {
                $statusFiltro = 'Todos';
            }
        } else {
            $statusFiltro = 'Todos';
        }
        $filtrosNomes = [
            'Fornecedor' => $fornecedorNome,
            'Data Inicial' => $request->filled('data_de') ? $request->data_de : null,
            'Data Final' => $request->filled('data_ate') ? $request->data_ate : null,
            'Status' => $statusFiltro,
        ];
        $pdf = Pdf::loadView('relatorios.contas-pagar-pdf', [
            'contas' => $contas,
            'filtrosNomes' => $filtrosNomes,
            'fornecedores' => Fornecedor::pluck('nome', 'id'),
        ])->setPaper('a4', 'landscape');
        return $pdf->stream('contas-a-pagar.pdf');
    }
}
