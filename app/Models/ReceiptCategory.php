<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReceiptCategory extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'note',
    ];

    /**
     * Get the receipt vouchers for the receipt category.
     */
    public function receiptVouchers()
    {
        return $this->hasMany(ReceiptVoucher::class);
    }
}
