<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VwTotalVendasPorCliente extends Model
{
    use HasFactory;


    protected $fillable = [
        'id',
        'cliente_nome',
        'valor_total_desconto',
        'ultima_venda',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_nome', 'nome');
    }
}
