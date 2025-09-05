<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\VendaPDV;

class LucratividadePDVPdfController extends Controller
{
    public function gerarRelatorio(Request $request)
    {
        $vendas = VendaPDV::with(['cliente', 'funcionario', 'itensVenda.produto'])
            ->withSum('itensVenda as total_custo_produtos', 'total_custo_atual')
            ->get();

        // Cálculos dos somatórios
        $somaCustoProdutos = $vendas->sum('total_custo_produtos');
        $somaValorTotal = $vendas->sum('valor_total');
        $somaValorTotalDesconto = $vendas->sum('valor_total_desconto');
        $somaLucro = $vendas->sum(function($venda) {
            return $venda->valor_total_desconto - ($venda->total_custo_produtos ?? 0);
        });

        $pdf = Pdf::loadView('reports.lucratividade-pdv', [
            'vendas' => $vendas,
            'somaCustoProdutos' => $somaCustoProdutos,
            'somaValorTotal' => $somaValorTotal,
            'somaValorTotalDesconto' => $somaValorTotalDesconto,
            'somaLucro' => $somaLucro,
        ])->setPaper('a4', 'landscape');
        return $pdf->stream('relatorio_lucratividade_pdv.pdf');
    }
}
