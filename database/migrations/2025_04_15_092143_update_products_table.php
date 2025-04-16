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
        Schema::table('products', function (Blueprint $table) {
            // Remove the price column
            $table->dropColumn('price');

            // Add new columns
            $table->decimal('import_price', 15, 0)->default(0)->comment('Giá nhập');
            $table->decimal('export_price', 15, 0)->default(0)->comment('Giá xuất');
            $table->integer('initial_stock')->default(0)->comment('Tồn đầu');
            $table->integer('warning_threshold')->default(0)->comment('Ngưỡng cảnh báo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Restore original price column
            $table->decimal('price', 15, 0)->default(0);

            // Remove new columns
            $table->dropColumn([
                'import_price',
                'export_price',
                'initial_stock',
                'warning_threshold'
            ]);
        });
    }
};
