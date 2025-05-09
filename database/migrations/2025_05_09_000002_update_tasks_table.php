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
        Schema::table('tasks', function (Blueprint $table) {
            $table->text('description')->nullable()->after('parent_id');
            $table->tinyInteger('priority')->default(1)->after('description'); // 0: Thấp, 1: Trung bình, 2: Cao, 3: Khẩn cấp
            $table->tinyInteger('status')->default(0)->after('priority'); // 0: Chưa bắt đầu, 1: Đang thực hiện, 2: Hoàn thành, 3: Tạm dừng, 4: Hủy bỏ
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['description', 'priority', 'status']);
        });
    }
};
