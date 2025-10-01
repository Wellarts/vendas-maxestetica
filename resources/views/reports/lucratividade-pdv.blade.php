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

        fieldset.venda {
            border: 1px solid #bbb;
            margin-bottom: 10px;
            padding: 6px 10px 8px 10px;
        }

        legend.venda {
            font-size: 1em;
            font-weight: bold;
            padding: 0 8px;
        }
    </style>
    @foreach ($vendas as $venda)
        <fieldset class="venda">
            <legend class="venda">Venda #{{ $venda->id }} - {{ $venda->cliente->nome ?? '-' }}</legend>
            <table width="100%" border="1" cellspacing="0" cellpadding="0" class="reduzida">
                <tr>
                    <th style="width:16%">Cliente</th>
                    <th style="width:10%">Data</th>
                    <th style="width:16%">Vendedor</th>
                    <th style="width:14%">Forma Pgto</th>
                    <th style="width:10%">Valor Total</th>
                    <th style="width:10%">Asc/Desc Valor</th>
                    <th style="width:8%">Asc/Desc % </th>
                    <th style="width:8%">Total</th>
                    <th style="width:12%">Total c/ Desc/Acres</th>
                    <th style="width:12%">Lucratividade</th>
                </tr>
                <tr>
                    <td>{{ $venda->cliente->nome ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y') }}</td>
                    <td>{{ $venda->funcionario->nome ?? '-' }}</td>
                    <td>{{ $venda->formaPgmto->nome ?? '-' }}</td>
                    <td>R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</td>
                    <td>R$ {{$venda->valor_acres_desc }}</td>
                    <td>{{ $venda->percent_acres_desc ? $venda->percent_acres_desc . '%' : '-' }}
                    </td>
                    <td>R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($venda->valor_total_desconto, 2, ',', '.') }}</td>
                    <td>
                        @php
                            $custoProdutos = $venda->itensVenda->sum(function ($item) {
                                return $item->valor_custo_atual * $item->qtd;
                            });
                            $lucroVenda = $venda->valor_total_desconto - $custoProdutos;
                        @endphp
                        R$ {{ number_format($lucroVenda, 2, ',', '.') }}
                    </td>
                </tr>
            </table>
            <div style="margin-top:2px;">
                <b>Produtos da Venda:</b>
                <table width="100%" border="1" cellspacing="0" cellpadding="0" class="reduzida">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Qtd</th>
                            <th>Preço Unitário</th>
                            <th>Subtotal</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($venda->itensVenda as $item)
                            <tr>
                                <td>{{ $item->produto->nome ?? '-' }}</td>
                                <td style="text-align: center;">{{ $item->qtd }}</td>
                                <td>R$ {{ number_format($item->valor_venda, 2, ',', '.') }}</td>
                                <td>R$ {{ number_format($item->sub_total, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </fieldset>
    @endforeach
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
