<?php

namespace Tests\Feature;

use App\Models\Consigner;
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
     * Test the root redirects to the login page first.
     */
    public function test_root_redirects_to_login_page_first(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    /**
     * Test the login page renders successfully.
     */
    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Reliance Granito');
        $response->assertSee('Account Sign In');
        $response->assertSee('admin@reliancegranito.com');
    }

    /**
     * Test successful login redirects to dashboard.
     */
    public function test_user_can_authenticate_and_redirect_to_dashboard(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@reliancegranito.com',
            'password' => 'Reliance@2026',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    /**
     * Test the dashboard requires authentication and loads when logged in.
     */
    public function test_dashboard_requires_auth_and_renders(): void
    {
        // Unauthenticated access redirects to login
        $unauthResponse = $this->get('/dashboard');
        $unauthResponse->assertRedirect('/login');

        // Authenticated access succeeds
        $user = User::first();
        $authResponse = $this->actingAs($user)->get('/dashboard');
        $authResponse->assertStatus(200);
        $authResponse->assertSee('Reliance Granito');
        $authResponse->assertSee('Export Control Tower');
        $authResponse->assertSee('Consignment #1469');
    }

    /**
     * Test the consigners page renders when authenticated.
     */
    public function test_consigners_index_renders_when_authenticated(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/consigners');
        $response->assertStatus(200);
        $response->assertSee('Consigners Master');
        $response->assertSee('Reliance Granito Private Limited');
    }

    /**
     * Test consigners API returns JSON array.
     */
    public function test_consigners_api_returns_json(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->getJson('/consigners');
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
        $user = User::first();
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

        $response = $this->actingAs($user)->postJson('/consigners', $payload);
        $response->assertStatus(201);
        $this->assertDatabaseHas('consigners', [
            'name' => 'Morbi Export Tiles Unit 5',
            'iec' => 'ABCDE1234F',
        ]);
    }

    /**
     * Test PDF Editor renders with documents and consigners when authenticated.
     */
    public function test_pdf_editor_renders_successfully(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/pdf-editor');
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
        $user = User::first();
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
                    ],
                ],
            ],
            'edits_count' => 1,
        ];

        $response = $this->actingAs($user)->postJson('/pdf-editor/save', $payload);
        $response->assertStatus(200);
        $this->assertDatabaseHas('pdf_edits', [
            'document_name' => '1469 SB ORIGINAL.PDF',
            'edits_count' => 1,
        ]);
    }
}
