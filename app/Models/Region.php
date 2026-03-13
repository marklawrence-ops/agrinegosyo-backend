<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    // A Region has many MSMEs
    public function msmes()
    {
        return $this->hasMany(MSME::class);
    }
}