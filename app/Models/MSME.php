<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MSME extends Model
{
    use HasFactory;

    // Explicitly defining the table name is a good habit for acronyms
    protected $table = 'msmes'; 

    protected $fillable = [
        'region_id',
        'store_name',
        'owner_hash',
    ];

    // An MSME belongs to one Region
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    // An MSME has many Price Records
    public function priceRecords()
    {
        return $this->hasMany(PriceRecord::class);
    }
}