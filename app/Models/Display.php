<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Display extends Model
{
    protected $fillable = ['area_id', 'ticket_id', 'called_at', 'puesto_id'];

    protected $casts = [
        'called_at' => 'datetime', // Asegura que se maneje como fecha
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function puesto()
{
    return $this->belongsTo(Puesto::class);
}
}
