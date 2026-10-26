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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->index();
            $table->string('invoice_date');
            $table->foreignId('consigner_id')->nullable()->constrained('consigners')->nullOnDelete();
            $table->string('consigner_name')->nullable();
            $table->string('container_number')->nullable();
            $table->string('trailer_number')->nullable();
            $table->string('buyer_po')->nullable()->default('-');
            $table->json('buyer_other')->nullable();
            $table->json('shipping_logistics')->nullable();
            $table->json('items')->nullable();
            $table->decimal('total_quantity', 12, 3)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->decimal('rounded_amount', 14, 2)->default(0);
            $table->text('amount_in_words')->nullable();
            $table->string('currency', 10)->default('INR');
            $table->string('status', 50)->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
