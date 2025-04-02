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
        Schema::create('work_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bid_package_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Tên hạng mục
            $table->foreignId('contractor_id')->nullable()->constrained(); // Nhà thầu thực hiện
            $table->decimal('price', 15, 2)->nullable(); // Giá hạng mục
            $table->text('notes')->nullable(); // Ghi chú
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending'); // Trạng thái
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_items');
    }
};
