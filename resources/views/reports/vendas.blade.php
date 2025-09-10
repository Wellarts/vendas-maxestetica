@extends('layouts.pdf')

@section('content')
    <h2>Relatório de Vendas de Produtos</h2>

    @if(isset($produtoId) || isset($dataInicial) || isset($dataFinal))
    <div style="margin-bottom: 18px; font-size: 1rem; color: #888; background: #f4f6f9; padding: 8px 12px; border-radius: 6px;">
            <strong>Filtros aplicados:</strong>
            @if(isset($produtoId) && $produtoId)
                <span><b>Produto ID:</b> {{ $produtoId }}</span>@if(isset($dataInicial) && $dataInicial || isset($dataFinal) && $dataFinal) | @endif
            @endif
            @if(isset($dataInicial) && $dataInicial)
                <span><b>Data Inicial:</b> {{ \Carbon\Carbon::parse($dataInicial)->format('d/m/Y') }}</span>@if(isset($dataFinal) && $dataFinal) | @endif
            @endif
            @if(isset($dataFinal) && $dataFinal)
                <span><b>Data Final:</b> {{ \Carbon\Carbon::parse($dataFinal)->format('d/m/Y') }}</span>
            @endif
        </div>
    @endif
    <table width="100%" border="0" cellspacing="0" cellpadding="5">
        <thead>
            <tr>
                <th>Venda</th>
                <th>Data</th>
                <th>Cliente</th>
                <th>Vendedor</th>
                <th>Produto</th>
                <th>Qtd</th>
                <th>Preço Unitário</th>
              
            </tr>
        </thead>
        <tbody>
            @foreach($vendas as $venda)
                <tr>
                    <td>{{ $venda->venda_id }}</td>
                    <td>{{ \Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y') }}</td>
                    <td>{{ $venda->cliente_nome }}</td>
                    <td>{{ $venda->funcionario_nome }}</td>
                    <td>{{ $venda->produto_nome }}</td>
                    <td>{{ $venda->quantidade }}</td>
                    <td>R$ {{ number_format($venda->preco_unitario, 2, ',', '.') }}</td>
                   
                </tr>
            @endforeach
        </tbody>
    </table>

@php
    $totalProdutos = $vendas->count();
    $totalFuncionarios = $vendas->pluck('funcionario_nome')->unique()->count();
    $totalClientes = $vendas->pluck('cliente_nome')->unique()->count();
@endphp

<div style="margin-top: 30px;">
    <table style="width:100%; border-collapse: collapse; font-size: 1.1rem; color: #676769; font-weight: 500;">
        <thead>
            <tr style="background: #f4f6f9;">
                <th style="padding: 8px; border: 1px solid #e5e7eb;">Indicador</th>
                <th style="padding: 8px; border: 1px solid #e5e7eb;">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">Total de Produtos Vendidos</td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">{{ $totalProdutos }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">Total de Funcionários Envolvidos</td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">{{ $totalFuncionarios }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">Total de Clientes Atendidos</td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">{{ $totalClientes }}</td>
            </tr>
        </tbody>
    </table>
</div>

<div style="margin-top: 20px; font-size: 1rem; color: #222;">
    <strong>Valores vendidos por produto:</strong>
    <table style="width: 100%; border-collapse: collapse; margin-top: 8px;">
        <thead>
            <tr style="background: #f4f6f9;">
                <th style="padding: 8px; border: 1px solid #e5e7eb;">Produto</th>
                <th style="padding: 8px; border: 1px solid #e5e7eb;">Quantidade Vendida</th>
                <th style="padding: 8px; border: 1px solid #e5e7eb;">Valor Total Vendido (R$)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vendas->groupBy('produto_nome') as $produto => $items)
                <tr>
                    <td style="padding: 8px; border: 1px solid #e5e7eb;">{{ $produto }}</td>
                    <td style="padding: 8px; border: 1px solid #e5e7eb;">{{ $items->sum('quantidade') }}</td>
                    <td style="padding: 8px; border: 1px solid #e5e7eb;">
                        {{ number_format($items->sum(function($item){ return $item->preco_unitario * $item->quantidade; }), 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <strong style="margin-top: 18px; display: block;">Valores vendidos por cliente:</strong>
    <table style="width: 100%; border-collapse: collapse; margin-top: 8px;">
        <thead>
            <tr style="background: #f4f6f9;">
                <th style="padding: 8px; border: 1px solid #e5e7eb;">Cliente</th>
                <th style="padding: 8px; border: 1px solid #e5e7eb;">Quantidade Comprada</th>
                <th style="padding: 8px; border: 1px solid #e5e7eb;">Valor Total Vendido (R$)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vendas->groupBy('cliente_nome') as $cliente => $items)
                <tr>
                    <td style="padding: 8px; border: 1px solid #e5e7eb;">{{ $cliente }}</td>
                    <td style="padding: 8px; border: 1px solid #e5e7eb;">{{ $items->sum('quantidade') }}</td>
                    <td style="padding: 8px; border: 1px solid #e5e7eb;">
                        {{ number_format($items->sum(function($item){ return $item->preco_unitario * $item->quantidade; }), 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
