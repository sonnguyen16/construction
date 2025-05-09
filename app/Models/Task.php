<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'start_date',
        'duration',
        'progress',
        'project_id',
        'parent_id',
        'description',
        'priority', // 0: Thấp, 1: Trung bình, 2: Cao, 3: Khẩn cấp
        'status', // 0: Chưa bắt đầu, 1: Đang thực hiện, 2: Hoàn thành, 3: Tạm dừng, 4: Hủy bỏ
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'progress' => 'float',
        'duration' => 'integer',
        'priority' => 'integer',
        'status' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($task) {
            if (!$task->created_by && auth()->check()) {
                $task->created_by = auth()->id();
            }
        });

        static::updating(function ($task) {
            if (auth()->check()) {
                $task->updated_by = auth()->id();
            }
        });
    }

    /**
     * Lấy công việc cha
     */
    public function parent()
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    /**
     * Lấy các công việc con
     */
    public function children()
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    /**
     * Lấy dự án liên quan
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
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

    /**
     * Lấy các liên kết đến công việc này
     */
    public function predecessors()
    {
        return $this->hasMany(TaskLink::class, 'target_id');
    }

    /**
     * Lấy các liên kết từ công việc này
     */
    public function successors()
    {
        return $this->hasMany(TaskLink::class, 'source_id');
    }

    /**
     * Lấy danh sách người dùng liên quan đến công việc
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withPivot('role', 'duration')
            ->withTimestamps();
    }

    /**
     * Lấy danh sách sản phẩm/vật tư liên quan đến công việc
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'task_product')
            ->withPivot('quantity', 'notes', 'duration')
            ->withTimestamps();
    }

    /**
     * Lấy đường dẫn thư mục của task
     */
    public function getFolderPathAttribute()
    {
        return "tasks/{$this->id}_" . str_replace(['/', '\\', '?', '%', '*', ':', '|', '"', '<', '>'], '_', $this->name);
    }

    /**
     * Lấy danh sách người thực hiện
     */
    public function assignees()
    {
        return $this->users()->wherePivot('role', 0);
    }

    /**
     * Lấy danh sách người phụ trách
     */
    public function managers()
    {
        return $this->users()->wherePivot('role', 1);
    }

    /**
     * Lấy danh sách người giám sát
     */
    public function supervisors()
    {
        return $this->users()->wherePivot('role', 2);
    }
}
