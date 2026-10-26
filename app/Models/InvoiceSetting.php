<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'tagline',
        'address',
        'phone',
        'email',
        'website',
        'iec',
        'gstin',
        'pan',
        'lut_arn',
        'lut_rule',
        'bank_name',
        'bank_branch',
        'bank_account_no',
        'bank_swift_code',
        'bank_ad_code',
        'bank_address',
        'invoice_prefix',
        'pre_carriage_by',
        'place_of_receipt',
        'country_of_origin',
        'default_port_of_loading',
        'default_port_of_discharge',
        'default_final_destination',
        'default_payment_terms',
        'origin_clause',
        'declaration',
        'currency',
        'rcmc_fieo_no',
        'repc_no',
        'iso_certification',
        'export_house_star',
        'signatory_company',
        'signatory_name',
        'signatory_title',
    ];

    /**
     * Retrieve the singleton settings record or initialize default.
     */
    public static function getSettings(): self
    {
        return static::firstOrCreate([], [
            'company_name' => 'RELIANCE GRANITO',
            'tagline' => 'EXPORTER & MANUFACTURER OF NATURAL STONE & GRANITE',
            'address' => 'SECTOR 23, 23-A-6, CHOPASNI HOUSING BOARD, OPPOSITE ASHOK UDHYAN GATE NO. 2, JODHPUR, RAJASTHAN - 342008 (INDIA)',
            'phone' => '+91- 9785065066, 9414154054',
            'email' => 'reliancegranito@gmail.com',
            'website' => 'www.reliancegranito.com',
            'iec' => 'ABEFR3842G',
            'gstin' => '08ABEFR3842G1Z5',
            'pan' => 'ABEFR3842G',
            'lut_arn' => 'AD080226010208C',
            'lut_rule' => 'UNDER LUT NO - 06/LUT RULE 96A/2026-2027 UNDER GST/DIV E/JODHPUR Via ARN NO. AD080226010208C',
            'bank_name' => 'ICICI BANK LIMITED',
            'bank_branch' => 'JODHPUR BRANCH',
            'bank_account_no' => '777705022062',
            'bank_swift_code' => 'ICICINBBCTS',
            'bank_ad_code' => '6390013',
            'bank_address' => '14/67, Chopasni Housing Board, JODHPUR - 342003, RAJASTHAN (INDIA)',
            'invoice_prefix' => '680 / 26-27',
            'pre_carriage_by' => 'TRUCK',
            'place_of_receipt' => 'CHENNAI PORT',
            'country_of_origin' => 'INDIA',
            'default_port_of_loading' => 'CHENNAI PORT',
            'default_port_of_discharge' => 'CATLAI PORT, VIETNAM',
            'default_final_destination' => 'CATLAI PORT, VIETNAM',
            'default_payment_terms' => '100% AGAINST SCAN B/L COPY',
            'origin_clause' => 'GOODS ARE OF INDIAN ORIGIN',
            'declaration' => 'We Declare that this invoice shows the actual price of the goods described and all the details given are true and best as per E.& O.E.',
            'currency' => 'INR',
            'rcmc_fieo_no' => 'RCMC/FIEO/11176/2025-2027',
            'repc_no' => 'REPC-22-10349',
            'iso_certification' => 'ISO 9001:2015 IC/9534879/24',
            'export_house_star' => 'Two Star Export House',
            'signatory_company' => 'RELIANCE GRANITO',
            'signatory_name' => 'Ditin',
            'signatory_title' => 'Partner / Manager',
        ]);
    }
}
