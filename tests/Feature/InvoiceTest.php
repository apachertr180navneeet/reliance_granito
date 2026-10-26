<?php

namespace Tests\Feature;

use App\Models\Consigner;
use App\Models\User;
use Database\Seeders\ConsignerSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            UserSeeder::class,
            ConsignerSeeder::class,
        ]);
    }

    /**
     * Unauthenticated user cannot access invoice creation.
     */
    public function test_invoices_create_requires_auth(): void
    {
        $response = $this->get('/invoices/create');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Authenticated user can render commercial invoice table passing variables.
     */
    public function test_authenticated_user_can_access_invoices_create_page(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/invoices/create');

        $response->assertStatus(200);
        $response->assertSee('COMMERCIAL INVOICE');
        $response->assertSee('RELIANCE GRANITO');
        $response->assertSee('Select Consignee / Buyer');
    }

    /**
     * Invoice create accepts consigner_id query param and binds to table.
     */
    public function test_invoices_create_with_consigner_query_param(): void
    {
        $user = User::first();
        $consigner = Consigner::first();

        $response = $this->actingAs($user)->get("/invoices/create?consigner_id={$consigner->id}");
        $response->assertStatus(200);
        $response->assertSee($consigner->name);
    }

    /**
     * Consigner edit page renders embedded Commercial Invoice table section.
     */
    public function test_consigner_edit_page_renders_commercial_invoice_section(): void
    {
        $user = User::first();
        $consigner = Consigner::first();

        $response = $this->actingAs($user)->get("/consigners/{$consigner->id}/edit");
        $response->assertStatus(200);
        $response->assertSee('Commercial Invoice Table');
        $response->assertSee('Create Commercial Invoice');
        $response->assertSee($consigner->name);
    }
}
