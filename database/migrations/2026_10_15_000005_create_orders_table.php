<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('delivery_address');
            $table->string('payment_method');
            $table->decimal('total', 12, 2)->default(0);
            $table->string('status')->default('pending'); // pending, offered, assigned, in_transit, delivered, cancelled, incident
            $table->boolean('requires_traceability')->default(false);
            $table->foreignId('delivery_driver_id')->nullable()->constrained('delivery_drivers')->nullOnDelete();
            $table->timestamp('offered_at')->nullable();
            $table->timestamp('offer_expires_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();

            $table->index('business_id');
            $table->index('status');
            $table->index('delivery_driver_id');
            $table->index('offer_expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
