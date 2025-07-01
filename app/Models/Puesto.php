<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Puesto extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'area_id'];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Un puesto puede tener múltiples usuarios (si hay rotación)
     * O un usuario si es asignación fija
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function user()
{
    return $this->hasOne(User::class);
}

public function displays()
{
    return $this->hasMany(Display::class);
}

public function tickets()
    {
        return $this->hasManyThrough(Ticket::class, User::class);
    }
}
