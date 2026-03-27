<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Articulo extends Model
{
    protected $fillable = [
        'comercio_id',
        'nombre_producto',
        'descripcion_articulo',
        'precio_ars',
        'categoria',
        'imagen_url',
        'activo',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'precio_ars' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function comercio(): BelongsTo
    {
        return $this->belongsTo(Comercio::class);
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function getWhatsappLinkAttribute(): string
    {
        $numero = $this->comercio->whatsapp;
        $nombreComercio = urlencode($this->comercio->nombre);
        $nombreProducto = urlencode($this->nombre_producto);

        return "https://wa.me/{$numero}?text=Hola%20{$nombreComercio},%20vi%20tu%20producto%20{$nombreProducto}%20en%20EstaAqui%20y%20me%20interesa.";
    }
}
