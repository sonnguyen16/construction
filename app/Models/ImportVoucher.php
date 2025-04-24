<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportVoucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'project_id',
        'bid_package_id',
        'import_date',
        'contractor_id',
        'notes',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'import_date' => 'date',
    ];

    /**
     * Get the project associated with the import voucher.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the contractor associated with the import voucher.
     */
    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    /**
     * Get the user who created the import voucher.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the bid package associated with the import voucher.
     */
    public function bidPackage()
    {
        return $this->belongsTo(BidPackage::class);
    }

    /**
     * Get the user who last updated the import voucher.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the items for the import voucher.
     */
    public function items()
    {
        return $this->hasMany(ImportVoucherItem::class);
    }

    /**
     * Calculate the total amount of the import voucher.
     */
    public function getTotalAmountAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->import_price;
        });
    }
}
