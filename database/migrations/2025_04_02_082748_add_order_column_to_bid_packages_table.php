<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bid_packages', function (Blueprint $table) {
            $table->integer('order')->default(0)->after('status');
        });

        // Cập nhật thứ tự cho các gói thầu hiện có
        DB::statement('SET @row_number = 0');
        DB::statement('
            UPDATE bid_packages
            SET `order` = (@row_number:=@row_number + 1)
            WHERE deleted_at IS NULL
            ORDER BY created_at ASC
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bid_packages', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
