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
        'auto_calculate',
    ];

    protected $appends = [
        'display_estimated_price',
        'display_client_price',
        'display_additional_price',
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
        return $this->belongsTo(Project::class)->whereNull('deleted_at');
    }

    /**
     * Lấy nhà thầu được chọn
     */
    public function selectedContractor()
    {
        return $this->belongsTo(Contractor::class, 'selected_contractor_id')->whereNull('deleted_at');
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
        return $this->belongsTo(BidPackage::class, 'parent_id')->whereNull('deleted_at');
    }

    /**
     * Lấy các hạng mục con của gói thầu
     */
    public function children()
    {
        return $this->hasMany(BidPackage::class, 'parent_id')->whereNull('deleted_at');
    }
    
    /**
     * Lấy các công việc liên quan đến gói thầu
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }


    /**
     * Tính tổng giá dự toán của gói thầu (bao gồm cả các hạng mục con)
     */
    public function getTotalEstimatedPriceAttribute()
    {
        // Nếu là hạng mục con hoặc không có hạng mục con, trả về giá dự toán gốc
        if ($this->is_work_item || $this->children()->count() === 0) {
            return $this->estimated_price;
        }

        // Tính tổng giá dự toán của các hạng mục con
        $childrenSum = $this->children()->sum('estimated_price');

        // Trả về tổng giá dự toán = giá dự toán gốc + tổng giá dự toán của các hạng mục con
        return $childrenSum;
    }

    /**
     * Tính tổng giá phát sinh của gói thầu (bao gồm cả các hạng mục con)
     */
    public function getTotalAdditionalPriceAttribute()
    {
        // Nếu là hạng mục con hoặc không có hạng mục con, trả về giá phát sinh gốc
        if ($this->is_work_item || $this->children()->count() === 0) {
            return $this->additional_price;
        }

        // Tính tổng giá phát sinh của các hạng mục con
        $childrenSum = $this->children()->sum('additional_price');

        // Trả về tổng giá phát sinh = giá phát sinh gốc + tổng giá phát sinh của các hạng mục con
        return $childrenSum;
    }

    /**
     * Tính tổng giá giao thầu của gói thầu (bao gồm cả các hạng mục con)
     */
    public function getTotalClientPriceAttribute()
    {
        // Nếu là hạng mục con hoặc không có hạng mục con, trả về giá giao thầu gốc
        if ($this->is_work_item || $this->children()->count() === 0) {
            return $this->client_price;
        }

        // Tính tổng giá giao thầu của các hạng mục con
        $childrenClientPrice = 0;
        foreach ($this->children()->get() as $child) {
            $childSelectedBid = $child->bidPriceSelected;
            if ($childSelectedBid) {
                $childrenClientPrice += $childSelectedBid->price + ($child->additional_price ?? 0);
            } else {
                $childrenClientPrice += $child->additional_price ?? 0;
            }
        }

        // Trả về tổng giá giao thầu = giá dự thầu đã chọn + giá phát sinh gốc + tổng giá giao thầu của các hạng mục con
        return $childrenClientPrice;
    }

    /**
     * Trả về giá dự toán hiển thị dựa trên tùy chọn auto_calculate
     */
    public function getDisplayEstimatedPriceAttribute()
    {
        return $this->auto_calculate && !$this->is_work_item ? $this->getTotalEstimatedPriceAttribute() : $this->estimated_price;
    }

    /**
     * Trả về giá phát sinh hiển thị dựa trên tùy chọn auto_calculate
     */
    public function getDisplayAdditionalPriceAttribute()
    {
        return $this->auto_calculate && !$this->is_work_item ? $this->getTotalAdditionalPriceAttribute() : $this->additional_price;
    }

    /**
     * Trả về giá giao thầu hiển thị dựa trên tùy chọn auto_calculate
     */
    public function getDisplayClientPriceAttribute()
    {
        return $this->auto_calculate && !$this->is_work_item ? $this->getTotalClientPriceAttribute() : $this->client_price;
    }
}
