<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExportVoucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'project_id',
        'export_date',
        'customer_id',
        'notes',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'export_date' => 'date',
    ];

    /**
     * Get the project associated with the export voucher.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the customer associated with the export voucher.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who created the export voucher.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the export voucher.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the items for the export voucher.
     */
    public function items()
    {
        return $this->hasMany(ExportVoucherItem::class);
    }

    /**
     * Calculate the total amount of the export voucher.
     */
    public function getTotalAmountAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->export_price;
        });
    }
}
