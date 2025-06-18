<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskReport extends Model
{
    use HasFactory;

    /**
     * Các thuộc tính có thể gán hàng loạt
     */
    protected $fillable = [
        'task_id',
        'user_id',
        'message',
        'progress',
        'status',
        'reviewer_id',
        'review_message',
        'reviewed_at'
    ];

    /**
     * Các thuộc tính cần chuyển đổi
     */
    protected $casts = [
        'progress' => 'integer',
        'reviewed_at' => 'datetime'
    ];

    /**
     * Lấy task liên quan đến báo cáo
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Lấy người dùng đã tạo báo cáo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Lấy người dùng đã duyệt báo cáo
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Lấy các file đính kèm của báo cáo
     */
    public function files()
    {
        return $this->hasMany(TaskReportFile::class, 'report_id');
    }
}
