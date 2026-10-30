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
        Schema::create('consigners', function (Blueprint $table) {
            $table->id();
            $table->string('consigner_code')->unique()->nullable();
            $table->string('name');
            $table->string('entity_type')->default('Manufacturer Exporter');
            $table->string('iec', 20);
            $table->string('iec_branch', 10)->default('0');
            $table->string('gstin', 25);
            $table->string('pan', 20)->nullable();
            $table->string('ad_code', 25)->nullable();
            $table->string('forex_bank')->nullable();
            $table->text('registered_address');
            $table->text('factory_address')->nullable();
            $table->string('region')->default('Gujarat');
            $table->string('contact_person')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consigners');
    }
};
