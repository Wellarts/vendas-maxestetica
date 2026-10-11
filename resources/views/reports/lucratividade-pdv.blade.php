@extends('layouts.pdf')

@section('content')
    <h2>Relatório de Lucratividade PDV</h2>
    @if (isset($filtrosNomes) && collect($filtrosNomes)->filter()->count())
        <div
            style="margin-bottom: 18px; font-size: 1rem; color: #888; background: #f4f6f9; padding: 8px 12px; border-radius: 6px;">
            <strong>Filtros aplicados:</strong>
            @php $sep = false; @endphp
            @foreach ($filtrosNomes as $chave => $valor)
                @if ($valor)
                    @if ($sep)
                        |
                    @endif
                    <span><b>{{ $chave }}:</b>
                        @if (str_contains($chave, 'Data') && $valor)
                            {{ \Carbon\Carbon::parse($valor)->format('d/m/Y') }}
                        @else
                            {{ $valor }}
                        @endif
                    </span>
                    @php $sep = true; @endphp
                @endif
            @endforeach
        </div>
    @endif
    <style>
        table.reduzida th,
        table.reduzida td {
            padding: 2px 4px;
            font-size: 0.95em;
        }

        tr.venda td {
            background-color: #e9edf2;
            font-weight: bold;
        }

        tr.itens-cab td {
            background-color: #f7f7f7;
            color: #666;
            font-size: 0.85em;
        }
    </style>

    {{-- Uma única tabela para todas as vendas: bem mais leve para o dompdf do que fieldset + 2 tabelas por venda --}}
    <table width="100%" border="1" cellspacing="0" cellpadding="0" class="reduzida">
        <thead>
            <tr>
                <th style="width:6%">Venda</th>
                <th style="width:16%">Cliente</th>
                <th style="width:9%">Data</th>
                <th style="width:14%">Vendedor</th>
                <th style="width:12%">Forma Pgto</th>
                <th style="width:10%">Valor Total</th>
                <th style="width:9%">Asc/Desc Valor</th>
                <th style="width:6%">Asc/Desc %</th>
                <th style="width:10%">Total c/ Desc/Acres</th>
                <th style="width:8%">Lucratividade</th>
            </tr>
        </thead>
        @foreach ($vendas as $venda)
            <tbody>
                <tr class="venda">
                    <td style="text-align:center;">#{{ $venda->id }}</td>
                    <td>{{ $venda->cliente->nome ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y') }}</td>
                    <td>{{ $venda->funcionario->nome ?? '-' }}</td>
                    <td>{{ $venda->formaPgmto->nome ?? '-' }}</td>
                    <td>R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</td>
                    <td>R$ {{ $venda->valor_acres_desc }}</td>
                    <td>{{ $venda->percent_acres_desc ? $venda->percent_acres_desc . '%' : '-' }}</td>
                    <td>R$ {{ number_format($venda->valor_total_desconto, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($venda->valor_total_desconto - ($venda->total_custo_produtos ?? 0), 2, ',', '.') }}</td>
                </tr>
                <tr class="itens-cab">
                    <td></td>
                    <td colspan="4">Produto</td>
                    <td style="text-align:center;">Qtd</td>
                    <td colspan="2">Preço Unitário</td>
                    <td colspan="2">Subtotal</td>
                </tr>
                @foreach ($venda->itensVenda as $item)
                    <tr>
                        <td></td>
                        <td colspan="4">{{ $item->produto->nome ?? '-' }}</td>
                        <td style="text-align: center;">{{ $item->qtd }}</td>
                        <td colspan="2">R$ {{ number_format($item->valor_venda, 2, ',', '.') }}</td>
                        <td colspan="2">R$ {{ number_format($item->sub_total, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        @endforeach
    </table>
    <br>
    <h4>Somatórios</h4>
    <table width="100%" border="1" cellspacing="0" cellpadding="2" class="reduzida" style="font-size:1.1em;">
        <tr style="background-color:#f0f0f0;">
            <th>Custo Produtos</th>
            <th>Valor Total</th>
            <th>Valor Total Desc/Acres</th>
            <th>Lucratividade</th>
        </tr>
        <tr>
            <td style="font-weight:bold; font-size:1.2em;">R$ {{ number_format($somaCustoProdutos, 2, ',', '.') }}</td>
            <td style="font-weight:bold; font-size:1.2em;">R$ {{ number_format($somaValorTotal, 2, ',', '.') }}</td>
            <td style="font-weight:bold; font-size:1.2em;">R$ {{ number_format($somaValorTotalDesconto, 2, ',', '.') }}
            </td>
            <td style="font-weight:bold; font-size:1.2em;">R$ {{ number_format($somaLucro, 2, ',', '.') }}</td>
        </tr>
    </table>
@endsection