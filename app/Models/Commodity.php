<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commodity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'srp',
    ];

    // A Commodity is linked to many Price Records over time
    public function priceRecords()
    {
        return $this->hasMany(PriceRecord::class);
    }
}