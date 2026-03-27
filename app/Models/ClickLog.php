<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClickLog extends Model
{
    protected $fillable = [
        'tipo',
        'comercio_id',
        'articulo_id',
        'ip_address',
        'user_agent',
        'city'
    ];

    public function comercio()
    {
        return $this->belongsTo(Comercio::class);
    }

    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }
}
