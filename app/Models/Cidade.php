<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cidade extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'estado_id',

    ];

    public function estado()
    {

        return $this->belongsTo(Estado::class);

    }

    public function cliente()
    {
        return $this->hasMany(Cliente::class);
    }

    public function fornecedor()
    {
        return $this->hasMany(Fornecedor::class);
    }
}
