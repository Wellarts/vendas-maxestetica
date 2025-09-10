<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\VendaPDV;

class LucratividadePDVPdfController extends Controller
{
    public function gerarRelatorio(Request $request)
    {

        $query = VendaPDV::with(['cliente', 'funcionario', 'formaPgmto', 'itensVenda.produto'])
            ->where('tipo_registro', 'venda')
            ->withSum('itensVenda as total_custo_produtos', 'total_custo_atual');

        // Filtros
        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }
        if ($request->filled('funcionario_id')) {
            $query->where('funcionario_id', $request->funcionario_id);
        }
        if ($request->filled('forma_pgmto_id')) {
            $query->where('forma_pgmto_id', $request->forma_pgmto_id);
        }
        if ($request->filled('data_de')) {
            $query->whereDate('data_venda', '>=', $request->data_de);
        }
        if ($request->filled('data_ate')) {
            $query->whereDate('data_venda', '<=', $request->data_ate);
        }

        $vendas = $query->get();

        // Cálculos dos somatórios
        $somaCustoProdutos = $vendas->sum('total_custo_produtos');
        $somaValorTotal = $vendas->sum('valor_total');
        $somaValorTotalDesconto = $vendas->sum('valor_total_desconto');
        $somaLucro = $vendas->sum(function($venda) {
            return $venda->valor_total_desconto - ($venda->total_custo_produtos ?? 0);
        });

        // Buscar nomes dos filtros
        $clienteNome = null;
        $funcionarioNome = null;
        $formaPgmtoNome = null;
        if ($request->filled('cliente_id')) {
            $cliente = \App\Models\Cliente::find($request->cliente_id);
            $clienteNome = $cliente ? $cliente->nome : $request->cliente_id;
        }
        if ($request->filled('funcionario_id')) {
            $funcionario = \App\Models\Funcionario::find($request->funcionario_id);
            $funcionarioNome = $funcionario ? $funcionario->nome : $request->funcionario_id;
        }
        if ($request->filled('forma_pgmto_id')) {
            $forma = \App\Models\FormaPgmto::find($request->forma_pgmto_id);
            $formaPgmtoNome = $forma ? $forma->nome : $request->forma_pgmto_id;
        }

        $pdf = Pdf::loadView('reports.lucratividade-pdv', [
            'vendas' => $vendas,
            'somaCustoProdutos' => $somaCustoProdutos,
            'somaValorTotal' => $somaValorTotal,
            'somaValorTotalDesconto' => $somaValorTotalDesconto,
            'somaLucro' => $somaLucro,
            'filtrosNomes' => [
                'Cliente' => $clienteNome,
                'Funcionário' => $funcionarioNome,
                'Forma de Pagamento' => $formaPgmtoNome,
                'Data Inicial' => $request->filled('data_de') ? $request->data_de : null,
                'Data Final' => $request->filled('data_ate') ? $request->data_ate : null,
            ],
        ])->setPaper('a4', 'landscape');
        return $pdf->stream('relatorio_lucratividade_pdv.pdf');
    }
}
