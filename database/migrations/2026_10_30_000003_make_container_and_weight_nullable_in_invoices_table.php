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
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('container_type', 50)->nullable()->change();
            $table->unsignedInteger('no_of_containers')->nullable()->change();
            $table->decimal('gross_weight', 12, 2)->nullable()->change();
            $table->decimal('net_weight', 12, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('container_type', 50)->default('20 FCL')->change();
            $table->unsignedInteger('no_of_containers')->default(1)->change();
            $table->decimal('gross_weight', 12, 2)->default(26500.00)->change();
            $table->decimal('net_weight', 12, 2)->default(25500.00)->change();
        });
    }
};

