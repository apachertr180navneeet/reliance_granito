<?php

namespace Tests\Feature;

use App\Models\Consigner;
use App\Models\Invoice;
use App\Models\InvoiceSetting;
use App\Models\User;
use App\Services\InvoiceCalculationService;
use Database\Seeders\ConsignerSeeder;
use Database\Seeders\InvoiceSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceExportImprovementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Consigner $consigner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            UserSeeder::class,
            ConsignerSeeder::class,
            InvoiceSeeder::class,
        ]);

        $this->user = User::first();
        $this->consigner = Consigner::first();
    }

    /**
     * Test A — IGST invoice
     * - Select IGST.
     * - Confirm required export wording.
     * - Confirm LUT number is absent.
     * - Confirm applicable tax calculations (18% IGST on taxable value).
     * - Confirm exchange-rate behavior.
     * - Confirm final totals and amount in words.
     */
    public function test_a_igst_invoice_generation_and_tax_calculation(): void
    {
        $items = [
            [
                'item_no' => 1,
                'marks_numbers' => '20 FCL ( 1 )',
                'container_no' => 'TCLU7789571',
                'trailer_no' => 'TN02BB9434',
                'description' => 'NATURAL POLISHED GRANITE SLABS',
                'hsn_code' => '68022390',
                'quantity' => 518.69,
                'rate' => 31.666,
                'amount' => 16425.27,
            ],
        ];

        $postData = [
            'invoice_number' => 'RG-EXP-IGST-001',
            'invoice_date' => '30.10.2026',
            'invoice_format' => 'IGST',
            'delivery_term' => 'CIF',
            'currency' => 'USD',
            'exchange_rate' => 94.20,
            'igst_rate' => 18.00,
            'consignee_mode' => 'consigner',
            'consigner_id' => $this->consigner->id,
            'consignee_name' => $this->consigner->name,
            'buyer_name' => 'VIETNAM IMPORTER CORP',
            'buyer_address' => 'District 1, Ho Chi Minh City, Vietnam',
            'container_number' => 'TCLU7789571',
            'trailer_number' => 'TN02BB9434',
            'items_json' => json_encode($items),
        ];

        $response = $this->actingAs($this->user)->post(route('invoices.store'), $postData);
        $response->assertSessionHasNoErrors();
        $invoice = Invoice::where('invoice_number', 'RG-EXP-IGST-001')->firstOrFail();
        $response->assertRedirect(route('invoices.show', $invoice));

        // 1. Confirm format and calculated amounts
        $this->assertEquals('IGST', $invoice->invoice_format);
        $this->assertEquals(16425.27, $invoice->total_amount);
        $this->assertEquals(94.20, $invoice->exchange_rate);
        $this->assertEquals(18.00, $invoice->igst_rate);

        // Taxable value INR: 16425.27 * 94.20 = 1547260.43
        $this->assertEquals(1547260.43, $invoice->taxable_value_inr);
        // IGST INR: 1547260.43 * 0.18 = 278506.88
        $this->assertEquals(278506.88, $invoice->igst_amount_inr);
        $this->assertStringContainsString('SIXTEEN THOUSAND FOUR HUNDRED TWENTY FIVE US DOLLARS', $invoice->amount_in_words);

        // 2. Confirm output in show view
        $viewResponse = $this->actingAs($this->user)->get(route('invoices.show', $invoice));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('SUPPLY MEANT FOR EXPORT WITH PAYMENT OF IGST');
        $viewResponse->assertSee('Integrated Tax / IGST');
        $viewResponse->assertSee('278,506.88');
        $viewResponse->assertSee('94.20');
        // Ensure LUT number is absent from terms of export in IGST view
        $settings = InvoiceSetting::getSettings();
        if ($settings->lut_arn) {
            $viewResponse->assertDontSee('Terms of Export : '.$settings->lut_arn);
        }
    }

    /**
     * Test B — LUT invoice
     * - Select LUT.
     * - Confirm required LUT wording.
     * - Confirm LUT number is displayed.
     * - Confirm IGST is not added.
     * - Switch from IGST to LUT and back to ensure no stale values remain.
     */
    public function test_b_lut_invoice_wording_and_toggle_switch(): void
    {
        $items = [
            [
                'item_no' => 1,
                'description' => 'POLISHED GRANITE TILES',
                'hsn_code' => '68022390',
                'quantity' => 100.0,
                'rate' => 20.0,
                'amount' => 2000.0,
            ],
        ];

        // 1. Create LUT invoice
        $postData = [
            'invoice_number' => 'RG-EXP-LUT-001',
            'invoice_date' => '30.10.2026',
            'invoice_format' => 'LUT',
            'delivery_term' => 'CIF',
            'currency' => 'USD',
            'exchange_rate' => 94.20,
            'consignee_mode' => 'consigner',
            'consigner_id' => $this->consigner->id,
            'items_json' => json_encode($items),
        ];

        $response = $this->actingAs($this->user)->post(route('invoices.store'), $postData);
        $response->assertSessionHasNoErrors();
        $invoice = Invoice::where('invoice_number', 'RG-EXP-LUT-001')->firstOrFail();

        $this->assertEquals('LUT', $invoice->invoice_format);
        $this->assertEquals(0.00, $invoice->igst_amount_inr);

        $viewResponse = $this->actingAs($this->user)->get(route('invoices.show', $invoice));
        $viewResponse->assertSee('SUPPLY MEANT FOR EXPORT UNDER BOND OR LETTER OF UNDERTAKING WITHOUT PAYMENT OF IGST');

        // 2. Toggle to IGST via update
        $updateData = array_merge($postData, [
            'invoice_format' => 'IGST',
            'igst_rate' => 18.00,
        ]);
        $this->actingAs($this->user)->put(route('invoices.update', $invoice), $updateData);
        $invoice->refresh();

        $this->assertEquals('IGST', $invoice->invoice_format);
        // Taxable: 2000 * 94.20 = 188400. IGST: 188400 * 0.18 = 33912
        $this->assertEquals(33912.00, $invoice->igst_amount_inr);

        // 3. Toggle back to LUT to ensure no stale IGST remains
        $updateData['invoice_format'] = 'LUT';
        $this->actingAs($this->user)->put(route('invoices.update', $invoice), $updateData);
        $invoice->refresh();

        $this->assertEquals('LUT', $invoice->invoice_format);
        $this->assertEquals(0.00, $invoice->igst_amount_inr);
    }

    /**
     * Test C — Currency (INR vs USD invoices)
     * - Create an INR invoice.
     * - Create a USD invoice.
     * - Confirm correct rate, currency, subtotal, and final amount.
     */
    public function test_c_currency_behavior_inr_and_usd(): void
    {
        $calc = new InvoiceCalculationService;

        // USD Invoice Calculation
        $usdRes = $calc->calculateTotals(
            [['quantity' => 10, 'rate' => 50, 'amount' => 500]],
            'IGST',
            'USD',
            90.00,
            18.00
        );
        $this->assertEquals('USD', $usdRes['currency']);
        $this->assertEquals(500.00, $usdRes['total_amount']);
        $this->assertEquals(45000.00, $usdRes['taxable_value_inr']);
        $this->assertEquals(8100.00, $usdRes['igst_amount_inr']);
        $this->assertStringContainsString('FIVE HUNDRED US DOLLARS ONLY', $usdRes['amount_in_words']);

        // INR Invoice Calculation
        $inrRes = $calc->calculateTotals(
            [['quantity' => 10, 'rate' => 500, 'amount' => 5000]],
            'IGST',
            'INR',
            null,
            18.00
        );
        $this->assertEquals('INR', $inrRes['currency']);
        $this->assertEquals(5000.00, $inrRes['total_amount']);
        $this->assertEquals(5000.00, $inrRes['taxable_value_inr']);
        $this->assertEquals(900.00, $inrRes['igst_amount_inr']);
        $this->assertStringContainsString('FIVE THOUSAND RUPEES ONLY', $inrRes['amount_in_words']);
    }

    /**
     * Test D — Product calculation & line items
     * - Add multiple products.
     * - Confirm each line amount and invoice total.
     * - Save, reopen, and verify same products and values.
     */
    public function test_d_product_calculations_and_multiple_items(): void
    {
        $items = [
            [
                'item_no' => 1,
                'marks_numbers' => 'PKG 1-10',
                'container_no' => 'DFSU111',
                'trailer_no' => 'TR-1',
                'description' => 'GRANITE SLABS BLACK',
                'hsn_code' => '68022390',
                'quantity' => 200.5,
                'rate' => 25.0,
                'amount' => 5012.50,
            ],
            [
                'item_no' => 2,
                'marks_numbers' => 'PKG 11-20',
                'container_no' => 'DFSU222',
                'trailer_no' => 'TR-2',
                'description' => 'GRANITE SLABS WHITE',
                'hsn_code' => '68022390',
                'quantity' => 300.0,
                'rate' => 30.0,
                'amount' => 9000.00,
            ],
        ];

        $postData = [
            'invoice_number' => 'RG-EXP-MULTI-001',
            'invoice_date' => '30.10.2026',
            'invoice_format' => 'LUT',
            'delivery_term' => 'CIF',
            'currency' => 'USD',
            'consignee_mode' => 'consigner',
            'consigner_id' => $this->consigner->id,
            'items_json' => json_encode($items),
        ];

        $this->actingAs($this->user)->post(route('invoices.store'), $postData);
        $invoice = Invoice::where('invoice_number', 'RG-EXP-MULTI-001')->firstOrFail();

        $this->assertEquals(500.5, $invoice->total_quantity);
        $this->assertEquals(14012.50, $invoice->total_amount);
        $this->assertCount(2, $invoice->items);

        // Reopen in edit view
        $editResponse = $this->actingAs($this->user)->get(route('invoices.edit', $invoice));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('GRANITE SLABS BLACK');
        $editResponse->assertSee('GRANITE SLABS WHITE');
        $editResponse->assertSee('14,012.50');
    }

    /**
     * Test E — Parties (Consignee "TO THE ORDER", separate buyer, edited address, 4 notify parties)
     */
    public function test_e_parties_consignee_to_the_order_and_four_notify_parties(): void
    {
        $notifyParties = [
            ['name' => 'NOTIFY ONE PTE LTD', 'address' => 'Singapore Port Way #01', 'tax' => 'SG111', 'phone' => '111', 'email' => 'n1@sg.com'],
            ['name' => 'NOTIFY TWO LOGISTICS', 'address' => 'Haiphong Harbor St', 'tax' => 'VN222', 'phone' => '222', 'email' => 'n2@vn.com'],
            ['name' => 'NOTIFY THREE BANK', 'address' => 'Bangkok Silom Road', 'tax' => 'TH333', 'phone' => '333', 'email' => 'n3@th.com'],
            ['name' => 'NOTIFY FOUR AGENT', 'address' => 'Colombo Fort', 'tax' => 'LK444', 'phone' => '444', 'email' => 'n4@lk.com'],
        ];

        $postData = [
            'invoice_number' => 'RG-EXP-ORDER-001',
            'invoice_date' => '30.10.2026',
            'invoice_format' => 'LUT',
            'delivery_term' => 'CIF',
            'currency' => 'USD',
            'consignee_mode' => 'to_the_order',
            'consignee_name' => 'TO THE ORDER',
            'buyer_name' => 'OVERSEAS END BUYER INC',
            'buyer_address' => 'Invoice Specific Custom Address 123',
            'buyer_tax' => 'US-TAX-9988',
            'buyer_phone' => '+1-555-0199',
            'buyer_email' => 'buyer@overseas.com',
            'notify_parties_json' => json_encode($notifyParties),
            'items_json' => json_encode([
                ['description' => 'ITEM 1', 'quantity' => 10, 'rate' => 10, 'amount' => 100],
            ]),
        ];

        $this->actingAs($this->user)->post(route('invoices.store'), $postData);
        $invoice = Invoice::where('invoice_number', 'RG-EXP-ORDER-001')->firstOrFail();

        $this->assertEquals('to_the_order', $invoice->consignee_mode);
        $this->assertEquals('TO THE ORDER', $invoice->consignee_name);
        $this->assertEquals('OVERSEAS END BUYER INC', $invoice->buyer_name);
        $this->assertEquals('Invoice Specific Custom Address 123', $invoice->buyer_address);
        $this->assertCount(4, $invoice->notify_parties);

        // View document
        $showResponse = $this->actingAs($this->user)->get(route('invoices.show', $invoice));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('TO THE ORDER');
        $showResponse->assertSee('OVERSEAS END BUYER INC');
        $showResponse->assertSee('Invoice Specific Custom Address 123');
        $showResponse->assertSee('NOTIFY ONE PTE LTD');
        $showResponse->assertSee('NOTIFY FOUR AGENT');
    }

    /**
     * Test F — Container and weight handling (1 container vs 2 containers)
     */
    public function test_f_container_and_weight_handling(): void
    {
        // One container: 26500 gross / 25500 net
        $inv1 = Invoice::create([
            'invoice_number' => 'RG-CONT-01',
            'invoice_date' => '30.10.2026',
            'invoice_format' => 'LUT',
            'delivery_term' => 'CIF',
            'currency' => 'USD',
            'container_number' => 'TCLU1234567',
            'container_type' => '20 FCL',
            'no_of_containers' => 1,
            'gross_weight' => 26500.00,
            'net_weight' => 25500.00,
            'total_quantity' => 100,
            'total_amount' => 1000,
            'rounded_amount' => 1000,
            'items' => [['description' => 'TEST', 'quantity' => 100, 'rate' => 10, 'amount' => 1000]],
        ]);

        $res1 = $this->actingAs($this->user)->get(route('invoices.show', $inv1));
        $res1->assertSee('26500 KGS');
        $res1->assertSee('25500 KGS');

        // Two containers: 53000 gross / 51000 net
        $inv2 = Invoice::create([
            'invoice_number' => 'RG-CONT-02',
            'invoice_date' => '30.10.2026',
            'invoice_format' => 'LUT',
            'delivery_term' => 'CIF',
            'currency' => 'USD',
            'container_number' => 'TCLU1111111 / TCLU2222222',
            'container_type' => '20 FCL',
            'no_of_containers' => 2,
            'gross_weight' => 53000.00,
            'net_weight' => 51000.00,
            'total_quantity' => 200,
            'total_amount' => 2000,
            'rounded_amount' => 2000,
            'items' => [['description' => 'TEST 2', 'quantity' => 200, 'rate' => 10, 'amount' => 2000]],
        ]);

        $res2 = $this->actingAs($this->user)->get(route('invoices.show', $inv2));
        $res2->assertSee('53000 KGS');
        $res2->assertSee('51000 KGS');
    }

    /**
     * Test G — Delivery terms (CIF, CNF/CFR, FOB)
     */
    public function test_g_delivery_terms_options(): void
    {
        foreach (['CIF', 'CNF', 'FOB'] as $term) {
            $inv = Invoice::create([
                'invoice_number' => "RG-TERM-{$term}",
                'invoice_date' => '30.10.2026',
                'invoice_format' => 'LUT',
                'delivery_term' => $term,
                'currency' => 'USD',
                'total_quantity' => 50,
                'total_amount' => 500,
                'rounded_amount' => 500,
                'items' => [['description' => 'STONE', 'quantity' => 50, 'rate' => 10, 'amount' => 500]],
            ]);

            $res = $this->actingAs($this->user)->get(route('invoices.show', $inv));
            $res->assertStatus(200);
            $res->assertSee($term);
        }
    }

    /**
     * Test H — Region field correctness
     */
    public function test_h_region_field_handling(): void
    {
        $inv = Invoice::create([
            'invoice_number' => 'RG-REGION-01',
            'invoice_date' => '30.10.2026',
            'invoice_format' => 'LUT',
            'region' => 'Gujarat',
            'currency' => 'USD',
            'total_quantity' => 10,
            'total_amount' => 100,
            'rounded_amount' => 100,
            'items' => [['description' => 'ITEM', 'quantity' => 10, 'rate' => 10, 'amount' => 100]],
        ]);

        $this->assertEquals('Gujarat', $inv->region);

        $res = $this->actingAs($this->user)->get(route('invoices.edit', $inv));
        $res->assertStatus(200);
        $res->assertSee('value="Gujarat"', false);
    }

    /**
     * Test I — Commercial Packing List generation
     * - Verify piece count, SQM quantity, HSN, container references, and weights.
     * - Confirm totals are correct.
     * - Confirm no #REF!, undefined, null, or broken placeholders appear.
     */
    public function test_i_commercial_packing_list_generation_without_broken_placeholders(): void
    {
        $items = [
            [
                'item_no' => 1,
                'marks_numbers' => 'PKG 1-100',
                'container_no' => 'DFSU3044515',
                'trailer_no' => 'TN02BB9434',
                'description' => 'NATURAL POLISHED GRANITE SLABS CRATES',
                'hsn_code' => '68022390',
                'packages' => 120,
                'quantity' => 250.000,
                'rate' => 30.0,
                'amount' => 7500.0,
                'net_weight' => 12750.00,
                'gross_weight' => 13250.00,
            ],
            [
                'item_no' => 2,
                'marks_numbers' => 'PKG 101-200',
                'container_no' => 'DFSU3044515',
                'trailer_no' => 'TN02BB9434',
                'description' => 'POLISHED GRANITE TILES BUNDLES',
                'hsn_code' => '68022390',
                'packages' => 106,
                'quantity' => 268.690,
                'rate' => 32.0,
                'amount' => 8598.08,
                'net_weight' => 12750.00,
                'gross_weight' => 13250.00,
            ],
        ];

        $inv = Invoice::create([
            'invoice_number' => 'RG-PKG-001',
            'invoice_date' => '30.10.2026',
            'invoice_format' => 'LUT',
            'delivery_term' => 'CIF',
            'currency' => 'USD',
            'container_number' => 'DFSU3044515',
            'trailer_number' => 'TN02BB9434',
            'container_type' => '20 FCL',
            'no_of_containers' => 1,
            'total_packages' => 226,
            'package_type' => 'Wooden Bundles / Pallets',
            'gross_weight' => 26500.00,
            'net_weight' => 25500.00,
            'total_quantity' => 518.690,
            'total_amount' => 16098.08,
            'rounded_amount' => 16098.00,
            'items' => $items,
        ]);

        $response = $this->actingAs($this->user)->get(route('invoices.packing-list', $inv));
        $response->assertStatus(200);

        // Required headers & content
        $response->assertSee('COMMERCIAL PACKING LIST');
        $response->assertSee('DFSU3044515');
        $response->assertSee('68022390');
        $response->assertSee('518.690');
        $response->assertSee('226 PKGS');
        $response->assertSee('25500 KG');
        $response->assertSee('26500 KG');

        // Verify ABSOLUTELY NO broken spreadsheet formulas or placeholders
        $response->assertDontSee('#REF!');
        $response->assertDontSee('#VALUE!');
        $response->assertDontSee('undefined');
        $response->assertDontSee('NaN');
        $response->assertDontSee('null');
    }

    /**
     * Test J — Regression: Existing invoices created before changes remain unchanged
     */
    public function test_j_regression_preserves_historical_invoice(): void
    {
        $seeded = Invoice::where('invoice_number', '680 / 26-27')->firstOrFail();

        // Historical properties untouched
        $this->assertEquals('680 / 26-27', $seeded->invoice_number);
        $this->assertEquals('04.09.2026', $seeded->invoice_date);
        $this->assertEquals('INR', $seeded->currency);
        $this->assertEquals(5702.09, $seeded->total_amount);
        $this->assertEquals(430.35, $seeded->total_quantity);
        $this->assertEquals('DFSU3044515', $seeded->container_number);

        // Can still render without error
        $response = $this->actingAs($this->user)->get(route('invoices.show', $seeded));
        $response->assertStatus(200);
        $response->assertSee('680 / 26-27');
        $response->assertSee('5,702.09');

        // Can render packing list without error
        $pkgResponse = $this->actingAs($this->user)->get(route('invoices.packing-list', $seeded));
        $pkgResponse->assertStatus(200);
        $pkgResponse->assertSee('COMMERCIAL PACKING LIST');
        $pkgResponse->assertDontSee('#REF!');
    }

    /**
     * Test K — Web Route for running migrations
     */
    public function test_k_migrate_web_route_executes_successfully(): void
    {
        $response = $this->get('/migrate');
        $response->assertStatus(200);
        $this->assertTrue(
            str_contains($response->getContent(), 'Nothing to migrate') ||
            str_contains($response->getContent(), 'DONE') ||
            str_contains($response->getContent(), 'INFO')
        );

        $aliasResponse = $this->get('/run-migrate');
        $aliasResponse->assertRedirect('/migrate');
    }

    /**
     * Test L — Container Number and Trailer / Vehicle No depend on each item
     */
    public function test_l_container_and_trailer_per_item(): void
    {
        $items = [
            [
                'marks_numbers' => '20 FCL ( 1 )',
                'container_no' => 'DFSU3044515',
                'trailer_no' => 'TN02BB9434',
                'description' => 'POLISHED GRANITE FIRST CONTAINER',
                'hsn_code' => '68022390',
                'quantity' => 120.50,
                'rate' => 25.00,
                'amount' => 3012.50,
            ],
            [
                'marks_numbers' => '20 FCL ( 2 )',
                'container_no' => 'MSKU9988776',
                'trailer_no' => 'GJ03AB1234',
                'description' => 'POLISHED GRANITE SECOND CONTAINER',
                'hsn_code' => '68022390',
                'quantity' => 150.00,
                'rate' => 30.00,
                'amount' => 4500.00,
            ],
        ];

        $postData = [
            'invoice_number' => 'RG-MULTI-CONTAINER-01',
            'invoice_date' => '30.10.2026',
            'invoice_format' => 'LUT',
            'delivery_term' => 'CIF',
            'currency' => 'USD',
            'consignee_mode' => 'to_the_order',
            'consignee_name' => 'TO THE ORDER',
            'items_json' => json_encode($items),
        ];

        $response = $this->actingAs($this->user)->post(route('invoices.store'), $postData);
        $response->assertSessionHasNoErrors();

        $invoice = Invoice::where('invoice_number', 'RG-MULTI-CONTAINER-01')->firstOrFail();
        $this->assertEquals('DFSU3044515', $invoice->container_number);
        $this->assertEquals('TN02BB9434', $invoice->trailer_number);
        $this->assertEquals(2, $invoice->no_of_containers);

        // Verify Commercial Invoice view displays both containers and trailers in their respective rows
        $showResponse = $this->actingAs($this->user)->get(route('invoices.show', $invoice));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('DFSU3044515');
        $showResponse->assertSee('TN02BB9434');
        $showResponse->assertSee('MSKU9988776');
        $showResponse->assertSee('GJ03AB1234');

        // Verify Packing List view displays both containers and trailers in their respective rows
        $pkgResponse = $this->actingAs($this->user)->get(route('invoices.packing-list', $invoice));
        $pkgResponse->assertStatus(200);
        $pkgResponse->assertSee('DFSU3044515');
        $pkgResponse->assertSee('TN02BB9434');
        $pkgResponse->assertSee('MSKU9988776');
        $pkgResponse->assertSee('GJ03AB1234');
    }

    public function test_payment_terms_differ_for_lut_and_igst_formats(): void
    {
        $items = [
            [
                'item_no' => 1,
                'marks_numbers' => '20 FCL ( 1 )',
                'container_no' => 'TCLU7789571',
                'trailer_no' => 'TN02BB9434',
                'description' => 'NATURAL POLISHED GRANITE SLABS',
                'hsn_code' => '68022390',
                'quantity' => 100,
                'rate' => 10,
                'amount' => 1000,
            ],
        ];

        // 1. LUT invoice renders "100% AGAINST SCAN B/L COPY"
        $this->actingAs($this->user)->post(route('invoices.store'), [
            'invoice_number' => 'RG-LUT-TERMS-01',
            'invoice_date' => '30.10.2026',
            'invoice_format' => 'LUT',
            'delivery_term' => 'CIF',
            'currency' => 'USD',
            'consignee_mode' => 'consigner',
            'consigner_id' => $this->consigner->id,
            'payment_terms' => '100% AGAINST SCAN B/L COPY',
            'items_json' => json_encode($items),
        ]);
        $lutInvoice = Invoice::where('invoice_number', 'RG-LUT-TERMS-01')->firstOrFail();
        $lutResponse = $this->actingAs($this->user)->get(route('invoices.show', $lutInvoice));
        $lutResponse->assertStatus(200);
        $lutResponse->assertSee('100% AGAINST SCAN B/L COPY');

        // 2. IGST invoice renders "80% AT PORT & 20% AFTER CHECKING"
        $this->actingAs($this->user)->post(route('invoices.store'), [
            'invoice_number' => 'RG-IGST-TERMS-01',
            'invoice_date' => '30.10.2026',
            'invoice_format' => 'IGST',
            'delivery_term' => 'CIF',
            'currency' => 'USD',
            'exchange_rate' => 94.20,
            'igst_rate' => 18.00,
            'consignee_mode' => 'consigner',
            'consigner_id' => $this->consigner->id,
            'payment_terms' => '80% AT PORT & 20% AFTER CHECKING',
            'items_json' => json_encode($items),
        ]);
        $igstInvoice = Invoice::where('invoice_number', 'RG-IGST-TERMS-01')->firstOrFail();
        $igstResponse = $this->actingAs($this->user)->get(route('invoices.show', $igstInvoice));
        $igstResponse->assertStatus(200);
        $igstResponse->assertSee('80% AT PORT & 20% AFTER CHECKING');

        // 3. Create route with format=IGST defaults payment terms to 80% AT PORT & 20% AFTER CHECKING
        $createIgst = $this->actingAs($this->user)->get(route('invoices.create', ['format' => 'IGST']));
        $createIgst->assertStatus(200);
        $createIgst->assertSee('80% AT PORT & 20% AFTER CHECKING');
    }
}
