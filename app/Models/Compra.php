<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Compra extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [

            'fornecedor_id',
            'data_compra',
            'outros_custos',
            'valor_total',
            'obs',
            'status_caixa',
        ];

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function itensCompra()
    {
        return $this->hasMany(ItensCompra::class);
    }

    public function ProdutoFornecedor()
    {
        return $this->hasMany(ProdutoFornecedor::class);
    }

    public function contasPagar()
    {
        return $this->hasMany(contasPagar::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['*']);
        // Chain fluent methods for configuration options
    }
}
