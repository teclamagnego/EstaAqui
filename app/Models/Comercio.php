<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Comercio extends Authenticatable
{
    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'logo_url',
        'whatsapp',
        'zona_barrio',
        'direccion',
        'categoria_comercio',
        'email',
        'password',
        'imagen_url',
        'activo',
        'orden',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Comercio $comercio) {
            if (empty($comercio->slug)) {
                $comercio->slug = Str::slug($comercio->nombre);
            }
        });
    }

    public function articulos(): HasMany
    {
        return $this->hasMany(Articulo::class);
    }

    public function clickLogs(): HasMany
    {
        return $this->hasMany(ClickLog::class);
    }

    public function scopeActivo($query)
    {
        return $query->where('comercios.activo', true);
    }
}
