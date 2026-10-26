<?php

namespace Database\Seeders;

use App\Models\Consigner;
use App\Models\Invoice;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Invoice::count() > 0) {
            return;
        }

        $consigner = Consigner::first();

        Invoice::create([
            'invoice_number' => '680 / 26-27',
            'invoice_date' => '04.09.2026',
            'consigner_id' => $consigner?->id,
            'consigner_name' => $consigner?->name ?? 'THANH DAT STONE MTV COMPANY LIMITED',
            'container_number' => 'DFSU3044515',
            'trailer_number' => 'TN02BB9434',
            'buyer_po' => '-',
            'buyer_other' => [
                'name' => 'GSGWEBSOFT',
                'address' => 'Plot no. 16 hanuman nagar',
                'tax_code' => 'Rajasthan',
                'phone' => '46541564156',
                'email' => 'navneetgehlot03061993@gmail.com',
            ],
            'shipping_logistics' => [
                'pre_carriage_by' => 'TRUCK',
                'place_of_receipt' => 'CHENNAI PORT',
                'country_of_origin' => 'INDIA',
                'country_of_destination' => 'CATLAI PORT, VIETNAM',
                'vessel_flight_no' => 'MSC RAVEL V.1469W',
                'port_of_loading' => 'CHENNAI PORT',
                'port_of_discharge' => 'CATLAI PORT, VIETNAM',
                'final_destination' => 'CATLAI PORT, VIETNAM',
                'payment_terms' => '100% AGAINST SCAN B/L COPY',
            ],
            'items' => [
                [
                    'item_no' => 1,
                    'marks_numbers' => '20 FCL ( 1 )',
                    'container_no' => 'DFSU3044515',
                    'trailer_no' => 'TN02BB9434',
                    'description' => "NATURAL POLISHED GRANITE SLABS\nSIZE: THICK 18 MM X (90 - 210 CM X (60 - 100 CM))",
                    'hsn_code' => '68022390',
                    'quantity' => 430.350,
                    'rate' => 13.250,
                    'amount' => 5702.09,
                ],
            ],
            'total_quantity' => 430.350,
            'total_amount' => 5702.09,
            'rounded_amount' => 5702.00,
            'amount_in_words' => 'FIVE THOUSAND SEVEN HUNDRED & TWO RUPEES ONLY',
            'currency' => 'INR',
            'status' => 'Active',
        ]);
    }
}
