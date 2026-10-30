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
            $table->string('invoice_format', 20)->default('LUT')->after('invoice_date'); // 'LUT' or 'IGST'
            $table->string('delivery_term', 20)->default('CIF')->after('invoice_format'); // 'CIF', 'CNF/CFR', 'FOB'
            $table->decimal('exchange_rate', 12, 4)->nullable()->after('currency');
            $table->decimal('igst_rate', 5, 2)->default(18.00)->after('exchange_rate');
            $table->decimal('taxable_value_inr', 14, 2)->default(0.00)->after('igst_rate');
            $table->decimal('igst_amount_inr', 14, 2)->default(0.00)->after('taxable_value_inr');

            $table->string('consignee_mode', 30)->default('consigner')->after('consigner_name'); // 'consigner' or 'to_the_order'
            $table->string('consignee_name')->nullable()->after('consignee_mode');
            $table->text('consignee_address')->nullable()->after('consignee_name');
            $table->string('consignee_tax', 100)->nullable()->after('consignee_address');
            $table->string('consignee_phone', 100)->nullable()->after('consignee_tax');
            $table->string('consignee_email', 100)->nullable()->after('consignee_phone');

            $table->string('buyer_name')->nullable()->after('consignee_email');
            $table->text('buyer_address')->nullable()->after('buyer_name');
            $table->string('buyer_tax', 100)->nullable()->after('buyer_address');
            $table->string('buyer_phone', 100)->nullable()->after('buyer_tax');
            $table->string('buyer_email', 100)->nullable()->after('buyer_phone');

            $table->json('notify_parties')->nullable()->after('buyer_email');

            $table->string('container_type', 50)->nullable()->after('trailer_number');
            $table->unsignedInteger('no_of_containers')->nullable()->after('container_type');
            $table->decimal('gross_weight', 12, 2)->nullable()->after('no_of_containers');
            $table->decimal('net_weight', 12, 2)->nullable()->after('gross_weight');
            $table->unsignedInteger('total_packages')->default(0)->after('net_weight');
            $table->string('package_type', 100)->default('Wooden Bundles / Pallets')->after('total_packages');
            $table->string('region', 50)->default('Morbi')->after('package_type');

            $table->string('lut_no')->nullable()->after('region');
            $table->string('iso_certification')->nullable()->after('lut_no');
            $table->json('packing_list_details')->nullable()->after('items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_format',
                'delivery_term',
                'exchange_rate',
                'igst_rate',
                'taxable_value_inr',
                'igst_amount_inr',
                'consignee_mode',
                'consignee_name',
                'consignee_address',
                'consignee_tax',
                'consignee_phone',
                'consignee_email',
                'buyer_name',
                'buyer_address',
                'buyer_tax',
                'buyer_phone',
                'buyer_email',
                'notify_parties',
                'container_type',
                'no_of_containers',
                'gross_weight',
                'net_weight',
                'total_packages',
                'package_type',
                'region',
                'lut_no',
                'iso_certification',
                'packing_list_details',
            ]);
        });
    }
};
