<?php
use App\Http\Controllers\VwTotalVendasPorClienteReportController;
use App\Http\Controllers\ContasPagarReportController;
use App\Filament\Pages\EstoqueContabil;
use App\Http\Controllers\ComprovantesController;
use App\Http\Controllers\ControllerNovaParcela;
use App\Http\Controllers\ControllerNovaParcelaPagar;
use App\Http\Controllers\VendaPDVReportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LucratividadePDVPdfController;
use App\Http\Controllers\ContasReceberReportController;
use App\Http\Controllers\FluxoCaixaReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', function () { return redirect('/admin'); })->name('login');

Route::get('pdf/{id}', [ComprovantesController::class, 'geraPdf'])->name('comprovanteNormal');
Route::get('pdfPdv/{id}', [ComprovantesController::class, 'geraPdfPDV'])->name('comprovantePDV');
Route::get('comprovante-pdv-imagem/{id}', [ComprovantesController::class, 'geraImagemPDV'])->name('comprovantePDVImagem');
Route::get('novaParcela/{id}', [ControllerNovaParcela::class, 'novaParcela'])->name('novaParcela');
Route::get('novaParcelaPagar/{id}', [ControllerNovaParcelaPagar::class, 'novaParcelaPagar'])->name('novaParcelaPagar');
Route::get('/relatorio-vendas-pdf', [VendaPDVReportController::class, 'vendasPdf'])->name('relatorio.vendas.pdf');
Route::get('/relatorio-venda-produtos', [VendaPDVReportController::class, 'vendasPorProdutoPdf'])->name('relatorio.venda.produtos');
Route::get('/relatorio-estoque-contabil', [EstoqueContabil::class, 'exportarPdf'])->name('relatorio.estoque.contabil');
Route::get('/relatorio-lucratividade-pdv', [LucratividadePDVPdfController::class, 'gerarRelatorio'])->name('relatorio.lucratividade.pdv');
Route::get('/relatorio-vendas-por-cliente', [VwTotalVendasPorClienteReportController::class, 'gerarRelatorio'])->name('relatorio.vendas.por.cliente');
Route::get('/relatorio-contas-pagar-pdf', [ContasPagarReportController::class, 'pdf'])->name('relatorio.contas.pagar.pdf'); 
Route::get('/relatorio-contas-receber-pdf', [ContasReceberReportController::class, 'pdf'])->name('relatorio.contas.receber.pdf');
Route::get('/relatorio-fluxo-caixa-pdf', [FluxoCaixaReportController::class, 'pdf'])->name('relatorio.fluxo.caixa.pdf');