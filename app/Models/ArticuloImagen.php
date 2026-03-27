<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticuloImagen extends Model
{
    protected $table = 'articulo_imagenes';

    protected $fillable = [
        'articulo_id',
        'url',
        'orden',
    ];

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(Articulo::class);
    }
}
