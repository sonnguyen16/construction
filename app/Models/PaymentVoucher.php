<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PaymentVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'contractor_id',
        'payment_category_id',
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

    /**
     * Tự động tạo mã phiếu chi khi tạo mới
     */
    protected static function boot()
    {
        parent::boot();

        // Tự động tạo mã phiếu chi khi tạo mới
        static::creating(function ($paymentVoucher) {
            $paymentVoucher->code = self::generateCode();
            $paymentVoucher->created_by = Auth::id();
            $paymentVoucher->updated_by = Auth::id();
        });

        // Cập nhật người cập nhật khi cập nhật phiếu chi
        static::updating(function ($paymentVoucher) {
            $paymentVoucher->updated_by = Auth::id();
        });
    }

    /**
     * Tạo mã phiếu chi tự động
     */
    public static function generateCode()
    {
        $prefix = 'PC';
        $date = now()->format('ymd');
        $lastPaymentVoucher = self::where('code', 'like', "{$prefix}{$date}%")->orderBy('id', 'desc')->first();

        if ($lastPaymentVoucher) {
            $lastNumber = (int) substr($lastPaymentVoucher->code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Lấy gói thầu liên quan đến phiếu chi
     */
    public function bidPackage()
    {
        return $this->belongsTo(BidPackage::class, 'bid_package_id');
    }

    /**
     * Lấy nhà thầu liên quan đến phiếu chi
     */
    public function contractor()
    {
        return $this->belongsTo(Contractor::class, 'contractor_id');
    }

    /**
     * Lấy dự án liên quan đến phiếu chi
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Lấy loại chi liên quan đến phiếu chi
     */
    public function paymentCategory()
    {
        return $this->belongsTo(PaymentCategory::class);
    }

    /**
     * Lấy người tạo phiếu chi
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Lấy người cập nhật phiếu chi
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
