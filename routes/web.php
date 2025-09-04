<?php

use App\Http\Controllers\ComprovantesController;
use App\Http\Controllers\ControllerNovaParcela;
use App\Http\Controllers\ControllerNovaParcelaPagar;
use App\Http\Controllers\VendaPDVReportController;
use Illuminate\Support\Facades\Route;

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
Route::get('novaParcela/{id}', [ControllerNovaParcela::class, 'novaParcela'])->name('novaParcela');
Route::get('novaParcelaPagar/{id}', [ControllerNovaParcelaPagar::class, 'novaParcelaPagar'])->name('novaParcelaPagar');

Route::get('/relatorio-vendas-pdf', [VendaPDVReportController::class, 'vendasPdf'])->name('relatorio.vendas.pdf');

Route::get('/relatorio-venda-produtos', [VendaPDVReportController::class, 'vendasPorProdutoPdf'])->name('relatorio.venda.produtos');
