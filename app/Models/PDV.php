<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PDV extends Model
{
    use HasFactory;

    protected $fillable = [
        'produto_id',
        'codbar',
        'venda_p_d_v_id',
        'valor_venda',
        'qtd',
        'acres_desc',
        'sub_total',
        'valor_custo_atual',
        'total_custo_atual',
    ];

    public function Produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function VendaPDV()
    {
        return $this->belongsTo(VendaPDV::class);
    }
}
