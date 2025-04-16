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
        Schema::create('export_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->foreignId('project_id')->constrained();
            $table->date('export_date');
            $table->foreignId('customer_id')->nullable()->constrained();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('export_voucher_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('export_voucher_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity');
            $table->decimal('export_price', 15, 0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_voucher_items');
        Schema::dropIfExists('export_vouchers');
    }
};
