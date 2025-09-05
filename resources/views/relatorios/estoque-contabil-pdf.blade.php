@php
    use Illuminate\Support\Carbon;
@endphp

@extends('layouts.pdf')

@section('content')
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: center; }
        th { background: #f2f2f2; }
        h2 { text-align: center; }
        tbody tr:nth-child(even) { background: #f7f7f7; }
        tbody tr:nth-child(odd) { background: #fff; }
    </style>
    <h2>Relatório de Estoque Contábil</h2>
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
            <tr style="font-weight:bold;background:#f9f9f9;">
                <td colspan="2">Totais</td>
                <td>{{ $totais->somaEstoque ?? 0 }}</td>
                <td>R$ {{ number_format($totais->somaValorCompra ?? 0, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($totais->somaValorVenda ?? 0, 2, ',', '.') }}</td>
                <td>-</td>
                <td>R$ {{ number_format($totais->somaTotalCompra ?? 0, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($totais->somaTotalVenda ?? 0, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($totais->somaTotalLucratividade ?? 0, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
@endsection
