<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskLink extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'source_id',
        'target_id',
        'type',
        'created_by',
        'updated_by',
    ];

    /**
     * Các loại liên kết:
     * 0 - Finish to Start (FS): Công việc đích bắt đầu sau khi công việc nguồn kết thúc
     * 1 - Start to Start (SS): Công việc đích bắt đầu sau khi công việc nguồn bắt đầu
     * 2 - Finish to Finish (FF): Công việc đích kết thúc sau khi công việc nguồn kết thúc
     * 3 - Start to Finish (SF): Công việc đích kết thúc sau khi công việc nguồn bắt đầu
     */
    protected $casts = [
        'type' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($taskLink) {
            if (!$taskLink->created_by && auth()->check()) {
                $taskLink->created_by = auth()->id();
            }
        });

        static::updating(function ($taskLink) {
            if (auth()->check()) {
                $taskLink->updated_by = auth()->id();
            }
        });
    }

    /**
     * Lấy công việc nguồn
     */
    public function source()
    {
        return $this->belongsTo(Task::class, 'source_id');
    }

    /**
     * Lấy công việc đích
     */
    public function target()
    {
        return $this->belongsTo(Task::class, 'target_id');
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
