<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\BidPackage;
use App\Models\Bid;
use App\Models\Contractor;
use App\Models\Customer;
use App\Models\ProjectCategory;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy danh sách nhà thầu, khách hàng và danh mục dự án
        $contractors = Contractor::all();
        $customers = Customer::all();
        $projectCategories = ProjectCategory::all();

        if ($contractors->isEmpty()) {
            $this->command->info('Không có nhà thầu nào. Hãy chạy ContractorSeeder trước.');
            return;
        }

        if ($customers->isEmpty()) {
            $this->command->info('Không có khách hàng nào. Hãy chạy CustomerSeeder trước.');
            return;
        }

        if ($projectCategories->isEmpty()) {
            $this->command->info('Không có danh mục dự án nào. Hãy chạy ProjectCategorySeeder trước.');
            return;
        }

        // Tạo 5 dự án mẫu
        for ($i = 1; $i <= 5; $i++) {
            // Chọn ngẫu nhiên một danh mục dự án
            $projectCategory = $projectCategories->random();
            $projectCode = 'PRJ-' . str_pad($i, 3, '0', STR_PAD_LEFT);

            // Thêm tiền tố danh mục vào mã dự án
            $projectCode = $projectCategory->name . '_' . $projectCode;

            $project = Project::create([
                'code' => $projectCode,
                'name' => 'Dự án ' . $i,
                'customer_id' => $customers->random()->id,
                'project_category_id' => $projectCategory->id,
                'description' => 'Mô tả chi tiết cho dự án ' . $i,
                'status' => $i % 3 == 0 ? 'completed' : ($i % 3 == 1 ? 'active' : 'cancelled'),
            ]);

            // Tạo 2-4 gói thầu cho mỗi dự án
            $numBidPackages = rand(2, 4);
            for ($j = 1; $j <= $numBidPackages; $j++) {
                $bidPackage = BidPackage::create([
                    'project_id' => $project->id,
                    'code' => 'BP-' . str_pad($i, 3, '0', STR_PAD_LEFT) . '-' . $j,
                    'name' => 'Gói thầu ' . $j . ' của dự án ' . $i,
                    'description' => 'Mô tả chi tiết cho gói thầu ' . $j . ' của dự án ' . $i,
                    'status' => $j % 3 == 0 ? 'completed' : ($j % 3 == 1 ? 'open' : 'awarded'),
                ]);

                // Tạo 3-5 giá dự thầu cho mỗi gói thầu
                $numBids = rand(3, 5);
                $selectedContractorIndex = rand(0, $numBids - 1);

                // Lấy ngẫu nhiên các nhà thầu
                $randomContractors = $contractors->random($numBids);

                for ($k = 0; $k < $numBids; $k++) {
                    $contractor = $randomContractors[$k];
                    $price = rand(50000000, 200000000);

                    $bid = Bid::create([
                        'bid_package_id' => $bidPackage->id,
                        'contractor_id' => $contractor->id,
                        'price' => $price,
                        'notes' => $k % 2 == 0 ? 'Ghi chú cho giá dự thầu của ' . $contractor->name : null,
                        'is_selected' => $bidPackage->status != 'open' && $k == $selectedContractorIndex,
                    ]);

                    // Nếu là nhà thầu được chọn, cập nhật thông tin gói thầu
                    if ($bid->is_selected) {
                        $clientPrice = $price * (1 + rand(10, 30) / 100); // Giá giao thầu cao hơn 10-30%

                        $bidPackage->selected_contractor_id = $contractor->id;
                        $bidPackage->estimated_price = $price;
                        $bidPackage->client_price = $clientPrice;
                        $bidPackage->profit = $clientPrice - $price;
                        $bidPackage->save();
                    }
                }
            }
        }
    }
}
