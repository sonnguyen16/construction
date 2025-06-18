<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskReportFile extends Model
{
    use HasFactory;

    /**
     * Các thuộc tính có thể gán hàng loạt
     */
    protected $fillable = [
        'report_id',
        'file_path',
        'file_name',
        'file_size',
        'file_type'
    ];

    /**
     * Các thuộc tính cần chuyển đổi
     */
    protected $casts = [
        'file_size' => 'integer'
    ];

    /**
     * Lấy báo cáo mà file này thuộc về
     */
    public function report()
    {
        return $this->belongsTo(TaskReport::class, 'report_id');
    }

    /**
     * Lấy URL đầy đủ của file
     */
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }
}
