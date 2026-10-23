<?php

use App\Models\Traits\HasSlug;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a nullable `slug` to the addressable entities of the domain.
     *
     * The column is nullable so existing rows (pre-slug) stay valid; from now on
     * every model created through Eloquent receives a slug automatically via the
     * {@see HasSlug} trait. Uniqueness is global for global
     * entities (businesses, users, packages) and scoped per tenant for models
     * that belong to a business (delivery_drivers, products, orders).
     */
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name')->unique();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name')->unique();
        });

        Schema::table('delivery_drivers', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('business_id');
            $table->unique(['business_id', 'slug']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->unique(['business_id', 'slug']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('business_id');
            $table->unique(['business_id', 'slug']);
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('tracking_code')->unique();
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });

        Schema::table('delivery_drivers', function (Blueprint $table) {
            $table->dropUnique(['business_id', 'slug']);
            $table->dropColumn('slug');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['business_id', 'slug']);
            $table->dropColumn('slug');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['business_id', 'slug']);
            $table->dropColumn('slug');
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
