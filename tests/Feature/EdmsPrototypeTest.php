<?php

namespace Tests\Feature;

use App\Models\Consigner;
use App\Models\Document;
use App\Models\Shipment;
use App\Models\User;
use Database\Seeders\ConsignerSeeder;
use Database\Seeders\DocumentSeeder;
use Database\Seeders\ShipmentSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EdmsPrototypeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed([
            UserSeeder::class,
            ConsignerSeeder::class,
            ShipmentSeeder::class,
            DocumentSeeder::class,
        ]);
    }

    /**
     * Test the root redirects to dashboard.
     */
    public function test_root_redirects_to_dashboard(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/dashboard');
    }

    /**
     * Test the dashboard loads successfully.
     */
    public function test_dashboard_renders_with_consignment_details(): void
    {
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Reliance Granito');
        $response->assertSee('Export Control Tower');
        $response->assertSee('Consignment #1469');
    }

    /**
     * Test the consigners page renders successfully.
     */
    public function test_consigners_index_renders(): void
    {
        $response = $this->get('/consigners');
        $response->assertStatus(200);
        $response->assertSee('Consigners Master');
        $response->assertSee('Reliance Granito Private Limited');
    }

    /**
     * Test consigners API returns JSON array.
     */
    public function test_consigners_api_returns_json(): void
    {
        $response = $this->getJson('/consigners');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'consigner_code' => 'EXP-001',
            'name' => 'Reliance Granito Private Limited',
        ]);
    }

    /**
     * Test creating a consigner stores it in database.
     */
    public function test_consigner_can_be_created_in_database(): void
    {
        $payload = [
            'name' => 'Morbi Export Tiles Unit 5',
            'entity_type' => 'Manufacturer Exporter',
            'iec' => 'ABCDE1234F',
            'iec_branch' => '0',
            'gstin' => '24ABCDE1234F1Z0',
            'registered_address' => 'Plot 101, GIDC Morbi, Gujarat',
            'factory_address' => 'Plot 101, GIDC Morbi, Gujarat',
            'region' => 'Morbi',
            'status' => 'Active',
        ];

        $response = $this->postJson('/consigners', $payload);
        $response->assertStatus(201);
        $this->assertDatabaseHas('consigners', [
            'name' => 'Morbi Export Tiles Unit 5',
            'iec' => 'ABCDE1234F',
        ]);
    }

    /**
     * Test PDF Editor renders with documents and consigners.
     */
    public function test_pdf_editor_renders_successfully(): void
    {
        $response = $this->get('/pdf-editor');
        $response->assertStatus(200);
        $response->assertSee('Visual PDF Editor');
        $response->assertSee('1469 SB ORIGINAL.PDF');
    }

    /**
     * Test documents streaming endpoint streams authentic PDF file.
     */
    public function test_documents_stream_endpoint_returns_pdf(): void
    {
        $response = $this->get('/docs/1469%20SB%20ORIGINAL.PDF');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test saving PDF annotations persists to database.
     */
    public function test_pdf_annotations_can_be_saved_to_mysql(): void
    {
        $payload = [
            'document_name' => '1469 SB ORIGINAL.PDF',
            'annotations' => [
                '1' => [
                    [
                        'id' => 'ann_test_1',
                        'type' => 'whiteout',
                        'x' => 0.1,
                        'y' => 0.2,
                        'w' => 0.3,
                        'h' => 0.05,
                    ]
                ]
            ],
            'edits_count' => 1,
        ];

        $response = $this->postJson('/pdf-editor/save', $payload);
        $response->assertStatus(200);
        $this->assertDatabaseHas('pdf_edits', [
            'document_name' => '1469 SB ORIGINAL.PDF',
            'edits_count' => 1,
        ]);
    }
}
