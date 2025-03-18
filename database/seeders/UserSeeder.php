<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo tài khoản admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'avatar' => 'https://i.pravatar.cc/150?img=1'
        ]);

        // Tạo 20 người dùng fake
        $avatarIds = range(2, 70); // IDs cho pravatar.cc
        shuffle($avatarIds);

        // Danh sách email đã sử dụng để tránh trùng lặp
        $usedEmails = ['admin@example.com'];

        for ($i = 0; $i < 20; $i++) {
            $gender = rand(0, 1) ? 'male' : 'female';
            $firstName = $this->getRandomName($gender, 'first');
            $lastName = $this->getRandomName($gender, 'last');
            $fullName = $firstName . ' ' . $lastName;

            // Tạo email và đảm bảo không trùng lặp
            $baseEmail = strtolower(Str::slug($firstName)) . '.' . strtolower(Str::slug($lastName)) . '@example.com';
            $email = $baseEmail;
            $counter = 1;

            while (in_array($email, $usedEmails)) {
                $email = strtolower(Str::slug($firstName)) . '.' . strtolower(Str::slug($lastName)) . $counter . '@example.com';
                $counter++;
            }

            $usedEmails[] = $email;

            User::create([
                'name' => $fullName,
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'avatar' => 'https://i.pravatar.cc/150?img=' . $avatarIds[$i]
            ]);
        }
    }

    /**
     * Lấy tên ngẫu nhiên theo giới tính
     */
    private function getRandomName($gender, $type)
    {
        $maleFirstNames = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Phan', 'Vũ', 'Võ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương', 'Lý'];
        $femaleFirstNames = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Phan', 'Vũ', 'Võ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương', 'Lý'];

        $maleLastNames = ['Văn', 'Hữu', 'Đức', 'Công', 'Quốc', 'Minh', 'Hoàng', 'Hải', 'Tuấn', 'Anh', 'Quang', 'Thành', 'Đạt', 'Hùng', 'Dũng', 'Trung', 'Tú', 'Hiếu', 'Phong', 'Bảo'];
        $femaleLastNames = ['Thị', 'Thu', 'Ngọc', 'Hồng', 'Hà', 'Lan', 'Phương', 'Thúy', 'Hương', 'Hiền', 'Linh', 'Mai', 'Trang', 'Thanh', 'Thảo', 'Quỳnh', 'Ánh', 'Yến', 'Nhung', 'Hạnh'];

        if ($type === 'first') {
            return $gender === 'male' ? $maleFirstNames[array_rand($maleFirstNames)] : $femaleFirstNames[array_rand($femaleFirstNames)];
        } else {
            return $gender === 'male' ? $maleLastNames[array_rand($maleLastNames)] : $femaleLastNames[array_rand($femaleLastNames)];
        }
    }
}
