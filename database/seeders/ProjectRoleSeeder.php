<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class ProjectRoleSeeder extends Seeder
{
    /**
     * Gán vai trò Super Admin cho người dùng admin@example.com trong tất cả các dự án hiện có
     */
    public function run(): void
    {
        // Tìm người dùng admin
        $adminUser = User::where('email', 'admin@example.com')->first();
        
        // Tìm vai trò Super Admin
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        
        if (!$adminUser || !$superAdminRole) {
            $this->command->error('Không tìm thấy người dùng admin hoặc vai trò Super Admin!');
            return;
        }
        
        // Lấy tất cả các dự án
        $projects = Project::all();
        
        $count = 0;
        foreach ($projects as $project) {
            // Kiểm tra xem đã có phân quyền cho admin trong dự án này chưa
            $existingRole = ProjectRole::where('user_id', $adminUser->id)
                ->where('project_id', $project->id)
                ->first();
                
            if (!$existingRole) {
                // Nếu chưa có, tạo mới
                ProjectRole::create([
                    'user_id' => $adminUser->id,
                    'project_id' => $project->id,
                    'role_id' => $superAdminRole->id
                ]);
                $count++;
            }
        }
        
        $this->command->info("Gán vai trò Super Admin cho admin@example.com thành công trong {$count} dự án.");
    }
}
