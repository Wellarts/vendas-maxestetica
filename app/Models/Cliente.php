<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Cliente extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'nome',
        'cpf_cnpj',
        'endereco',
        'estado_id',
        'cidade_id',
        'telefone',
        'email',
        'profissao',
        'numero_conselho',
    ];

    public function Estado()
    {
        return $this->belongsTo(Estado::class);
    }

    public function Cidade()
    {
        return $this->belongsTo(Cidade::class);
    }
    public function venda()
    {
        return $this->hasMany(Venda::class);
    }

    public function vendasPdv()
    {
        return $this->hasMany(VendaPDV::class);
    }

    public function contasReceber()
    {
        return $this->belongsTo(ContasReceber::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['*']);
        // Chain fluent methods for configuration options
    }
}
