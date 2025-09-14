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
        $html = view('pdfPdv.venda', compact('vendas'))->render();

        // Define o caminho público para salvar a imagem
        $fileName = 'comprovante_' . $id . '_' . time() . '.png';
        $publicPath = public_path('comprovantes');
        if (!file_exists($publicPath)) {
            mkdir($publicPath, 0777, true);
        }
        $imagePath = $publicPath . DIRECTORY_SEPARATOR . $fileName;

        \Spatie\Browsershot\Browsershot::html($html)
            ->setScreenshotType('png')
            ->windowSize(900, 1200)
            ->save($imagePath);

        $url = asset('comprovantes/' . $fileName);
        // Redireciona para a URL da imagem ou retorna a URL
        return redirect($url);
    }
}
