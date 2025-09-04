<?php

namespace App\Filament\Resources\VendaResource\Pages;

use App\Filament\Resources\VendaResource;
use App\Livewire\TotalVendaStatsOverview;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditVenda extends EditRecord
{
    protected static string $resource = VendaResource::class;

    protected $listeners = ['atualizarValorTotal' => 'atualizarValorTotal'];

    public function atualizarValorTotal()
    {
        $venda = $this->record;
        if ($venda) {
            $valorTotal         = $venda->itensVenda()->sum('sub_total');
            $venda->valor_total = $valorTotal;
            $venda->save();
            $this->form->fill(['valor_total' => $valorTotal]);
        }
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
               ->before(function ($record, $action) {
                   if ($record->itensVenda()->exists()) {
                       Notification::make()
                           ->title('Atenção')
                           ->body('Não é possível excluir a venda porque ela possui itens.')
                           ->danger()
                           // ->icon('heroicon-o-x-circle')
                           ->iconColor('danger')
                           ->duration(5000)
                           ->persistent()
                           ->send();

                       return $action->cancel();
                   }
               }),

        ];
    }

    protected function getFooterActions(): array
    {
        return [

            Actions\Action::make('recarregar_valores')
                ->label('Recarregar Valores')
                // ->icon('heroicon-o-refresh')
                ->action(function () {
                    $this->form->fill([
                        'valor_total' => $this->record->valor_total,
                    ]);
                    Notification::make()
                        ->title('Valores recarregados')
                        ->success()
                        ->duration(3000)
                        ->send();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {

        return [
           TotalVendaStatsOverview::class,

        ];
    }
}
