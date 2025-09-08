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
