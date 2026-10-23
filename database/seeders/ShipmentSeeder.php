<?php

namespace Database\Seeders;

use App\Models\Consigner;
use App\Models\Shipment;
use Illuminate\Database\Seeder;

class ShipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $consigner = Consigner::where('consigner_code', 'EXP-001')->first();

        Shipment::updateOrCreate(
            ['shipment_number' => 'SHP-1469'],
            [
                'consigner_id' => $consigner?->id,
                'buyer_name' => 'CJ Stone Co. Ltd',
                'buyer_address' => 'No 45 Ly Thuong Kiet Street, Hong Bang District, Hai Phong City, Vietnam',
                'destination_port' => 'Hai Phong Port, Vietnam (VNHPH)',
                'pol_name' => 'Chennai Sea Port, India (INMAA1)',
                'incoterms' => 'CIF Hai Phong Port',
                'total_quantity' => '518.69 SQM',
                'total_packages' => 226,
                'package_type' => 'Wooden Bundles / Pallets (ISPM-15)',
                'gross_weight' => '26,500 KG',
                'net_weight' => '26,000 KG',
                'container_number' => 'TCLU7789571',
                'seal_number' => '14697',
                'currency' => 'USD',
                'invoice_value' => 16425.27,
                'status' => 'Active Docket',
                'review_status' => 'Ready for Review & Redaction',
            ]
        );
    }
}
