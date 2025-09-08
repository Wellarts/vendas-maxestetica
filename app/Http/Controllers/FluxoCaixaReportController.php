<?php

namespace App\Http\Controllers;

use App\Models\FluxoCaixa;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class FluxoCaixaReportController extends Controller
{
    public function pdf(Request $request)
    {
        $query = FluxoCaixa::query();

        if ($request->filled('data_de')) {
            $query->whereDate('created_at', '>=', $request->data_de);
        }
        if ($request->filled('data_ate')) {
            $query->whereDate('created_at', '<=', $request->data_ate);
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        
        $lancamentos = $query->orderBy('created_at')->get();

        $pdf = Pdf::loadView('relatorios.fluxo-caixa-pdf', [
            'lancamentos' => $lancamentos,
            'filtros' => $request->all(),
        ])->setPaper('a4', 'landscape');
        return $pdf->stream('fluxo-caixa.pdf');
    }
}
