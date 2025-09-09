@php
    use Illuminate\Support\Carbon;
@endphp

@extends('layouts.pdf')

@section('content')
   
    <h2>Relatório de Estoque Financeiro</h2>
    <p>Data de emissão: {{ Carbon::now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Código de Barras</th>
                <th>Estoque</th>
                <th>Valor Compra</th>
                <th>Valor Venda</th>
                <th>Lucratividade (%)</th>
                <th>Total Compra</th>
                <th>Total Venda</th>
                <th>Total Lucratividade</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produtos as $produto)
            <tr>
                <td>{{ $produto->nome }}</td>
                <td>{{ $produto->codbar }}</td>
                <td>{{ $produto->estoque }}</td>
                <td>R$ {{ number_format($produto->valor_compra, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($produto->valor_venda, 2, ',', '.') }}</td>
                <td>{{ $produto->lucratividade }}</td>
                <td>R$ {{ number_format($produto->estoque * $produto->valor_compra, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($produto->estoque * $produto->valor_venda, 2, ',', '.') }}</td>
                <td>R$ {{ number_format(($produto->estoque * $produto->valor_venda) - ($produto->estoque * $produto->valor_compra), 2, ',', '.') }}</td>
            </tr>
            @endforeach
            </tbody>
            </table>

            <table style="margin-top: 30px;">
            <thead>
                <tr style="font-weight:bold;background:#f2f2f2;">
                <th colspan="2">Totais</th>
                <th>Estoque Total</th>
                
                <th>Total Compra</th>
                <th>Total Venda</th>
                <th>Total Lucratividade</th>
                </tr>
            </thead>
            <tbody>
                <tr style="font-weight:bold;background:#f9f9f9;">
                <td colspan="2">Totais</td>
                <td>{{ $totais->somaEstoque ?? 0 }}</td>
                
                <td>R$ {{ number_format($totais->somaTotalCompra ?? 0, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($totais->somaTotalVenda ?? 0, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($totais->somaTotalLucratividade ?? 0, 2, ',', '.') }}</td>
                </tr>
            </tbody>
            </table>
        </tbody>
    </table>
@endsection
