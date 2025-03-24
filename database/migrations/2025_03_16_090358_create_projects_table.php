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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Mã dự án
            $table->string('name'); // Tên dự án
            $table->foreignId('customer_id')->nullable()->constrained(); // Khách hàng
            $table->text('description')->nullable(); // Ghi chú
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active'); // Trạng thái
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};