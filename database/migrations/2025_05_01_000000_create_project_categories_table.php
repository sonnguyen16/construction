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
        Schema::create('project_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Thêm cột project_category_id vào bảng projects
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('project_category_id')->nullable()->after('customer_id')
                  ->constrained('project_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['project_category_id']);
            $table->dropColumn('project_category_id');
        });
        
        Schema::dropIfExists('project_categories');
    }
};
