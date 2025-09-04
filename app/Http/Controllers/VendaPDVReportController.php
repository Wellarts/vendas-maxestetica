<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\VendaPDV;

class VendaPDVReportController extends Controller
{
    public function vendasPdf(Request $request)
    {
        $vendas = VendaPDV::with('produtos')->get();
        $pdf = Pdf::loadView('reports.vendas', compact('vendas'));
        return $pdf->download('relatorio_vendas.pdf');
    }

    public function vendasPorProdutoPdf(Request $request)
        {
            $produtoId = $request->input('produto_id');
            $dataInicial = $request->input('data_inicial');
            $dataFinal = $request->input('data_final');

            $query = \DB::table('vw_produtos_vendidos_pdv');

            if ($produtoId) {
                $query->where('produto_id', $produtoId);
            }
            if ($dataInicial) {
                $query->whereDate('data_venda', '>=', $dataInicial);
            }
            if ($dataFinal) {
                $query->whereDate('data_venda', '<=', $dataFinal);
            }

            $vendas = $query->get();

            $pdf = Pdf::loadView('reports.vendas', compact('vendas', 'produtoId', 'dataInicial', 'dataFinal'))
                ->setPaper('a4', 'landscape');
            return $pdf->stream('relatorio_venda_produto.pdf');
    }
}
