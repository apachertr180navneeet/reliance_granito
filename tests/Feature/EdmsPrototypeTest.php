<?php

namespace Tests\Feature;

use App\Models\Consigner;
use App\Models\Document;
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
     * Test the login page renders with clean, secure inputs (no prefilled credentials or hints).
     */
    public function test_login_page_renders_with_clean_inputs(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Reliance Granito');
        $response->assertSee('Account Sign In');
        // Ensure no credentials or passwords are leaked in the HTML markup
        $response->assertDontSee('admin@reliancegranito.com');
        $response->assertDontSee('Reliance@2026');
        $response->assertDontSee('Default:');
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
     * Test invalid credentials are rejected and not authenticated.
     */
    public function test_invalid_credentials_are_rejected(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@reliancegranito.com',
            'password' => 'WrongPassword!@#',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
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
     * Test path traversal in document streaming is strictly blocked.
     */
    public function test_path_traversal_is_blocked_in_document_streaming(): void
    {
        $response = $this->get('/docs/..%2F..%2F.env');
        $response->assertStatus(403);
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

    /**
     * Test header, sidebar, and footer are common across all authenticated pages.
     */
    public function test_common_header_sidebar_and_footer_are_present_on_all_pages(): void
    {
        $user = User::first();
        $pages = ['/dashboard', '/consigners', '/pdf-editor'];

        foreach ($pages as $url) {
            $response = $this->actingAs($user)->get($url);
            $response->assertStatus(200);

            // Assert common sidebar elements
            $response->assertSee('app-sidebar');
            $response->assertSee('Reliance Granito');
            $response->assertSee('Dashboard & Tower', false);
            $response->assertSee('Consigners (Consignor)');
            $response->assertSee('PDF Visual Editor');

            // Assert common header elements
            $response->assertSee('top-navbar');
            $response->assertSee('Jodhpur Export Office & Morbi Ceramic Facility', false);
            $response->assertSee('user-profile-badge');

            // Assert common footer elements
            $response->assertSee('app-footer');
            $response->assertSee('Reliance Granito Private Limited');
            $response->assertSee('Export Documentation Management System (EDMS)');
            $response->assertSee('Powered by');
            $response->assertSee('Laravel 12 & MySQL', false);
        }
    }

    /**
     * Test consigner create page renders with form and layout.
     */
    public function test_consigner_create_page_renders_successfully(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/consigners/create');

        $response->assertStatus(200);
        $response->assertSee('Add New Consigner Entity');
        $response->assertSee('Firm & Exporter Legal Particulars', false);
        $response->assertSee('Save Consigner to MySQL');
        $response->assertSee('app-sidebar');
        $response->assertSee('top-navbar');
        $response->assertSee('app-footer');
    }

    /**
     * Test consigner edit page renders with existing consigner data.
     */
    public function test_consigner_edit_page_renders_with_existing_data(): void
    {
        $user = User::first();
        $consigner = Consigner::first();

        $response = $this->actingAs($user)->get("/consigners/{$consigner->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Edit Consigner Particulars');
        $response->assertSee($consigner->name);
        $response->assertSee($consigner->iec);
        $response->assertSee('Update Consigner in MySQL');
        $response->assertSee('app-sidebar');
        $response->assertSee('top-navbar');
        $response->assertSee('app-footer');
    }

    /**
     * Test submitting create form stores record and redirects to consigners list.
     */
    public function test_user_can_submit_create_form_and_redirect_to_index(): void
    {
        $user = User::first();
        $payload = [
            'name' => 'Royal Granito Morbi Unit 9',
            'entity_type' => 'Manufacturer Exporter',
            'iec' => 'ABCDE9999F',
            'iec_branch' => '0',
            'gstin' => '24ABCDE9999F1Z9',
            'pan' => 'ABCDE9999F',
            'ad_code' => '7788990',
            'forex_bank' => 'State Bank of India, Morbi Branch',
            'registered_address' => 'Morbi Ceramic Highway, Morbi, Gujarat',
            'factory_address' => 'Morbi Ceramic Highway, Morbi, Gujarat',
            'region' => 'Morbi',
            'contact_person' => 'Bhavesh Patel',
            'contact_phone' => '+91 98250 11223',
            'contact_email' => 'bhavesh@royalgranito.com',
            'status' => 'Active',
        ];

        $response = $this->actingAs($user)->post('/consigners', $payload);

        $response->assertRedirect('/consigners');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('consigners', [
            'name' => 'Royal Granito Morbi Unit 9',
            'iec' => 'ABCDE9999F',
        ]);
    }

    /**
     * Test submitting edit form updates record and redirects to consigners list.
     */
    public function test_user_can_submit_edit_form_and_redirect_to_index(): void
    {
        $user = User::first();
        $consigner = Consigner::first();

        $payload = [
            'name' => 'Reliance Granito Private Limited (Updated HQ)',
            'entity_type' => 'Manufacturer Exporter',
            'iec' => $consigner->iec,
            'iec_branch' => '1',
            'gstin' => $consigner->gstin,
            'pan' => $consigner->pan,
            'ad_code' => '6390013',
            'forex_bank' => 'ICICI Bank Ltd, Jodhpur',
            'registered_address' => 'Updated Jodhpur Address, Rajasthan',
            'factory_address' => $consigner->factory_address,
            'region' => 'Jodhpur',
            'contact_person' => 'Rajesh B. Patel',
            'contact_phone' => '+91 2822 240890',
            'contact_email' => 'export@reliancegranito.com',
            'status' => 'Active',
        ];

        $response = $this->actingAs($user)->put("/consigners/{$consigner->id}", $payload);

        $response->assertRedirect('/consigners');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('consigners', [
            'id' => $consigner->id,
            'name' => 'Reliance Granito Private Limited (Updated HQ)',
            'iec_branch' => '1',
        ]);
    }
}
