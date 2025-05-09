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
        Schema::table('task_user', function (Blueprint $table) {
            $table->integer('duration')->nullable()->after('role')->comment('Thời gian làm việc (ngày)');
        });

        Schema::table('task_product', function (Blueprint $table) {
            $table->integer('duration')->nullable()->after('quantity')->comment('Thời gian sử dụng (ngày)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_user', function (Blueprint $table) {
            $table->dropColumn('duration');
        });

        Schema::table('task_product', function (Blueprint $table) {
            $table->dropColumn('duration');
        });
    }
};
