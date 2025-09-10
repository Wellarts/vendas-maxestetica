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
    <h2>Relatório de Fluxo de Caixa</h2>
    <p>Data de emissão: {{ Carbon::now()->format('d/m/Y H:i') }}</p>
    @if(isset($filtros) && is_array($filtros) && collect($filtros)->filter()->count())
        <div style="margin-bottom: 18px; font-size: 1rem; color: #888; background: #f4f6f9; padding: 8px 12px; border-radius: 6px;">
            <strong>Filtros aplicados:</strong>
            @php $sep = false; @endphp
            @foreach($filtros as $chave => $valor)
                @if($valor)
                    @if($sep) | @endif
                    <span><b>{{ ucfirst(str_replace('_', ' ', $chave)) }}:</b>
                        @if(str_contains($chave, 'data') && $valor)
                            {{ \Carbon\Carbon::parse($valor)->format('d/m/Y') }}
                        @else
                            {{ is_array($valor) ? implode(', ', $valor) : $valor }}
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
                <th>Data/Hora</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Descrição</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalCredito = 0;
                $totalDebito = 0;
            @endphp
            @foreach($lancamentos as $lancamento)
            @php
                if($lancamento->tipo === 'CREDITO') {
                    $totalCredito += $lancamento->valor;
                } elseif($lancamento->tipo === 'DEBITO') {
                    $totalDebito += $lancamento->valor;
                }
            @endphp
            <tr>
                <td>{{ \Carbon\Carbon::parse($lancamento->created_at)->format('d/m/Y H:i') }}</td>
                <td>{{ $lancamento->tipo }}</td>
                <td>R$ {{ number_format($lancamento->valor, 2, ',', '.') }}</td>
                <td>{{ $lancamento->obs }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight:bold;background:#f2f2f2;">
                <td colspan="2">Total Crédito</td>
                <td colspan="2">R$ {{ number_format($totalCredito, 2, ',', '.') }}</td>
            </tr>
            <tr style="font-weight:bold;background:#f2f2f2;">
                <td colspan="2">Total Débito</td>
                <td colspan="2">R$ {{ number_format($totalDebito, 2, ',', '.') }}</td>
            </tr>
            <tr style="font-weight:bold;background:#e2e2e2;">
                <td colspan="2">Saldo Final</td>
                <td colspan="2">R$ {{ number_format($totalCredito + $totalDebito, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
@endsection
