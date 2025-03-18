<?php

namespace Database\Seeders;

use App\Models\Contractor;
use Illuminate\Database\Seeder;

class ContractorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contractors = [
            [
                'name' => 'Công ty TNHH Xây dựng Hoàng Tâm',
                'phone' => '0987654321',
                'email' => 'hoangtam@example.com',
                'address' => 'Số 123, Đường Lê Lợi, Quận 1, TP.HCM',
                'notes' => 'Nhà thầu chính cho các dự án lớn',
            ],
            [
                'name' => 'Công ty CP Xây dựng Minh Phát',
                'phone' => '0912345678',
                'email' => 'minhphat@example.com',
                'address' => 'Số 456, Đường Nguyễn Huệ, Quận 1, TP.HCM',
                'notes' => 'Chuyên về các công trình dân dụng',
            ],
            [
                'name' => 'Công ty TNHH Xây dựng Thành Công',
                'phone' => '0909123456',
                'email' => 'thanhcong@example.com',
                'address' => 'Số 789, Đường Trần Hưng Đạo, Quận 5, TP.HCM',
                'notes' => 'Nhà thầu phụ cho các công trình nhỏ',
            ],
            [
                'name' => 'Công ty CP Đầu tư Xây dựng Phú Hưng',
                'phone' => '0978123456',
                'email' => 'phuhung@example.com',
                'address' => 'Số 101, Đường Nguyễn Văn Linh, Quận 7, TP.HCM',
                'notes' => 'Chuyên về các dự án cao ốc văn phòng',
            ],
            [
                'name' => 'Công ty TNHH Xây dựng Tân Tiến',
                'phone' => '0918765432',
                'email' => 'tantien@example.com',
                'address' => 'Số 202, Đường Điện Biên Phủ, Quận 3, TP.HCM',
                'notes' => 'Nhà thầu cho các công trình công nghiệp',
            ],
        ];

        foreach ($contractors as $contractor) {
            Contractor::create($contractor);
        }

        // Tạo thêm 15 nhà thầu ngẫu nhiên
        for ($i = 1; $i <= 15; $i++) {
            Contractor::create([
                'name' => 'Nhà thầu ' . $i,
                'phone' => '09' . rand(10000000, 99999999),
                'email' => 'nhathau' . $i . '@example.com',
                'address' => 'Địa chỉ nhà thầu ' . $i,
                'notes' => $i % 3 == 0 ? 'Ghi chú cho nhà thầu ' . $i : null,
            ]);
        }
    }
}
