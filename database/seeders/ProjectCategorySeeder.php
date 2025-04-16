<?php

namespace Database\Seeders;

use App\Models\ProjectCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo 3 danh mục mặc định: D&C, D&B, D&M
        $categories = [
            [
                'name' => 'D&C',
                'note' => 'Design and Construction - Thiết kế và Xây dựng',
            ],
            [
                'name' => 'D&B',
                'note' => 'Design and Build - Thiết kế và Thi công',
            ],
            [
                'name' => 'D&M',
                'note' => 'Design and Management - Thiết kế và Quản lý',
            ],
        ];

        foreach ($categories as $category) {
            ProjectCategory::create($category);
        }
    }
}
