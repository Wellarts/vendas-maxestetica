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
            @php
                $somaEstoque = 0;
                $somaValorCompra = 0;
                $somaValorVenda = 0;
                $somaTotalCompra = 0;
                $somaTotalVenda = 0;
                $somaTotalLucratividade = 0;
            @endphp
            @foreach($produtos as $produto)
                @php
                    $somaEstoque += $produto->estoque;
                    $somaValorCompra += $produto->valor_compra;
                    $somaValorVenda += $produto->valor_venda;
                    $somaTotalCompra += ($produto->estoque * $produto->valor_compra);
                    $somaTotalVenda += ($produto->estoque * $produto->valor_venda);
                    $somaTotalLucratividade += (($produto->estoque * $produto->valor_venda) - ($produto->estoque * $produto->valor_compra));
                @endphp
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
                <td>{{ $somaEstoque }}</td>
                <td>R$ {{ number_format($somaValorCompra, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($somaValorVenda, 2, ',', '.') }}</td>
                <td>-</td>
                <td>R$ {{ number_format($somaTotalCompra, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($somaTotalVenda, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($somaTotalLucratividade, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
@endsection
