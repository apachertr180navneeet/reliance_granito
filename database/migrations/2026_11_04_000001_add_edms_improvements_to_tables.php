<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add fields to consigners table
        Schema::table('consigners', function (Blueprint $table) {
            if (! Schema::hasColumn('consigners', 'tax_code')) {
                $table->string('tax_code', 100)->nullable()->after('gstin');
            }
            if (! Schema::hasColumn('consigners', 'phone')) {
                $table->string('phone', 100)->nullable()->after('contact_phone');
            }
            if (! Schema::hasColumn('consigners', 'email')) {
                $table->string('email', 100)->nullable()->after('contact_email');
            }
            if (! Schema::hasColumn('consigners', 'bank_name')) {
                $table->string('bank_name', 255)->nullable()->after('forex_bank');
            }
            if (! Schema::hasColumn('consigners', 'bank_branch')) {
                $table->string('bank_branch', 255)->nullable()->after('bank_name');
            }
            if (! Schema::hasColumn('consigners', 'bank_account_no')) {
                $table->string('bank_account_no', 100)->nullable()->default('777705052052')->after('bank_branch');
            }
            if (! Schema::hasColumn('consigners', 'bank_ifsc')) {
                $table->string('bank_ifsc', 50)->nullable()->after('bank_account_no');
            }
            if (! Schema::hasColumn('consigners', 'bank_swift')) {
                $table->string('bank_swift', 50)->nullable()->after('bank_ifsc');
            }
            if (! Schema::hasColumn('consigners', 'bank_address')) {
                $table->text('bank_address')->nullable()->after('bank_swift');
            }
        });

        // 2. Update default bank account in invoice_settings
        if (Schema::hasTable('invoice_settings')) {
            try {
                DB::table('invoice_settings')
                    ->where('bank_account_no', '777705022062')
                    ->update(['bank_account_no' => '777705052052']);
            } catch (Throwable $e) {
                // Ignore if table is empty
            }
        }

        // 3. Add fields to invoices table
        Schema::table('invoices', function (Blueprint $table) {
            // Manufactured / Supplied By fields
            if (! Schema::hasColumn('invoices', 'supplied_by_name')) {
                $table->string('supplied_by_name', 255)->nullable()->after('buyer_other');
            }
            if (! Schema::hasColumn('invoices', 'supplied_by_gstin')) {
                $table->string('supplied_by_gstin', 50)->nullable()->after('supplied_by_name');
            }
            if (! Schema::hasColumn('invoices', 'supplied_by_invoice_no')) {
                $table->string('supplied_by_invoice_no', 100)->nullable()->after('supplied_by_gstin');
            }
            if (! Schema::hasColumn('invoices', 'supplied_by_cgst')) {
                $table->decimal('supplied_by_cgst', 12, 2)->nullable()->after('supplied_by_invoice_no');
            }
            if (! Schema::hasColumn('invoices', 'supplied_by_sgst')) {
                $table->decimal('supplied_by_sgst', 12, 2)->nullable()->after('supplied_by_cgst');
            }
            if (! Schema::hasColumn('invoices', 'supplied_by_igst')) {
                $table->decimal('supplied_by_igst', 12, 2)->nullable()->after('supplied_by_sgst');
            }

            // Drawback & EPCG Scheme fields
            if (! Schema::hasColumn('invoices', 'drawback_details')) {
                $table->text('drawback_details')->nullable()->after('supplied_by_igst');
            }
            if (! Schema::hasColumn('invoices', 'epcg_details')) {
                $table->text('epcg_details')->nullable()->after('drawback_details');
            }
            if (! Schema::hasColumn('invoices', 'scheme_party_name')) {
                $table->string('scheme_party_name', 255)->nullable()->after('epcg_details');
            }

            // Multiple containers and packing list structured details
            if (! Schema::hasColumn('invoices', 'containers_json')) {
                $table->json('containers_json')->nullable()->after('no_of_containers');
            }
            if (! Schema::hasColumn('invoices', 'packing_list_details')) {
                $table->json('packing_list_details')->nullable()->after('containers_json');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consigners', function (Blueprint $table) {
            $table->dropColumn([
                'tax_code',
                'phone',
                'email',
                'bank_name',
                'bank_branch',
                'bank_account_no',
                'bank_ifsc',
                'bank_swift',
                'bank_address',
            ]);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'supplied_by_name',
                'supplied_by_gstin',
                'supplied_by_invoice_no',
                'supplied_by_cgst',
                'supplied_by_sgst',
                'supplied_by_igst',
                'drawback_details',
                'epcg_details',
                'scheme_party_name',
                'containers_json',
                'packing_list_details',
            ]);
        });
    }
};
