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
        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bid_package_id')->constrained()->onDelete('cascade');
            $table->foreignId('contractor_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 15, 2); // Giá dự thầu
            $table->text('notes')->nullable(); // Ghi chú
            $table->boolean('is_selected')->default(false); // Đánh dấu nhà thầu được chọn
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bids');
    }
};
