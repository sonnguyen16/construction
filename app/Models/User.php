<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
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
     * Lấy danh sách dự án của người dùng
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_roles')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    /**
     * Lấy danh sách vai trò trong dự án của người dùng
     */
    public function projectRoles()
    {
        return $this->hasMany(ProjectRole::class);
    }

    /**
     * Kiểm tra người dùng có vai trò trong dự án không
     *
     * @param int|Project $project Dự án hoặc ID dự án
     * @param string|array $roles Tên vai trò hoặc mảng tên vai trò
     * @return bool
     */
    public function hasRoleInProject($project, $roles = null)
    {
        $projectId = $project instanceof Project ? $project->id : $project;

        // Lấy vai trò của người dùng trong dự án
        $projectRole = $this->projectRoles()
            ->where('project_id', $projectId)
            ->first();

        if (!$projectRole) {
            return false;
        }

        // Nếu không cần kiểm tra tên vai trò cụ thể
        if ($roles === null) {
            return true;
        }

        // Lấy tên vai trò
        $roleName = $projectRole->role->name;

        // Kiểm tra vai trò
        if (is_array($roles)) {
            return in_array($roleName, $roles);
        }

        return $roleName === $roles;
    }

    /**
     * Kiểm tra người dùng có quyền trong dự án không
     *
     * @param string|array $permissions Tên quyền hoặc mảng tên quyền
     * @param int|Project $project Dự án hoặc ID dự án
     * @return bool
     */
    public function hasPermissionInProject($permissions, $project)
    {
        $projectId = $project instanceof Project ? $project->id : $project;

        // Lấy vai trò của người dùng trong dự án
        $projectRole = $this->projectRoles()
            ->where('project_id', $projectId)
            ->first();

        if (!$projectRole) {
            return false;
        }

        // Lấy vai trò và kiểm tra quyền
        $role = $projectRole->role;

        if (is_array($permissions)) {
            foreach ($permissions as $permission) {
                if ($role->hasPermissionTo($permission)) {
                    return true;
                }
            }
            return false;
        }

        return $role->hasPermissionTo($permissions);
    }
}
