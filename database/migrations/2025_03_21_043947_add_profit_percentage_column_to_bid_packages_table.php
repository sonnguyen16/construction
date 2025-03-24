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
            $table->integer('profit_percentage')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bid_packages', function (Blueprint $table) {
            $table->dropColumn('profit_percentage');
        });
    }
};
