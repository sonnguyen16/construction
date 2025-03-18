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
        Schema::create('bid_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('code')->unique(); // Mã gói thầu
            $table->string('name'); // Tên gói thầu
            $table->text('description')->nullable(); // Ghi chú
            $table->decimal('client_price', 15, 2)->nullable(); // Giá giao thầu cho khách hàng
            $table->decimal('estimated_price', 15, 2)->nullable(); // Giá dự toán (giá của nhà thầu được chọn)
            $table->decimal('profit', 15, 2)->nullable(); // Lợi nhuận (client_price - estimated_price)
            $table->foreignId('selected_contractor_id')->nullable()->constrained('contractors'); // Nhà thầu được chọn
            $table->enum('status', ['open', 'awarded', 'completed', 'cancelled'])->default('open'); // Trạng thái
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bid_packages');
    }
};