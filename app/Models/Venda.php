<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Venda extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'cliente_id',
        'funcionario_id',
        'data_venda',
        'forma_pgmto_id',
        'tipo_acres_desc',
        'valor_acres_desc',
        'percent_acres_desc',
        'valor_total_desconto',
        'valor_total',
        'lucro_venda',
        'obs',
        'status_caixa',

    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function formaPgmto()
    {
        return $this->belongsTo(FormaPgmto::class);
    }

    public function itensVenda()
    {
        return $this->hasMany(ItensVenda::class);
    }

    public function contasReceber()
    {
        return $this->hasMany(ContasReceber::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['*']);
        // Chain fluent methods for configuration options
    }

    public function pdv()
    {
        return $this->hasMany(PDV::class);
    }


}
