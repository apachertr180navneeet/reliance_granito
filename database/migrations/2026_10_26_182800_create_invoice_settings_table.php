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
        Schema::create('invoice_settings', function (Blueprint $table) {
            $table->id();

            // Exporter Particulars
            $table->string('company_name')->default('RELIANCE GRANITO');
            $table->string('tagline')->nullable()->default('EXPORTER & MANUFACTURER OF NATURAL STONE & GRANITE');
            $table->text('address')->nullable();
            $table->string('phone')->nullable()->default('+91- 9785065066, 9414154054');
            $table->string('email')->nullable()->default('reliancegranito@gmail.com');
            $table->string('website')->nullable()->default('www.reliancegranito.com');
            $table->string('iec')->default('ABEFR3842G');
            $table->string('gstin')->default('08ABEFR3842G1Z5');
            $table->string('pan')->default('ABEFR3842G');
            $table->string('lut_arn')->default('AD080226010208C');
            $table->text('lut_rule')->nullable();

            // Bank Details
            $table->string('bank_name')->default('ICICI BANK LIMITED');
            $table->string('bank_branch')->default('JODHPUR BRANCH');
            $table->string('bank_account_no')->default('777705022062');
            $table->string('bank_swift_code')->default('ICICINBBCTS');
            $table->string('bank_ad_code')->default('6390013');
            $table->string('bank_address')->nullable()->default('14/67, Chopasni Housing Board, JODHPUR - 342003, RAJASTHAN (INDIA)');

            // Default Logistics & Shipment Terms
            $table->string('invoice_prefix')->default('680 / 26-27');
            $table->string('pre_carriage_by')->default('TRUCK');
            $table->string('place_of_receipt')->default('CHENNAI PORT');
            $table->string('country_of_origin')->default('INDIA');
            $table->string('default_port_of_loading')->default('CHENNAI PORT');
            $table->string('default_port_of_discharge')->default('CATLAI PORT, VIETNAM');
            $table->string('default_final_destination')->default('CATLAI PORT, VIETNAM');
            $table->string('default_payment_terms')->default('100% AGAINST SCAN B/L COPY');
            $table->string('origin_clause')->default('GOODS ARE OF INDIAN ORIGIN');
            $table->text('declaration')->nullable();
            $table->string('currency')->default('USD');

            // Statutory Badges & Signatory
            $table->string('rcmc_fieo_no')->default('RCMC/FIEO/11176/2025-2027');
            $table->string('repc_no')->default('REPC-22-10349');
            $table->string('iso_certification')->default('ISO 9001:2015 IC/9534879/24');
            $table->string('export_house_star')->default('Two Star Export House');
            $table->string('signatory_company')->default('RELIANCE GRANITO');
            $table->string('signatory_name')->default('Ditin');
            $table->string('signatory_title')->default('Partner / Manager');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_settings');
    }
};
