<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\VendaPDV;
use Barryvdh\DomPDF\Facade\Pdf;

class ComprovantesController extends Controller
{
    public function geraPdf($id)
    {

        $vendas = Venda::find($id);
        //  $registros = Venda::with('categoria')->get();
        //   dd($vendas->formaPgmto);



        //  return pdf::loadView('pdf.venda', compact(['vendas']))->stream();

        return Pdf::loadView('pdf.venda', compact('vendas'))->download('comprovante.pdf');
    }

    public function geraPdfPDV($id)
    {

        $vendas = VendaPDV::find($id);
        //  $registros = Venda::with('categoria')->get();
        //   dd($vendas->formaPgmto);



        //  return pdf::loadView('pdf.venda', compact(['vendas']))->stream();

        return Pdf::loadView('pdfPdv.venda', compact('vendas'))->download('comprovante.pdf');
    }
}
