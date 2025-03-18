<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ReceiptVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'customer_id',
        'project_id',
        'bid_package_id',
        'amount',
        'status',
        'payment_date',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        // Tự động tạo mã phiếu thu khi tạo mới
        static::creating(function ($receiptVoucher) {
            $receiptVoucher->code = self::generateCode();
            $receiptVoucher->created_by = Auth::id();
            $receiptVoucher->updated_by = Auth::id();
        });

        // Cập nhật người cập nhật khi cập nhật phiếu thu
        static::updating(function ($receiptVoucher) {
            $receiptVoucher->updated_by = Auth::id();
        });
    }

    /**
     * Tạo mã phiếu thu tự động
     */
    public static function generateCode()
    {
        $prefix = 'PT';
        $date = now()->format('ymd');
        $lastReceiptVoucher = self::where('code', 'like', "{$prefix}{$date}%")->orderBy('id', 'desc')->first();

        if ($lastReceiptVoucher) {
            $lastNumber = (int) substr($lastReceiptVoucher->code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Quan hệ với khách hàng
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Quan hệ với dự án
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Quan hệ với gói thầu
     */
    public function bidPackage()
    {
        return $this->belongsTo(BidPackage::class);
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
