<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extend the slug coverage to the bitácora/unión tables so every persisted
     * record of the domain has a human-readable identifier.
     *
     * These tables have no natural `name`, so the slug is built from a
     * descriptive composite of their foreign keys and type (see the models).
     * The column is nullable to keep pre-existing rows valid and globally
     * unique because the composite already carries the parent references.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('id')->unique();
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('id')->unique();
        });

        Schema::table('package_scans', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('id')->unique();
        });

        Schema::table('order_status_logs', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('id')->unique();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });

        Schema::table('package_scans', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });

        Schema::table('order_status_logs', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
