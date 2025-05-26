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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Tên khoản vay');
            $table->foreignId('contractor_id')->constrained()->comment('Nhà cung cấp/bên cho vay');
            $table->decimal('amount', 15, 2)->comment('Số tiền vay');
            $table->date('start_date')->comment('Ngày bắt đầu khoản vay');
            $table->date('end_date')->comment('Ngày kết thúc khoản vay');
            $table->decimal('interest_rate', 5, 2)->comment('Lãi suất (% theo năm)');
            $table->enum('status', ['active', 'completed'])
                ->default('active')->comment('Trạng thái: đang vay, đã hoàn thành');
            $table->string('contract_file')->nullable()->comment('File hợp đồng vay');
            $table->text('notes')->nullable()->comment('Ghi chú');
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
        Schema::dropIfExists('loans');
    }
};
