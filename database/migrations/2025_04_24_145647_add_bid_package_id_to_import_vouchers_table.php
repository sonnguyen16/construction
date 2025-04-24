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
        Schema::table('import_vouchers', function (Blueprint $table) {
            $table->foreignId('bid_package_id')->nullable()->after('project_id')->constrained('bid_packages')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('import_vouchers', function (Blueprint $table) {
            $table->dropForeign(['bid_package_id']);
            $table->dropColumn('bid_package_id');
        });
    }
};
