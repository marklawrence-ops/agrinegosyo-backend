<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'msme_id',
        'commodity_id',
        'market_price',
        'variance_percentage',
        'is_compliant',
        'recorded_at',
    ];

    // A Price Record belongs to one MSME
    public function msme()
    {
        return $this->belongsTo(MSME::class);
    }

    // A Price Record belongs to one Commodity
    public function commodity()
    {
        return $this->belongsTo(Commodity::class);
    }
}