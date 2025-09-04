<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutoFornecedor extends Model
{
    use HasFactory;

    protected $fillable = [
        'compra_id',
        'produto_id',
        'qtd',
        'valor',
    ];

    public function produto()
    {

        return $this->belongsTo(Produto::class);
    }

    public function compra()
    {

        return $this->belongsTo(Compra::class);
    }
}
