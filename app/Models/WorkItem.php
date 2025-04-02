<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bid_package_id',
        'name',
        'contractor_id',
        'price',
        'notes',
        'status',
        'created_by',
        'updated_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($workItem) {
            if (!$workItem->created_by && auth()->check()) {
                $workItem->created_by = auth()->id();
            }
        });

        static::updating(function ($workItem) {
            if (auth()->check()) {
                $workItem->updated_by = auth()->id();
            }
        });
    }

    /**
     * Lấy gói thầu mà hạng mục thuộc về
     */
    public function bidPackage()
    {
        return $this->belongsTo(BidPackage::class);
    }

    /**
     * Lấy nhà thầu thực hiện hạng mục
     */
    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    /**
     * Lấy người tạo hạng mục
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Lấy người cập nhật hạng mục
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
