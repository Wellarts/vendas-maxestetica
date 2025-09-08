<?php

namespace App\Filament\Resources\VwTotalVendasPorClienteResource\Pages;

use App\Filament\Resources\VwTotalVendasPorClienteResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageVwTotalVendasPorClientes extends ManageRecords
{
    protected static string $resource = VwTotalVendasPorClienteResource::class;

    protected static ?string $title = 'Vendas por Cliente';

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
