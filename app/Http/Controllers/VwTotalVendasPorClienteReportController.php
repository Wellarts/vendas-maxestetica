<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\VwTotalVendasPorCliente;

class VwTotalVendasPorClienteReportController extends Controller
{
    public function gerarRelatorio(Request $request)
    {
        $query = VwTotalVendasPorCliente::query();

        if ($request->filled('cliente')) {
            $query->where('cliente_nome', 'like', '%' . $request->cliente . '%');
        }

        $registros = $query->orderByDesc('valor_total_desconto')->get();
        $somaTotal = $registros->sum('valor_total_desconto');



        $pdf = Pdf::loadView('reports.vw-total-vendas-por-cliente', [
            'registros' => $registros,
            'somaTotal' => $somaTotal,
        ])->setPaper('a4', 'landscape');
        return $pdf->stream('relatorio_vendas_por_cliente.pdf');
    }
}
