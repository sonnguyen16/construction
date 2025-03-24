<?php

namespace Database\Seeders;

use App\Models\ReceiptVoucher;
use App\Models\Customer;
use App\Models\Project;
use App\Models\BidPackage;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Carbon\Carbon;

class ReceiptVoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('vi_VN');

        $customers = Customer::all();
        $projects = Project::all();

        // Tạo 30 phiếu thu mẫu
        for ($i = 0; $i < 30; $i++) {
            $customer = $customers->random();
            $project = $projects->random();
            $status = $faker->randomElement(['paid', 'unpaid']);
            $paymentDate = $status === 'completed' ? Carbon::now()->subDays($faker->numberBetween(1, 30)) : null;

            ReceiptVoucher::create([
                'customer_id' => $customer->id,
                'project_id' => $project->id,
                'amount' => $faker->numberBetween(10000000, 500000000),
                'description' => $faker->sentence(10),
                'status' => $status,
                'payment_date' => $paymentDate,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now()->subDays($faker->numberBetween(1, 120))
            ]);
        }
    }
}
