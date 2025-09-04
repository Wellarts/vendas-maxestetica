<?php

namespace App\Filament\Resources\CompraResource\Pages;

use App\Filament\Resources\CompraResource;
use App\Livewire\TotalCompraStatsOverview;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompra extends EditRecord
{
    protected static string $resource = CompraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->disabled(fn ($record) => $record->itensCompra()->exists())
                ->after(function ($record) {
                    // Excluir do fluxo de caixa, se existir
                    if (\App\Models\FluxoCaixa::where('id_lancamento', $record->id)->exists()) {
                        \App\Models\FluxoCaixa::where('id_lancamento', $record->id)->delete();
                    }

                    // Excluir parcelas a receber desta compra, se existir
                    if (\App\Models\ContasPagar::where('compra_id', $record->id)->exists()) {
                        \App\Models\ContasPagar::where('compra_id', $record->id)->delete();
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Lançamento removido do fluxo de caixa!')
                        ->body('O lançamento referente à compra nº ' . $record->id . ' foi removido do fluxo de caixa e parcelas a receber foram excluídas.')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {

        return [
           TotalCompraStatsOverview::class,

        ];
    }
}
