<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Guards the "out of stock" alert so it fires only once per low-stock
            // cycle (re-armed when stock returns to or above the minimum).
            $table->boolean('out_of_stock_alerted')->default(false)->after('low_stock_alerted');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('out_of_stock_alerted');
        });
    }
};
