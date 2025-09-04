<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    use HasFactory;

    protected $fillable = [
        'uf',
        'nome',

    ];


    public function cidade()
    {

        return $this->hasMany(Cidade::class);

    }


    public function cliente()
    {
        return $this->hasMany(Cliente::class);
    }

    public function fornecedor()
    {
        return $this->hasMany(Fornecedor::class);
    }

    public function funcionario()
    {
        return $this->hasMany(Funcionario::class);
    }


}
