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
        'commission_percentage',
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
     * Lấy danh sách người dùng có vai trò trong dự án
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'project_roles')
            ->withPivot('role_id')
            ->withTimestamps();
    }
    
    /**
     * Lấy danh sách vai trò trong dự án
     */
    public function projectRoles()
    {
        return $this->hasMany(ProjectRole::class);
    }
    
    /**
     * Kiểm tra người dùng có vai trò trong dự án không
     * 
     * @param User|int $user Người dùng hoặc ID người dùng
     * @param string|array|null $roles Tên vai trò hoặc mảng tên vai trò, null để chỉ kiểm tra có vai trò không
     * @return bool
     */
    public function hasUserWithRole($user, $roles = null)
    {
        $userId = $user instanceof User ? $user->id : $user;
        
        // Lấy vai trò của người dùng trong dự án
        $projectRole = $this->projectRoles()
            ->where('user_id', $userId)
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

    /**
     * Lấy tất cả các công việc của dự án
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
