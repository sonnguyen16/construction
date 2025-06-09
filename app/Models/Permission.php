<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = ['name', 'guard_name', 'scope'];
    
    /**
     * Lấy tất cả quyền có phạm vi toàn cục
     */
    public static function global()
    {
        return static::where('scope', 'global')->get();
    }
    
    /**
     * Lấy tất cả quyền có phạm vi dự án
     */
    public static function project()
    {
        return static::where('scope', 'project')->get();
    }
    
    /**
     * Kiểm tra xem quyền có phạm vi toàn cục không
     */
    public function isGlobal()
    {
        return $this->scope === 'global';
    }
    
    /**
     * Kiểm tra xem quyền có phạm vi dự án không
     */
    public function isProject()
    {
        return $this->scope === 'project';
    }
}
