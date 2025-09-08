@extends('layouts.pdf')

@section('content')
    <h2>Relatório Valor Vendido por Cliente</h2>
  
    <table width="100%" border="1" cellspacing="0" cellpadding="3" style="font-size:1em;">
        <thead>
            <tr style="background:#f0f0f0;">
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
            <tr style="font-weight:bold; background:#f9f9f9;">
                <td>Total Geral</td>
                <td colspan="2">R$ {{ number_format($somaTotal, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Gráfico removido, agora exibido como imagem gerada pelo QuickChart --}}
@endsection
