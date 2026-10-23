<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Shipment;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shipment = Shipment::where('shipment_number', 'SHP-1469')->first();
        $docsDir = base_path('docs');

        $docList = [
            [
                'name' => '1469 SB ORIGINAL.PDF',
                'title' => 'Customs EDI Shipping Bill (Original)',
                'doc_type' => 'shipping_bill',
                'status' => 'Original',
                'is_primary' => true,
            ],
            [
                'name' => '1469 INVOICE ORIGINAL.pdf',
                'title' => 'Commercial Invoice #1469 (Original)',
                'doc_type' => 'invoice',
                'status' => 'Original',
                'is_primary' => true,
            ],
            [
                'name' => '1469 BL.pdf',
                'title' => 'Bill of Lading #MEDU-146908 (Original)',
                'doc_type' => 'bill_of_lading',
                'status' => 'Original',
                'is_primary' => true,
            ],
            [
                'name' => '1469 SB HIDE.pdf',
                'title' => 'Shipping Bill #1469 (Redacted Reference)',
                'doc_type' => 'masked',
                'status' => 'Masked',
                'is_primary' => false,
            ],
            [
                'name' => 'BL HIDE.pdf',
                'title' => 'Bill of Lading (Redacted Reference)',
                'doc_type' => 'masked',
                'status' => 'Masked',
                'is_primary' => false,
            ],
            [
                'name' => '1469 SB EDITED.pdf',
                'title' => 'Shipping Bill (Particulars Amended)',
                'doc_type' => 'shipping_bill',
                'status' => 'Edited',
                'is_primary' => false,
            ],
            [
                'name' => '1469 INV EDITED.pdf',
                'title' => 'Commercial Invoice (Price Redacted)',
                'doc_type' => 'invoice',
                'status' => 'Edited',
                'is_primary' => false,
            ],
            [
                'name' => 'reliance_granito_export_workflow.pdf',
                'title' => 'Reliance Granito Export Workflow & SOP Guide',
                'doc_type' => 'workflow_guide',
                'status' => 'Reference',
                'is_primary' => false,
            ],
        ];

        foreach ($docList as $item) {
            $filePath = $docsDir.DIRECTORY_SEPARATOR.$item['name'];
            $fileSize = file_exists($filePath) ? filesize($filePath) : 0;

            Document::updateOrCreate(
                ['name' => $item['name']],
                [
                    'shipment_id' => $shipment?->id,
                    'title' => $item['title'],
                    'doc_type' => $item['doc_type'],
                    'file_path' => 'docs/'.$item['name'],
                    'file_size' => $fileSize,
                    'status' => $item['status'],
                    'is_primary' => $item['is_primary'],
                ]
            );
        }
    }
}
