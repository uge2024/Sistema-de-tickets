<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'code'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function display()
    {
        return $this->hasOne(Display::class);
    }

    public function puestos()
{
    return $this->hasMany(Puesto::class);
}
}
