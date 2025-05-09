<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskUser extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'task_user';

    protected $fillable = [
        'task_id',
        'user_id',
        'role',
        'duration',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'role' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($taskUser) {
            if (!$taskUser->created_by && auth()->check()) {
                $taskUser->created_by = auth()->id();
            }
        });

        static::updating(function ($taskUser) {
            if (auth()->check()) {
                $taskUser->updated_by = auth()->id();
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
     * Lấy người dùng liên quan
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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
