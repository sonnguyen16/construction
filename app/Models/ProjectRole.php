<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class ProjectRole extends Model
{
    use HasFactory;
    
    /**
     * Các thuộc tính có thể gán hàng loạt
     */
    protected $fillable = [
        'user_id',
        'project_id',
        'role_id',
    ];
    
    /**
     * Lấy người dùng sở hữu vai trò này
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Lấy dự án mà vai trò này thuộc về
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    /**
     * Lấy vai trò
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
