<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ProdutoResource;
use App\Models\Produto;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class EstoqueContabil extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    // protected static string $resource = ProdutoResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.estoque-contabil';

    protected static ?string $navigationGroup = 'Consultas';

    protected static ?string $navigationLabel = 'Estoque Financeiro';

    protected static ?string $title = 'Estoque Financeiro';

    protected static ?int $navigationSort = 16;

    public function mount()
    {

        $allEstoque = Produto::all();

        foreach ($allEstoque as $all) {
            $all->total_compra        = ($all->estoque * $all->valor_compra);
            $all->total_venda         = ($all->estoque * $all->valor_venda);
            $all->total_lucratividade = ($all->total_venda - $all->total_compra);
            $all->save();
        }
    }

    protected function getTableQuery(): Builder
    {
        return Produto::query()->where('tipo', 1);
    }

    protected function getTableColumns(): array
    {
        return [
                TextColumn::make('nome')
                    ->label('Produto')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('codbar')
                    ->label('Código de Barras')
                    ->sortable()
                    ->alignCenter()
                    ->searchable(),
                TextColumn::make('estoque')
                    ->alignCenter(),
                TextColumn::make('valor_compra')
                    ->money('BRL'),
                TextColumn::make('lucratividade')
                    ->alignCenter()
                    ->label('Lucratividade (%)'),
                TextColumn::make('valor_venda')
                    ->alignCenter()
                    ->money('BRL'),
                TextColumn::make('total_compra')
                    ->alignCenter()
                    ->getStateUsing(function (Produto $record): float {
                        return (($record->estoque * $record->valor_compra));
                    })
                    ->money('BRL')
                    ->summarize(Sum::make()->label('Total')->money('BRL'))
                    ->color('danger'),
                TextColumn::make('total_venda')
                    ->alignCenter()
                    ->getStateUsing(function (Produto $record): float {
                        return ($record->estoque * $record->valor_venda);
                    })
                    ->money('BRL')
                    ->summarize(Sum::make()->label('Total')->money('BRL'))
                    ->color('warning'),
                TextColumn::make('total_lucratividade')
                    ->alignCenter()
                    ->getStateUsing(function (Produto $record): float {
                        return ((($record->estoque * $record->valor_venda)) - (($record->estoque * $record->valor_compra)));
                    })
                    ->summarize(Sum::make()->label('Total')->money('BRL'))
                    ->color('success')
                    ->money('BRL'),

        ];
    }
}
