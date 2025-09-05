@extends('layouts.pdf')

@section('content')
    <h2>Relatório de Lucratividade PDV</h2>
    <table width="100%" border="1" cellspacing="0" cellpadding="5">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Data</th>
                <th>Funcionário</th>
                <th>Valor Total</th>
                <th>Desconto Valor</th>
                <th>% Desconto</th>
                <th>Valor Total</th>
                <th>Valor Total com Desconto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vendas as $venda)
                <tr>
                    <td>{{ $venda->id }}</td>
                    <td>{{ $venda->cliente->nome ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($venda->data_venda)->format('d/m/Y') }}</td>
                    <td>{{ $venda->funcionario->nome ?? '-' }}</td>
                    <td>R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($venda->valor_acres_desc, 2, ',', '.') }}</td>
                    <td>{{ $venda->percent_acres_desc ? number_format($venda->percent_acres_desc, 2, ',', '.') . '%' : '-' }}</td>
                    <td>R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($venda->valor_total_desconto, 2, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="9">
                        <b>Produtos da Venda:</b>
                        <table width="100%" border="1" cellspacing="0" cellpadding="3" style="margin-top: 5px;">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Qtd</th>
                                    <th>Preço Unitário</th>
                                    <th>Subtotal</th>
                                    <th>Custo Atual</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($venda->itensVenda as $item)
                                    <tr>
                                        <td>{{ $item->produto->nome ?? '-' }}</td>
                                        <td>{{ $item->qtd }}</td>
                                        <td>R$ {{ number_format($item->valor_venda, 2, ',', '.') }}</td>
                                        <td>R$ {{ number_format($item->sub_total, 2, ',', '.') }}</td>
                                        <td>R$ {{ number_format($item->total_custo_atual, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <br>
    <h4>Somatórios</h4>
    <table width="100%" border="1" cellspacing="0" cellpadding="5">
        <tr>
            <th>Custo Produtos</th>
            <th>Valor Total</th>
            <th>Valor Total Desconto</th>
            <th>Lucratividade</th>
        </tr>
        <tr>
            <td>R$ {{ number_format($somaCustoProdutos, 2, ',', '.') }}</td>
            <td>R$ {{ number_format($somaValorTotal, 2, ',', '.') }}</td>
            <td>R$ {{ number_format($somaValorTotalDesconto, 2, ',', '.') }}</td>
            <td>R$ {{ number_format($somaLucro, 2, ',', '.') }}</td>
        </tr>
    </table>
@endsection
