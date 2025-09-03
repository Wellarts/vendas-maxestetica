<?php

namespace App\Filament\Resources\VendaPDVResource\Pages;

use App\Filament\Resources\VendaPDVResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateVendaPDV extends CreateRecord
{
    protected static string $resource = VendaPDVResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Se for orçamento, zera campos financeiros e impede movimentação
        if (($data['tipo_registro'] ?? null) === 'orcamento') {
            $data['forma_pgmto_id'] = null;
            $data['data_venda'] = null;
            $data['valor_total'] = 0;
            $data['lucro_venda'] = 0;
            // Aqui você pode adicionar outros campos que não devem ser preenchidos em orçamento
        }
        return $data;
    }
}
