<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = ['area_id', 'ticket_number', 'type', 'status'];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function display()
    {
        return $this->hasOne(Display::class);
    }

}
