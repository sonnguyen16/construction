<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
        'customer_id',
        'project_category_id',
        'created_by',
        'updated_by',
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
     * Lấy tất cả các gói thầu của dự án
     */
    public function bidPackages()
    {
        return $this->hasMany(BidPackage::class)->whereNull('deleted_at')->whereNull('parent_id');
    }

    /**
     * Tính tổng giá giao thầu của tất cả các gói thầu
     */
    public function getTotalClientPriceAttribute()
    {
        return $this->bidPackages->sum('client_price');
    }

    /**
     * Tính tổng giá dự toán của tất cả các gói thầu
     */
    public function getTotalEstimatedPriceAttribute()
    {
        return $this->bidPackages->sum('estimated_price');
    }

    /**
     * Tính tổng lợi nhuận của tất cả các gói thầu
     */
    public function getTotalProfitAttribute()
    {
        return $this->bidPackages->sum('profit');
    }

    /**
     * Quan hệ với người tạo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Quan hệ với người sửa
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Lấy các phiếu thu liên quan đến dự án
     */
    public function receipt_vouchers()
    {
        return $this->hasMany(ReceiptVoucher::class)->whereNull('deleted_at');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Quan hệ với danh mục dự án
     */
    public function projectCategory()
    {
        return $this->belongsTo(ProjectCategory::class);
    }
}
