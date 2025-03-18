<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('vi_VN');

        // Tạo 15 khách hàng mẫu
        for ($i = 0; $i < 15; $i++) {
            Customer::create([
                'name' => $faker->company,
                'email' => $faker->companyEmail,
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
                'description' => $faker->paragraph(1),
                'created_by' => 1,
                'updated_by' => 1
            ]);
        }
    }
}
