<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExportVoucherItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'export_voucher_id',
        'product_id',
        'quantity',
        'export_price'
    ];

    /**
     * Get the export voucher that owns the item.
     */
    public function exportVoucher()
    {
        return $this->belongsTo(ExportVoucher::class);
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
        return $this->quantity * $this->export_price;
    }
}
