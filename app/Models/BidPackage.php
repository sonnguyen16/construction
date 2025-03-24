<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BidPackage extends Model
{
    use HasFactory;

    const STATUSES = [
        'open' => 'Đang mở thầu',
        'awarded' => 'Đã chọn nhà thầu',
        'completed' => 'Đã hoàn thành',
        'cancelled' => 'Đã hủy bỏ',
    ];

    protected $fillable = [
        'project_id',
        'code',
        'name',
        'description',
        'client_price',
        'estimated_price',
        'profit',
        'selected_contractor_id',
        'status',
        'additional_price',
        'profit_percentage',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (!$project->created_by && auth()->check()) {
                $project->created_by = auth()->id();
            }
        });

        static::updating(function ($project) {
            if (auth()->check()) {
                $project->updated_by = auth()->id();
            }
        });
    }

    /**
     * Lấy dự án mà gói thầu thuộc về
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Lấy nhà thầu được chọn
     */
    public function selectedContractor()
    {
        return $this->belongsTo(Contractor::class, 'selected_contractor_id');
    }

    /**
     * Lấy tất cả các giá dự thầu của gói thầu
     */
    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    /**
     * Quan hệ với phiếu chi
     */
    public function payment_vouchers()
    {
        return $this->hasMany(PaymentVoucher::class, 'bid_package_id');
    }

    /**
     * Quan hệ với phiếu thu
     */
    public function receipt_vouchers()
    {
        return $this->hasMany(ReceiptVoucher::class, 'bid_package_id');
    }
}
