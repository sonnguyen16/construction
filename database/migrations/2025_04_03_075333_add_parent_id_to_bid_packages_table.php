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
        Schema::table('bid_packages', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('project_id')
                ->references('id')->on('bid_packages')
                ->onDelete('cascade');
            $table->boolean('is_work_item')->default(false)->after('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bid_packages', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'is_work_item']);
        });
    }
};
