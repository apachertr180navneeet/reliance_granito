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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number')->unique();
            $table->foreignId('consigner_id')->nullable()->constrained('consigners')->nullOnDelete();
            $table->string('buyer_name');
            $table->text('buyer_address')->nullable();
            $table->string('destination_port');
            $table->string('pol_name')->default('Chennai Sea Port, India (INMAA1)');
            $table->string('incoterms')->default('CIF Hai Phong Port');
            $table->string('total_quantity')->default('518.69 SQM');
            $table->integer('total_packages')->default(226);
            $table->string('package_type')->default('Wooden Bundles / Pallets');
            $table->string('gross_weight')->default('26,500 KG');
            $table->string('net_weight')->default('26,000 KG');
            $table->string('container_number')->default('TCLU7789571');
            $table->string('seal_number')->default('14697');
            $table->string('currency', 10)->default('USD');
            $table->decimal('invoice_value', 12, 2)->default(16425.27);
            $table->string('status')->default('Active Docket');
            $table->string('review_status')->default('Ready for Redaction & Verification');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
