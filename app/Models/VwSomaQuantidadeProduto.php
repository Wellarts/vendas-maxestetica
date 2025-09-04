<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VwSomaQuantidadeProduto extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'nome',
        'total_vendido_qtd',
        'total_vendido_valor',
        'total_vendido_custo',
        'total_vendido_lucro',
        'rentabilidade',
    ];
}
