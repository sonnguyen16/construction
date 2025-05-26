<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignSuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy người dùng có ID = 1
        $user = User::find(1);
        
        if ($user) {
            // Lấy vai trò Super Admin
            $superAdminRole = Role::where('name', 'Super Admin')->first();
            
            if ($superAdminRole) {
                // Gán vai trò Super Admin cho người dùng
                $user->assignRole($superAdminRole);
                
                $this->command->info('Đã gán vai trò Super Admin cho người dùng ' . $user->name);
            } else {
                $this->command->error('Không tìm thấy vai trò Super Admin');
            }
        } else {
            $this->command->error('Không tìm thấy người dùng có ID = 1');
        }
    }
}
