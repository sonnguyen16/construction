<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportVoucherItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_voucher_id',
        'product_id',
        'quantity',
        'import_price'
    ];

    /**
     * Get the import voucher that owns the item.
     */
    public function importVoucher()
    {
        return $this->belongsTo(ImportVoucher::class);
    }

    /**
     * Get the product associated with the item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the total amount for this item.
     */
    public function getTotalAttribute()
    {
        return $this->quantity * $this->import_price;
    }
}