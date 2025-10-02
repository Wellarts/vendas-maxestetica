<?php

namespace App\Filament\Pages;

use App\Models\PDV;
use App\Models\VendaPDV;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Support\Facades\FilamentIcon;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Support\Facades\Route;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\ContasPagar;
use App\Models\ContasReceber;
use Carbon\Carbon;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $routePath = '/';

    protected static ?int $navigationSort = -2;

    /**
     * @var view-string
     */
    protected static string $view = 'filament-panels::pages.dashboard';

    //teste


    public function mount(): void
    {

        // Notification::make()
        //     ->title('ATENÇÃO')
        //     ->persistent()
        //     ->danger()
        //     ->body('Sua mensalidade está atrasada, regularize sua assinatura para evitar o bloqueio do sistema.
        //     PIX: 28708223831')
        //     ->actions([
        //         Action::make('Entendi')
        //             ->button()
        //             ->close(),
        //     ])
        //     ->send();


        // Otimização: buscar IDs em lote e deletar apenas se necessário
        // $vendaPDVIds = VendaPDV::pluck('id');
        // if ($vendaPDVIds->isNotEmpty()) {
        //     PDV::whereNotIn('venda_p_d_v_id', $vendaPDVIds)->delete();
        // }

        //***********NOTIFICAÇÃO DE CONTAS A RECEBER*************

        // Otimização: eager loading e cálculo de data fora do loop
        $contasReceberVencer = ContasReceber::where('status', '=', '0')->with('cliente')->get();
        $hoje = Carbon::today();
        foreach ($contasReceberVencer as $cr) {
            $dataVencimento = Carbon::parse($cr->data_vencimento);
            $qtd_dias = $hoje->diffInDays($dataVencimento, false);
            $clienteNome = $cr->cliente->nome ?? 'Desconhecido';
            $valorParcela = number_format($cr->valor_parcela, 2, ',', '.');
            $dataFormatada = $dataVencimento->format('d/m/Y');
            if ($qtd_dias <= 3 && $qtd_dias > 0) {
                Notification::make()
                    ->title('ATENÇÃO: Conta a receber com vencimento próximo.')
                    ->body("Do cliente <b>{$clienteNome}</b> no valor de R$ <b>{$valorParcela}</b> com vencimento em <b>{$dataFormatada}</b>.")
                    ->success()
                    ->persistent()
                    ->send();
            } elseif ($qtd_dias == 0) {
                Notification::make()
                    ->title('ATENÇÃO: Conta a receber com vencimento para hoje.')
                    ->body("Do cliente <b>{$clienteNome}</b> no valor de R$ <b>{$valorParcela}</b> com vencimento em <b>{$dataFormatada}</b>.")
                    ->warning()
                    ->persistent()
                    ->send();
            } elseif ($qtd_dias < 0) {
                Notification::make()
                    ->title('ATENÇÃO: Conta a receber vencida.')
                    ->body("Do cliente <b>{$clienteNome}</b> no valor de R$ <b>{$valorParcela}</b> com vencimento em <b>{$dataFormatada}</b>.")
                    ->danger()
                    ->persistent()
                    ->send();
            }
        }

        //***********NOTIFICAÇÃO DE CONTAS A PAGAR*************
        $contasPagarVencer = ContasPagar::where('status', '=', '0')->with('fornecedor')->get();
        $hoje = Carbon::today();
        foreach ($contasPagarVencer as $cp) {
            $dataVencimento = Carbon::parse($cp->data_vencimento);
            $qtd_dias = $hoje->diffInDays($dataVencimento, false);
            $fornecedorNome = $cp->fornecedor->nome ?? 'Desconhecido';
            $valorParcela = number_format($cp->valor_parcela, 2, ',', '.');
            $dataFormatada = $dataVencimento->format('d/m/Y');
            if ($qtd_dias <= 3 && $qtd_dias > 0) {
                Notification::make()
                    ->title('ATENÇÃO: Conta a pagar com vencimento próximo.')
                    ->body("Do fornecedor <b>{$fornecedorNome}</b> no valor de R$ <b>{$valorParcela}</b> com vencimento em <b>{$dataFormatada}</b>.")
                    ->success()
                    ->persistent()
                    ->send();
            } elseif ($qtd_dias == 0) {
                Notification::make()
                    ->title('ATENÇÃO: Conta a pagar com vencimento para hoje.')
                    ->body("Do fornecedor <b>{$fornecedorNome}</b> no valor de R$ <b>{$valorParcela}</b> com vencimento em <b>{$dataFormatada}</b>.")
                    ->warning()
                    ->persistent()
                    ->send();
            } elseif ($qtd_dias < 0) {
                Notification::make()
                    ->title('ATENÇÃO: Conta a pagar vencida.')
                    ->body("Do fornecedor <b>{$fornecedorNome}</b> no valor de R$ <b>{$valorParcela}</b> com vencimento em <b>{$dataFormatada}</b>.")
                    ->danger()
                    ->persistent()
                    ->send();
            }
        }
    }

    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ?? static::$title ?? __('filament-panels::pages/dashboard.title');
    }

    public static function getNavigationIcon(): ?string
    {
        return static::$navigationIcon
            ?? FilamentIcon::resolve('panels::pages.dashboard.navigation-item')
            ?? (Filament::hasTopNavigation() ? 'heroicon-m-home' : 'heroicon-o-home');
    }

    public static function routes(Panel $panel): void
    {
        Route::get(static::getRoutePath(), static::class)
            ->middleware(static::getRouteMiddleware($panel))
            ->withoutMiddleware(static::getWithoutRouteMiddleware($panel))
            ->name(static::getSlug());
    }

    public static function getRoutePath(): string
    {
        return static::$routePath;
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return Filament::getWidgets();
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getVisibleWidgets(): array
    {
        return $this->filterVisibleWidgets($this->getWidgets());
    }

    /**
     * @return int | string | array<string, int | string | null>
     */
    public function getColumns(): int | string | array
    {
        return 2;
    }

    public function getTitle(): string | Htmlable
    {
        return static::$title ?? __('filament-panels::pages/dashboard.title');
    }
}
