<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\Permission as CustomPermission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Xóa cache và tạo lại các quyền
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Xóa tất cả quyền và vai trò hiện có
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Sử dụng model Permission tùy chỉnh
        $permissionModel = CustomPermission::class;

        // Tạo các quyền theo từng module

        // 1. QUYỀN CỤC BỘ (GLOBAL)

        // 1.1 Quyền cho module Dashboard (chuyển sang scope project)
        $permissionModel::create(['name' => 'dashboard.view', 'guard_name' => 'web', 'scope' => 'project']);

        // 1.2 Quyền cho module Quản lý người dùng (cục bộ)
        $permissionModel::create(['name' => 'users.view', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'users.create', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'users.edit', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'users.delete', 'guard_name' => 'web', 'scope' => 'global']);

        // 1.3 Quyền cho module Quản lý nhà thầu (cục bộ)
        $permissionModel::create(['name' => 'contractors.view', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'contractors.create', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'contractors.edit', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'contractors.delete', 'guard_name' => 'web', 'scope' => 'global']);

        // 1.4 Quyền cho module Quản lý dự án (chuyển sang scope project)
        $permissionModel::create(['name' => 'projects.view', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'projects.edit', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'projects.delete', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'projects.expenses', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'projects.profit', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'projects.commission', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'projects.files', 'guard_name' => 'web', 'scope' => 'project']);

        // 2. QUYỀN THEO DỰ ÁN (PROJECT)

        // 2.1 Quyền cho module Quản lý gói thầu
        $permissionModel::create(['name' => 'bid-packages.view', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'bid-packages.create', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'bid-packages.edit', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'bid-packages.delete', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'bid-packages.files', 'guard_name' => 'web', 'scope' => 'project']);

        // 2.2 Quyền cho module Quản lý giá dự thầu
        $permissionModel::create(['name' => 'bids.view', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'bids.create', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'bids.edit', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'bids.delete', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'bids.select-contractor', 'guard_name' => 'web', 'scope' => 'project']);

        // 1.5 Quyền cho module Quản lý khách hàng (cục bộ)
        $permissionModel::create(['name' => 'customers.view', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'customers.create', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'customers.edit', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'customers.delete', 'guard_name' => 'web', 'scope' => 'global']);

        // 1.6 Quyền cho module Quản lý danh mục (cục bộ)
        $permissionModel::create(['name' => 'categories.view', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'categories.create', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'categories.edit', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'categories.delete', 'guard_name' => 'web', 'scope' => 'global']);

        // 1.7 Quyền cho module Quản lý đơn vị (cục bộ)
        $permissionModel::create(['name' => 'units.view', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'units.create', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'units.edit', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'units.delete', 'guard_name' => 'web', 'scope' => 'global']);

        // 1.8 Quyền cho module Quản lý loại thu (cục bộ)
        $permissionModel::create(['name' => 'receipt-categories.view', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'receipt-categories.create', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'receipt-categories.edit', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'receipt-categories.delete', 'guard_name' => 'web', 'scope' => 'global']);

        // 1.9 Quyền cho module Quản lý loại chi (cục bộ)
        $permissionModel::create(['name' => 'payment-categories.view', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'payment-categories.create', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'payment-categories.edit', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'payment-categories.delete', 'guard_name' => 'web', 'scope' => 'global']);

        // 1.10 Quyền cho module Quản lý danh mục dự án (cục bộ)
        $permissionModel::create(['name' => 'project-categories.view', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'project-categories.create', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'project-categories.edit', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'project-categories.delete', 'guard_name' => 'web', 'scope' => 'global']);

        // 1.11 Quyền cho module Quản lý sản phẩm (cục bộ)
        $permissionModel::create(['name' => 'products.view', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'products.create', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'products.edit', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'products.delete', 'guard_name' => 'web', 'scope' => 'global']);

        // 2.3 Quyền cho module Quản lý phiếu chi
        $permissionModel::create(['name' => 'payment-vouchers.view', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'payment-vouchers.create', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'payment-vouchers.edit', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'payment-vouchers.delete', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'payment-vouchers.update-status', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'payment-vouchers.print', 'guard_name' => 'web', 'scope' => 'project']);

        // 2.4 Quyền cho module Quản lý phiếu thu
        $permissionModel::create(['name' => 'receipt-vouchers.view', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'receipt-vouchers.create', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'receipt-vouchers.edit', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'receipt-vouchers.delete', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'receipt-vouchers.update-status', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'receipt-vouchers.print', 'guard_name' => 'web', 'scope' => 'project']);

        // 2.5 Quyền cho module Quản lý phiếu nhập kho
        $permissionModel::create(['name' => 'import-vouchers.view', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'import-vouchers.create', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'import-vouchers.edit', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'import-vouchers.delete', 'guard_name' => 'web', 'scope' => 'project']);

        // 2.6 Quyền cho module Quản lý phiếu xuất kho
        $permissionModel::create(['name' => 'export-vouchers.view', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'export-vouchers.create', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'export-vouchers.edit', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'export-vouchers.delete', 'guard_name' => 'web', 'scope' => 'project']);

        // 2.7 Quyền cho module Quản lý công việc
        $permissionModel::create(['name' => 'tasks.view', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'tasks.create', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'tasks.edit', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'tasks.delete', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'tasks.trash', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'tasks.resources', 'guard_name' => 'web', 'scope' => 'project']);

        // 2.8 Quyền cho module Quản lý khoản vay
        $permissionModel::create(['name' => 'loans.view', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'loans.create', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'loans.edit', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'loans.delete', 'guard_name' => 'web', 'scope' => 'project']);

        // 1.12 Quyền cho module Báo cáo (chuyển sang scope project)
        $permissionModel::create(['name' => 'reports.financial', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'reports.contractor-debt', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'reports.customer-debt', 'guard_name' => 'web', 'scope' => 'project']);

        // 1.13 Quyền cho module Quản lý phân quyền (cục bộ)
        $permissionModel::create(['name' => 'roles.view', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'roles.create', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'roles.edit', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'roles.delete', 'guard_name' => 'web', 'scope' => 'global']);
        $permissionModel::create(['name' => 'permissions.view', 'guard_name' => 'web', 'scope' => 'project']);
        $permissionModel::create(['name' => 'permissions.assign', 'guard_name' => 'web', 'scope' => 'project']);

        // Tạo các vai trò
        $adminRole = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $managerRole = Role::create(['name' => 'Quản lý', 'guard_name' => 'web']);
        $accountantRole = Role::create(['name' => 'Kế toán', 'guard_name' => 'web']);
        $projectManagerRole = Role::create(['name' => 'Quản lý dự án', 'guard_name' => 'web']);
        $staffRole = Role::create(['name' => 'Nhân viên', 'guard_name' => 'web']);

        // Gán tất cả quyền cho vai trò Super Admin
        $adminRole->givePermissionTo(Permission::all());

        // Gán quyền cho vai trò Quản lý
        $managerRole->givePermissionTo([
            'dashboard.view',
            'users.view',
            'projects.view', 'projects.edit', 'projects.expenses', 'projects.profit',
            'bid-packages.view', 'bid-packages.create', 'bid-packages.edit',
            'bids.view', 'bids.create', 'bids.edit', 'bids.select-contractor',
            'contractors.view', 'contractors.create', 'contractors.edit',
            'customers.view', 'customers.create', 'customers.edit',
            'payment-vouchers.view', 'payment-vouchers.update-status', 'payment-vouchers.print',
            'receipt-vouchers.view', 'receipt-vouchers.update-status', 'receipt-vouchers.print',
            'reports.financial', 'reports.contractor-debt', 'reports.customer-debt',
            'tasks.view',
        ]);

        // Gán quyền cho vai trò Kế toán
        $accountantRole->givePermissionTo([
            'dashboard.view',
            'payment-vouchers.view', 'payment-vouchers.create', 'payment-vouchers.edit', 'payment-vouchers.update-status', 'payment-vouchers.print',
            'receipt-vouchers.view', 'receipt-vouchers.create', 'receipt-vouchers.edit', 'receipt-vouchers.update-status', 'receipt-vouchers.print',
            'payment-categories.view', 'payment-categories.create', 'payment-categories.edit',
            'receipt-categories.view', 'receipt-categories.create', 'receipt-categories.edit',
            'contractors.view',
            'customers.view',
            'reports.financial', 'reports.contractor-debt', 'reports.customer-debt',
        ]);

        // Gán quyền cho vai trò Quản lý dự án
        $projectManagerRole->givePermissionTo([
            'dashboard.view',
            'projects.view', 'projects.edit', 'projects.expenses',
            'bid-packages.view', 'bid-packages.create', 'bid-packages.edit',
            'bids.view', 'bids.create', 'bids.edit',
            'contractors.view',
            'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete',
            'products.view',
            'import-vouchers.view', 'import-vouchers.create',
            'export-vouchers.view', 'export-vouchers.create',
        ]);

        // Gán quyền cho vai trò Nhân viên
        $staffRole->givePermissionTo([
            'dashboard.view',
            'projects.view',
            'bid-packages.view',
            'bids.view',
            'contractors.view',
            'customers.view',
            'tasks.view',
            'products.view',
        ]);
    }
}
