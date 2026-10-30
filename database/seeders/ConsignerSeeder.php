<?php

namespace Database\Seeders;

use App\Models\Consigner;
use Illuminate\Database\Seeder;

class ConsignerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $consigners = [
            [
                'consigner_code' => 'EXP-001',
                'name' => 'Reliance Granito Private Limited',
                'entity_type' => 'Manufacturer Exporter',
                'iec' => 'ABEFR3842G',
                'iec_branch' => '0',
                'gstin' => '08ABEFR3842G1Z5',
                'pan' => 'ABEFR3842G',
                'ad_code' => '6390013',
                'forex_bank' => 'ICICI Bank Limited, Jodhpur Branch (A/C: ICIC0006827)',
                'registered_address' => 'Sector 23, 23-A-6, Chopasni Housing Board, Opposite Ashok Udhyan Gate No. 2, Jodhpur, Rajasthan - 342008, India',
                'factory_address' => 'National Highway 8-A, Old Ghuntu Road, Gujarat - 363642, India',
                'region' => 'Gujarat',
                'contact_person' => 'Rajesh B. Patel',
                'contact_phone' => '+91 2822 240890',
                'contact_email' => 'export@reliancegranito.com',
                'status' => 'Active',
            ],
            [
                'consigner_code' => 'EXP-002',
                'name' => 'Reliance Granito (Gujarat Plant II)',
                'entity_type' => 'Manufacturing Facility / Plant',
                'iec' => 'ABEFR3842G',
                'iec_branch' => '1',
                'gstin' => '24ABEFR3842G1Z2',
                'pan' => 'ABEFR3842G',
                'ad_code' => '6390013',
                'forex_bank' => 'State Bank of India, Main Commercial Branch (A/C: 30894102941)',
                'registered_address' => 'Sector 23, Chopasni Housing Board, Jodhpur, Rajasthan - 342008, India',
                'factory_address' => 'Survey No. 142/P, Lakhdhirpur Road, 8-A National Highway, Gujarat - 363642, India',
                'region' => 'Gujarat',
                'contact_person' => 'Haresh V. Patel',
                'contact_phone' => '+91 98252 44101',
                'contact_email' => 'plant2@reliancegranito.com',
                'status' => 'Active',
            ],
            [
                'consigner_code' => 'EXP-003',
                'name' => 'Reliance Ceramic Industries LLP',
                'entity_type' => 'Sister Firm / Associate',
                'iec' => 'AABFR9182K',
                'iec_branch' => '0',
                'gstin' => '24AABFR9182K1Z9',
                'pan' => 'AABFR9182K',
                'ad_code' => '6391104',
                'forex_bank' => 'HDFC Bank Limited, Gujarat Branch (A/C: 502000341098)',
                'registered_address' => 'GIDC Industrial Estate, Phase II, Gujarat - 363641, India',
                'factory_address' => 'GIDC Industrial Estate, Phase II, Gujarat - 363641, India',
                'region' => 'Gujarat',
                'contact_person' => 'Bhavin R. Patel',
                'contact_phone' => '+91 2822 251140',
                'contact_email' => 'bhavin@relianceceramics.in',
                'status' => 'Active',
            ],
            [
                'consigner_code' => 'EXP-004',
                'name' => 'Marwar Granite & Quartz Export LLP',
                'entity_type' => 'Manufacturer Exporter',
                'iec' => 'AARFM7721H',
                'iec_branch' => '0',
                'gstin' => '08AARFM7721H1Z1',
                'pan' => 'AARFM7721H',
                'ad_code' => '6389920',
                'forex_bank' => 'Axis Bank Limited, Mandore Road Branch, Jodhpur (A/C: 919020045582)',
                'registered_address' => 'Plot No. 45-B, Mandore Industrial Area, Jodhpur - 342007, Rajasthan, India',
                'factory_address' => 'RIICO Industrial Area, Phase II, Boranada, Jodhpur - 342012, Rajasthan, India',
                'region' => 'Jodhpur',
                'contact_person' => 'Vikram Singh Rathore',
                'contact_phone' => '+91 291 2748890',
                'contact_email' => 'marwar.granite@gmail.com',
                'status' => 'Active',
            ],
        ];

        foreach ($consigners as $data) {
            Consigner::updateOrCreate(
                ['consigner_code' => $data['consigner_code']],
                $data
            );
        }
    }
}
