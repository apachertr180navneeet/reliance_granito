<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('consigners') && Schema::hasColumn('consigners', 'region')) {
            DB::table('consigners')
                ->where('region', 'Morbi')
                ->update(['region' => 'Gujarat']);

            DB::table('consigners')
                ->where('name', 'LIKE', '%Morbi%')
                ->update([
                    'name' => DB::raw("REPLACE(name, 'Morbi', 'Gujarat')"),
                ]);

            DB::table('consigners')
                ->where('factory_address', 'LIKE', '%Morbi%')
                ->update([
                    'factory_address' => DB::raw("REPLACE(REPLACE(factory_address, 'Morbi - ', ''), 'Morbi, ', '')"),
                ]);
        }

        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'region')) {
            DB::table('invoices')
                ->where('region', 'Morbi')
                ->update(['region' => 'Gujarat']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed
    }
};
