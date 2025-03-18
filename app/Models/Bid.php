<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    use HasFactory;

    protected $fillable = [
        'bid_package_id',
        'contractor_id',
        'price',
        'notes',
        'is_selected',
    ];

    /**
     * Lấy gói thầu mà giá dự thầu thuộc về
     */
    public function bidPackage()
    {
        return $this->belongsTo(BidPackage::class);
    }

    /**
     * Lấy nhà thầu đưa ra giá dự thầu
     */
    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }
}