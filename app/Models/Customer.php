<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'description',
        'tax_code',
        'created_by',
        'updated_by'
    ];

    protected static function boot()
    {
        parent::boot();

        // Tự động set người tạo và người cập nhật
        static::creating(function ($customer) {
            $customer->created_by = Auth::id();
            $customer->updated_by = Auth::id();
        });

        static::updating(function ($customer) {
            $customer->updated_by = Auth::id();
        });
    }

    /**
     * Quan hệ với phiếu thu
     */
    public function receiptVouchers()
    {
        return $this->hasMany(ReceiptVoucher::class);
    }

    /**
     * Quan hệ với người tạo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Quan hệ với người cập nhật
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
