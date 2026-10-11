<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VendaPDV;

class LucratividadePDVCsvController extends Controller
{
    public function exportar(Request $request)
    {
        $detalhado = $request->boolean('detalhado');

        $query = VendaPDV::with(['cliente:id,nome', 'funcionario:id,nome', 'formaPgmto:id,nome'])
            ->where('tipo_registro', 'venda')
            ->withSum('itensVenda as total_custo_produtos', 'total_custo_atual');

        if ($detalhado) {
            $query->with('itensVenda.produto:id,nome');
        }

        // Filtros (mesma regra do PDF)
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
            $query->where('data_venda', '>=', $request->data_de);
        }
        if ($request->filled('data_ate')) {
            $query->where('data_venda', '<=', $request->data_ate . ' 23:59:59');
        }

        $nomeArquivo = 'relatorio_lucratividade_pdv_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query, $detalhado) {
            set_time_limit(0);

            // Número no formato pt-BR (vírgula decimal, sem separador de milhar) para o Excel reconhecer como número
            $n = fn ($v) => number_format((float) $v, 2, ',', '');
            // Evita que o Excel interprete texto começando com = + - @ como fórmula
            $t = fn ($s) => preg_match('/^[=+\-@]/', (string) $s) ? "'" . $s : (string) $s;

            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8: acentos corretos no Excel

            fputcsv($out, [
                'Venda', 'Data', 'Cliente', 'Vendedor', 'Forma Pgto',
                'Valor Total', 'Asc/Desc Valor', 'Asc/Desc %', 'Total c/ Desc/Acres',
                'Custo Produtos', 'Lucratividade',
                'Produto', 'Qtd', 'Preço Unitário', 'Subtotal',
            ], ';');

            $somaValorTotal = 0;
            $somaDesconto = 0;
            $somaCusto = 0;
            $somaLucro = 0;
            $contador = 0;

            // lazyById: lê em blocos de 500 (com eager loading) e libera a memória a cada bloco
            foreach ($query->lazyById(500, 'id') as $venda) {
                $custo = (float) ($venda->total_custo_produtos ?? 0);
                $lucro = $venda->valor_total_desconto - $custo;

                $somaValorTotal += $venda->valor_total;
                $somaDesconto += $venda->valor_total_desconto;
                $somaCusto += $custo;
                $somaLucro += $lucro;

                fputcsv($out, [
                    $venda->id,
                    \Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y'),
                    $t($venda->cliente->nome ?? '-'),
                    $t($venda->funcionario->nome ?? '-'),
                    $t($venda->formaPgmto->nome ?? '-'),
                    $n($venda->valor_total),
                    $n($venda->valor_acres_desc),
                    $venda->percent_acres_desc ? $n($venda->percent_acres_desc) : '',
                    $n($venda->valor_total_desconto),
                    $n($custo),
                    $n($lucro),
                    '', '', '', '',
                ], ';');

                if ($detalhado) {
                    foreach ($venda->itensVenda as $item) {
                        fputcsv($out, [
                            $venda->id, '', '', '', '',
                            '', '', '', '', '', '',
                            $t($item->produto->nome ?? '-'),
                            $item->qtd,
                            $n($item->valor_venda),
                            $n($item->sub_total),
                        ], ';');
                    }
                }

                // Envia os dados aos poucos: mantém a conexão ativa e evita o 504 do nginx
                if (++$contador % 200 === 0) {
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }
            }

            fputcsv($out, [
                'TOTAL', '', '', '', '',
                $n($somaValorTotal), '', '', $n($somaDesconto),
                $n($somaCusto), $n($somaLucro),
                '', '', '', '',
            ], ';');

            fclose($out);
        }, $nomeArquivo, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
