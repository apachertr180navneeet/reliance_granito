<?php

namespace Tests\Feature;

use App\Models\Consignee;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsigneeTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::first();
    }

    /**
     * Test consignees index page loads with Consignee Master.
     */
    public function test_consignees_index_page_loads_with_consignee_master(): void
    {
        $response = $this->actingAs($this->user)->get(route('consignees.index'));

        $response->assertStatus(200);
        $response->assertSee('Consignee Master');
        $response->assertSee('Add Consignee');
        $response->assertSee(Consignee::first()->name);
    }

    /**
     * Test consignees create page loads with Consignee form.
     */
    public function test_consignees_create_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('consignees.create'));

        $response->assertStatus(200);
        $response->assertSee('Add New Consignee');
        $response->assertSee('Save Consignee to MySQL');
    }

    /**
     * Test storing a new consignee.
     */
    public function test_consignee_can_be_created(): void
    {
        $payload = [
            'name' => 'Royal Vietnam Granite Importers',
            'entity_type' => 'Manufacturer Exporter',
            'iec' => 'ROYAL1234V',
            'iec_branch' => '0',
            'gstin' => '24ROYAL1234V1Z8',
            'registered_address' => 'Cat Lai Port Industrial Zone, Vietnam',
            'factory_address' => 'Cat Lai Port, District 2, Vietnam',
            'region' => 'Gujarat',
            'phone' => '+84 28 3742 2234',
            'email' => 'import@royalvietnam.vn',
            'status' => 'Active',
        ];

        $response = $this->actingAs($this->user)->post(route('consignees.store'), $payload);

        $response->assertRedirect(route('consignees.index'));
        $this->assertDatabaseHas('consigners', [
            'name' => 'Royal Vietnam Granite Importers',
            'iec' => 'ROYAL1234V',
        ]);

        $consignee = Consignee::where('name', 'Royal Vietnam Granite Importers')->first();
        $this->assertNotNull($consignee);
        $this->assertEquals('+84 28 3742 2234', $consignee->phone);
    }

    /**
     * Test editing a consignee.
     */
    public function test_consignee_can_be_edited_and_updated(): void
    {
        $consignee = Consignee::first();

        $editResponse = $this->actingAs($this->user)->get(route('consignees.edit', $consignee));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Edit Consignee');
        $editResponse->assertSee($consignee->name);

        $updateData = [
            'name' => 'Updated Reliance Consignee Unit',
            'entity_type' => 'Manufacturer Exporter',
            'iec' => $consignee->iec,
            'iec_branch' => '0',
            'gstin' => '24ABEFR3842G1Z9',
            'tax_code' => '24ABEFR3842G1Z9',
            'bank_account_no' => '777705052052',
            'registered_address' => 'Updated Address, Gujarat',
            'factory_address' => 'Updated Factory, Gujarat',
            'region' => 'Gujarat',
            'phone' => '+91 9876543210',
            'email' => 'consignee@reliancegranito.com',
            'status' => 'Active',
        ];

        $putResponse = $this->actingAs($this->user)->put(route('consignees.update', $consignee), $updateData);
        $putResponse->assertRedirect(route('consignees.index'));

        $consignee->refresh();
        $this->assertEquals('Updated Reliance Consignee Unit', $consignee->name);
        $this->assertEquals('777705052052', $consignee->bank_account_no);
    }

    /**
     * Test Invoice creation with consignee_id query parameter and Consignee relationship.
     */
    public function test_invoice_create_binds_to_consignee(): void
    {
        $consignee = Consignee::first();

        $response = $this->actingAs($this->user)->get("/invoices/create?consignee_id={$consignee->id}");
        $response->assertStatus(200);
        $response->assertSee($consignee->name);

        $invoice = Invoice::create([
            'invoice_number' => 'EXP/2026/099',
            'invoice_date' => '04.11.2026',
            'consignee_id' => $consignee->id,
            'consigner_id' => $consignee->id,
            'consignee_name' => $consignee->name,
            'consignee_mode' => 'consignee',
        ]);

        $this->assertEquals($consignee->id, $invoice->consignee->id);
        $this->assertEquals($consignee->id, $invoice->consigner->id);
        $this->assertEquals($consignee->id, $invoice->consignee_id);
    }
}
