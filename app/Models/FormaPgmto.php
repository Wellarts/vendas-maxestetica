<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class FormaPgmto extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'nome',
    ];

    public function venda()
    {
        return $this->hasMany(Venda::class);
    }

    public function vendasPdv()
    {
        return $this->hasMany(VendaPDV::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['*']);
        // Chain fluent methods for configuration options
    }
}
