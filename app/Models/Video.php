<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'title',
        'file_path',
        'size',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getUrlAttribute()
{
    return asset('storage/' . $this->file_path);
}
}
