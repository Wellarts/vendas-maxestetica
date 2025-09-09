@extends('layouts.pdf')

@section('content')
    <h2>Relatório Valor Vendido por Cliente</h2>
  
    <table width="100%" cellspacing="0" cellpadding="3" style="font-size:0.8em; border-collapse:collapse;">
        <thead>
            <tr style="background:#f0f0f0; border-bottom:1px solid #ccc;">
                <th>Cliente</th>
                <th>Valor Total</th>
                <th>Última Compra</th>
            </tr>
        </thead>
        <tbody>
            @foreach($registros as $registro)
                <tr>
                    <td>{{ $registro->cliente_nome }}</td>
                    <td>R$ {{ number_format($registro->valor_total_desconto, 2, ',', '.') }}</td>
                    <td>{{ $registro->ultima_compra ?? 'Nunca comprou' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight:bold; background:#f9f9f9; border-top:1px solid #ccc;">
                <td>Total Geral</td>
                <td colspan="2">R$ {{ number_format($somaTotal, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Gráfico removido, agora exibido como imagem gerada pelo QuickChart --}}
@endsection
