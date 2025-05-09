<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'task_product';

    protected $fillable = [
        'task_id',
        'product_id',
        'quantity',
        'notes',
        'duration',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantity' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($taskProduct) {
            if (!$taskProduct->created_by && auth()->check()) {
                $taskProduct->created_by = auth()->id();
            }
        });

        static::updating(function ($taskProduct) {
            if (auth()->check()) {
                $taskProduct->updated_by = auth()->id();
            }
        });
    }

    /**
     * Lấy công việc liên quan
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Lấy sản phẩm liên quan
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Lấy người tạo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Lấy người cập nhật
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
