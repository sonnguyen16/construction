<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentVoucher;
use App\Models\Contractor;
use App\Models\BidPackage;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;

class PaymentVoucherSeeder extends Seeder
{
    /**
     * Tạo dữ liệu mẫu cho phiếu chi.
     */
    public function run(): void
    {
        $faker = Faker::create('vi_VN');
        // Đảm bảo đã có các dữ liệu liên quan
        $contractors = Contractor::all();
        $bidPackages = BidPackage::all();
        $users = User::all();

        // Kiểm tra nếu không có dữ liệu liên quan
        if ($contractors->isEmpty()) {
            $this->command->error('Không tìm thấy dữ liệu nhà thầu. Vui lòng chạy ContractorSeeder trước.');
            return;
        }

        if ($bidPackages->isEmpty()) {
            $this->command->error('Không tìm thấy dữ liệu gói thầu. Vui lòng chạy BidPackageSeeder trước.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->error('Không tìm thấy dữ liệu người dùng. Vui lòng chạy UserSeeder trước.');
            return;
        }

        // Mảng trạng thái thanh toán
        $statuses = ['paid', 'unpaid'];

        // Tạo 30 phiếu chi với dữ liệu ngẫu nhiên
        for ($i = 0; $i < 30; $i++) {
            $bidPackage = $bidPackages->random();
            $contractor = $bidPackage->selected_contractor_id ?
                Contractor::find($bidPackage->selected_contractor_id) :
                $contractors->random();
            $createdAt = Carbon::now()->subDays(rand(1, 60));

            // Tính toán số tiền ngẫu nhiên (từ 10 triệu đến 100 triệu)
            $amount = rand(10000000, 100000000);

            // Xác định người tạo và người cập nhật
            $createdBy = $users->random()->id;
            $updatedBy = rand(0, 1) ? $createdBy : $users->random()->id;

            // Xác định trạng thái
            $status = $faker->randomElement(['paid', 'unpaid']);

            // Tạo mô tả phù hợp
            $descriptions = [
                'Thanh toán đợt 1',
                'Thanh toán đợt 2',
                'Tạm ứng',
                'Thanh toán vật liệu',
                'Thanh toán nhân công',
                'Thanh toán hoàn công',
                'Thanh lý hợp đồng',
                'Chi phí phát sinh',
                'Thanh toán cuối kỳ',
                'Thanh toán theo tiến độ'
            ];
            $description = $descriptions[rand(0, count($descriptions) - 1)];

            PaymentVoucher::create([
                'code' => 'PV' . date('Ym') . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'description' => $description . ' cho ' . $bidPackage->name,
                'contractor_id' => $contractor->id,
                'bid_package_id' => $bidPackage->id,
                'amount' => $amount,
                'status' => $status,
                'created_by' => $createdBy,
                'updated_by' => $updatedBy,
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addHours(rand(1, 48))
            ]);
        }

        $this->command->info('Đã tạo thành công 30 phiếu chi mẫu!');
    }
}
