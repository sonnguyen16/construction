<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code'); // Mã phiếu chi (tự động)
            $table->text('description')->nullable(); // Mô tả (không bắt buộc)
            $table->foreignId('contractor_id')->constrained(); // Nhà thầu
            $table->foreignId('bid_package_id')->nullable()->constrained(); // Gói thầu (có thể null)
            $table->decimal('amount', 15, 2); // Số tiền
            $table->foreignId('created_by')->nullable()->constrained('users'); // Người tạo
            $table->foreignId('updated_by')->nullable()->constrained('users'); // Người sửa
            $table->timestamps();
            $table->softDeletes();
        });

        // Cập nhật các bảng khác để thêm người tạo, người sửa
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->constrained('users')->after('status');
            $table->foreignId('updated_by')->nullable()->constrained('users')->after('created_by');
        });

        Schema::table('bid_packages', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->constrained('users')->after('profit');
            $table->foreignId('updated_by')->nullable()->constrained('users')->after('created_by');
        });

        Schema::table('bids', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->constrained('users')->after('is_selected');
            $table->foreignId('updated_by')->nullable()->constrained('users')->after('created_by');
        });

        Schema::table('contractors', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->constrained('users')->after('notes');
            $table->foreignId('updated_by')->nullable()->constrained('users')->after('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contractors', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by']);
        });

        Schema::table('bids', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by']);
        });

        Schema::table('bid_packages', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by']);
        });

        Schema::dropIfExists('payment_vouchers');
    }
};
