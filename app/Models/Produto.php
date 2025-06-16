<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Produto extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'nome',
        'codbar',
        'estoque',
        'valor_compra',
        'lucratividade',
        'valor_venda',
        'total_compra',
        'total_venda',
        'total_lucratividade',
        'foto',
        'tipo',
    ];

    protected $casts = [
        'foto' => 'array',
    ];

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::deleting(function ($produto) {
    //       //  dd($produto->itensVenda()->count() > 0 ,' - ', $produto->pdv()->count() > 0);
    //         if ($produto->itensVenda()->count() > 0 || $produto->pdv()->count() > 0) {
    //             // Verifica se o produto está vinculado a vendas ou PDV
    //             // Se estiver, não permite a exclusão e lança uma exceção
    //             // Lança uma exceção que será capturada pelo Filament
    //             throw new \Exception('Este produto não pode ser excluído porque está vinculado a uma ou mais vendas.');
    //         }
    //     });
    // }

    public function ProdutoFornecedor()
    {
        return $this->hasMany(ProdutoFornecedor::class);
    }

    public function itensCompra()
    {
        return $this->hasMany(ItensCompra::class);
    }

    public function pdv()
    {
        return $this->hasMany(PDV::class);
    }

    public function itensVenda()
    {
        return $this->hasMany(ItensVenda::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*']);
        // Chain fluent methods for configuration options
    }
}
