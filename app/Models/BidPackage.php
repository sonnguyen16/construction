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
        'parent_id',
        'is_work_item',
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
        'order',
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
        return $this->hasMany(Bid::class)->whereNull('deleted_at');
    }

    public function bidPriceSelected()
    {
        return $this->hasOne(Bid::class)->where('is_selected', true)->whereNull('deleted_at');
    }

    /**
     * Quan hệ với phiếu chi
     */
    public function payment_vouchers()
    {
        return $this->hasMany(PaymentVoucher::class, 'bid_package_id')->whereNull('deleted_at');
    }
    /**
     * Lấy gói thầu cha (nếu là hạng mục con)
     */
    public function parent()
    {
        return $this->belongsTo(BidPackage::class, 'parent_id');
    }

    /**
     * Lấy các hạng mục con của gói thầu
     */
    public function children()
    {
        return $this->hasMany(BidPackage::class, 'parent_id')->whereNull('deleted_at');
    }

    /**
     * Scope để lấy các gói thầu gốc (không có parent)
     */
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope để lấy các hạng mục con
     */
    public function scopeWorkItems($query)
    {
        return $query->where('is_work_item', true);
    }

    /**
     * Quan hệ với hạng mục
     * @deprecated Sử dụng children() thay thế
     */
    public function work_items()
    {
        return $this->hasMany(WorkItem::class, 'bid_package_id')->whereNull('deleted_at');
    }
}
