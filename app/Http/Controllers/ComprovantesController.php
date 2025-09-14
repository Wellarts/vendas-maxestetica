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

    public function geraImagemPDV($id)
    {
        $vendas = \App\Models\VendaPDV::find($id);
        // Gera o PDF temporário
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfPdv.venda', compact('vendas'));
        $output = $pdf->output();

        // Salva PDF temporário
        $tempPdf = tempnam(sys_get_temp_dir(), 'pdv_') . '.pdf';
        file_put_contents($tempPdf, $output);

        // Usa Imagick para converter PDF em imagem (PNG)
        $imagick = new \Imagick();
        $imagick->setResolution(200, 200);
        $imagick->readImage($tempPdf.'[0]'); // primeira página
        $imagick->setImageFormat('png');
        $imageData = $imagick->getImageBlob();
        $imagick->clear();
        $imagick->destroy();
        unlink($tempPdf);

        return response($imageData)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'inline; filename="comprovante.png"');
    }
}
