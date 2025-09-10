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
    <h2>Relatório de Contas a Pagar</h2>
    <p>Data de emissão: {{ Carbon::now()->format('d/m/Y H:i') }}</p>
    @if(isset($filtrosNomes) && collect($filtrosNomes)->filter()->count())
        <div style="margin-bottom: 18px; font-size: 1rem; color: #888; background: #f4f6f9; padding: 8px 12px; border-radius: 6px;">
            <strong>Filtros aplicados:</strong>
            @php $sep = false; @endphp
            @foreach($filtrosNomes as $chave => $valor)
                @if($valor)
                    @if($sep) | @endif
                    <span><b>{{ $chave }}:</b>
                        @if(str_contains($chave, 'Data') && $valor)
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
    <table>
        <thead>
            <tr>
                <th>Fornecedor</th>
                <th>Parcela Nº</th>
                <th>Vencimento</th>
                <th>Valor Parcela</th>
                <th>Data Pagamento</th>
                <th>Valor Pago</th>
                <th>Status</th>
                <th>Observação</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalParcela = 0;
                $totalPago = 0;
            @endphp
            @foreach($contas as $conta)
            @php
                $totalParcela += $conta->valor_parcela;
                $totalPago += $conta->valor_pago;
            @endphp
            <tr>
                <td>{{ $conta->fornecedor->nome ?? '-' }}</td>
                <td>{{ $conta->ordem_parcela }}</td>
                <td>{{ \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y') }}</td>
                <td>R$ {{ number_format($conta->valor_parcela, 2, ',', '.') }}</td>
                <td>{{ $conta->data_pagamento ? \Carbon\Carbon::parse($conta->data_pagamento)->format('d/m/Y') : '-' }}</td>
                <td>{{ $conta->valor_pago ? 'R$ ' . number_format($conta->valor_pago, 2, ',', '.') : '-' }}</td>
                <td>{{ $conta->status ? 'Pago' : 'Em aberto' }}</td>
                <td>{{ $conta->obs }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight:bold;background:#f2f2f2;">
                <td colspan="3">Totais</td>
                <td>R$ {{ number_format($totalParcela, 2, ',', '.') }}</td>
                <td></td>
                <td>R$ {{ number_format($totalPago, 2, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
@endsection
