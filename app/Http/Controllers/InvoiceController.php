<?php

namespace App\Http\Controllers;

use App\Models\Consigner;
use App\Models\Invoice;
use App\Models\InvoiceSetting;
use App\Services\InvoiceCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceCalculationService $calculationService
    ) {}

    /**
     * Display a listing of all commercial invoices with search & pagination.
     */
    public function index(Request $request): View
    {
        $query = Invoice::with('consigner');

        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('invoice_number', 'like', "%{$term}%")
                    ->orWhere('consigner_name', 'like', "%{$term}%")
                    ->orWhere('consignee_name', 'like', "%{$term}%")
                    ->orWhere('buyer_name', 'like', "%{$term}%")
                    ->orWhere('container_number', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $invoices = $query->latest()->paginate(12)->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new Commercial Invoice with full table & dynamic variables.
     */
    public function create(Request $request): View
    {
        $settings = InvoiceSetting::getSettings();
        $consigners = Consigner::orderBy('name', 'asc')->get();

        // Target consigner from query param or null (blank)
        $consignerId = $request->query('consigner_id');
        $selectedConsigner = $consignerId ? $consigners->firstWhere('id', $consignerId) : null;

        // Standard Exporter Particulars from Settings
        $exporter = $this->buildExporterData($settings);

        // Next Invoice Number suggestion (or blank if user wants to add their own)
        $invPrefix = $request->query('inv_no', '');

        // Invoice Metadata & Shipment Specifics
        $initialFormat = strtoupper($request->query('format', 'LUT'));
        $defaultPaymentTerms = ($initialFormat === 'IGST') ? '80% AT PORT & 20% AFTER CHECKING' : '100% AGAINST SCAN B/L COPY';

        $invoice = [
            'invoice_number' => $invPrefix,
            'invoice_date' => $request->query('inv_date', now()->format('d.m.Y')),
            'invoice_format' => $initialFormat,
            'delivery_term' => $request->query('term', 'CIF'),
            'currency' => $request->query('currency', 'USD'),
            'exchange_rate' => (float) $request->query('exchange_rate', 94.20),
            'igst_rate' => (float) $request->query('igst_rate', 18.00),
            'consignee_mode' => $request->query('consignee_mode', 'consigner'),
            'consignee_name' => $selectedConsigner?->name ?? '',
            'consignee_address' => $selectedConsigner ? ($selectedConsigner->registered_address ?? $selectedConsigner->factory_address) : '',
            'consignee_tax' => $selectedConsigner ? ($selectedConsigner->gstin ?? $selectedConsigner->pan) : '',
            'consignee_phone' => $selectedConsigner?->contact_phone ?? '',
            'consignee_email' => $selectedConsigner?->contact_email ?? '',
            'buyer_name' => $request->query('buyer_name', ''),
            'buyer_address' => $request->query('buyer_address', ''),
            'buyer_tax' => $request->query('buyer_tax', ''),
            'buyer_phone' => $request->query('buyer_phone', ''),
            'buyer_email' => $request->query('buyer_email', ''),
            'container_type' => $request->query('container_type', ''),
            'no_of_containers' => $request->query('no_of_containers', ''),
            'gross_weight' => $request->query('gross_weight', ''),
            'net_weight' => $request->query('net_weight', ''),
            'total_packages' => (int) $request->query('total_packages', 0),
            'package_type' => $request->query('package_type', 'Wooden Bundles / Pallets'),
            'region' => $request->query('region', ($selectedConsigner?->region ?: 'Gujarat')),
            'lut_no' => $request->query('lut_no', ''),
            'iso_certification' => $request->query('iso_certification', ''),
            'container_number' => $request->query('container', ''),
            'trailer_number' => $request->query('trailer', ''),
            'buyer_po' => $request->query('buyer_po', ''),
            'pre_carriage_by' => $request->query('pre_carriage', ''),
            'place_of_receipt' => $request->query('place_receipt', ''),
            'country_of_origin' => $request->query('country_origin', ''),
            'country_of_destination' => $request->query('country_dest', ''),
            'vessel_flight_no' => $request->query('vessel', ''),
            'port_of_loading' => $request->query('port_loading', ''),
            'port_of_discharge' => $request->query('port_discharge', ''),
            'final_destination' => $request->query('final_dest', ''),
            'payment_terms' => $request->query('payment_terms', $defaultPaymentTerms),
            'origin_clause' => $settings->origin_clause ?: 'GOODS ARE OF INDIAN ORIGIN',
            'declaration' => $settings->declaration,
            'taxable_value_inr' => 0.00,
            'igst_amount_inr' => 0.00,
        ];

        // Notify Parties 1 to 4 (Independent)
        $notifyParties = [
            ['name' => '', 'address' => '', 'tax' => '', 'phone' => '', 'email' => ''],
            ['name' => '', 'address' => '', 'tax' => '', 'phone' => '', 'email' => ''],
            ['name' => '', 'address' => '', 'tax' => '', 'phone' => '', 'email' => ''],
            ['name' => '', 'address' => '', 'tax' => '', 'phone' => '', 'email' => ''],
        ];
        $invoice['notify_parties'] = $notifyParties;

        // Buyer (If other than Consignee) - Specific to Invoice
        $buyerOther = [
            'name' => $invoice['buyer_name'],
            'address' => $invoice['buyer_address'],
            'tax_code' => $invoice['buyer_tax'],
            'phone' => $invoice['buyer_phone'],
            'email' => $invoice['buyer_email'],
        ];

        // Line Items Table - Clean initial item
        $items = [
            [
                'item_no' => 1,
                'marks_numbers' => '',
                'container_no' => '',
                'trailer_no' => '',
                'description' => '',
                'hsn_code' => '',
                'quantity' => '',
                'rate' => '',
                'amount' => 0,
            ],
        ];

        $totalQuantity = 0;
        $totalAmount = 0;
        $roundedAmount = 0;
        $amountInWords = '';

        $bank = $this->buildBankData($settings, $selectedConsigner);
        $certifications = $this->buildCertificationsData($settings);

        return view('invoices.create', compact(
            'consigners',
            'selectedConsigner',
            'exporter',
            'invoice',
            'buyerOther',
            'notifyParties',
            'items',
            'totalQuantity',
            'totalAmount',
            'roundedAmount',
            'amountInWords',
            'bank',
            'certifications',
            'settings'
        ));
    }

    /**
     * Store a newly created commercial invoice in database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_number' => ['required', 'string', 'max:100'],
            'invoice_date' => ['required', 'string', 'max:50'],
            'invoice_format' => ['required', 'in:LUT,IGST'],
            'delivery_term' => ['required', 'string', 'max:50'],
            'currency' => ['required', 'in:USD,INR'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0'],
            'igst_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'consignee_mode' => ['required', 'in:consigner,to_the_order'],
            'consigner_id' => ['nullable', 'exists:consigners,id'],
            'consignee_name' => ['nullable', 'string', 'max:255'],
            'consignee_address' => ['nullable', 'string'],
            'consignee_tax' => ['nullable', 'string', 'max:100'],
            'consignee_phone' => ['nullable', 'string', 'max:100'],
            'consignee_email' => ['nullable', 'string', 'max:100'],

            'buyer_name' => ['nullable', 'string', 'max:255'],
            'buyer_address' => ['nullable', 'string'],
            'buyer_tax' => ['nullable', 'string', 'max:100'],
            'buyer_phone' => ['nullable', 'string', 'max:100'],
            'buyer_email' => ['nullable', 'string', 'max:100'],

            'notify_parties_json' => ['nullable', 'string'],

            'container_type' => ['nullable', 'string', 'max:50'],
            'no_of_containers' => ['nullable', 'integer', 'min:1'],
            'gross_weight' => ['nullable', 'numeric', 'min:0'],
            'net_weight' => ['nullable', 'numeric', 'min:0'],
            'total_packages' => ['nullable', 'integer', 'min:0'],
            'package_type' => ['nullable', 'string', 'max:100'],
            'region' => ['nullable', 'string', 'max:50'],
            'lut_no' => ['nullable', 'string', 'max:255'],
            'iso_certification' => ['nullable', 'string', 'max:255'],

            'container_number' => ['nullable', 'string', 'max:100'],
            'trailer_number' => ['nullable', 'string', 'max:100'],
            'buyer_po' => ['nullable', 'string', 'max:100'],
            'pre_carriage_by' => ['nullable', 'string', 'max:100'],
            'place_of_receipt' => ['nullable', 'string', 'max:100'],
            'country_of_origin' => ['nullable', 'string', 'max:100'],
            'country_of_destination' => ['nullable', 'string', 'max:100'],
            'vessel_flight_no' => ['nullable', 'string', 'max:100'],
            'port_of_loading' => ['nullable', 'string', 'max:100'],
            'port_of_discharge' => ['nullable', 'string', 'max:100'],
            'final_destination' => ['nullable', 'string', 'max:100'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'items_json' => ['nullable', 'string'],
        ]);

        $consigner = null;
        if ($validated['consignee_mode'] === 'consigner' && ! empty($validated['consigner_id'])) {
            $consigner = Consigner::find($validated['consigner_id']);
        }

        // Parse items JSON and calculate totals via centralized service
        $items = ! empty($validated['items_json']) ? json_decode($validated['items_json'], true) : [];
        if (! is_array($items)) {
            $items = [];
        }

        // Parse Notify Parties JSON
        $notifyParties = ! empty($validated['notify_parties_json']) ? json_decode($validated['notify_parties_json'], true) : [];
        if (! is_array($notifyParties)) {
            $notifyParties = [];
        }

        $calc = $this->calculationService->calculateTotals(
            $items,
            $validated['invoice_format'],
            $validated['currency'],
            ! empty($validated['exchange_rate']) ? (float) $validated['exchange_rate'] : null,
            ! empty($validated['igst_rate']) ? (float) $validated['igst_rate'] : 18.00
        );

        $consigneeName = ($validated['consignee_mode'] ?? 'consigner') === 'to_the_order'
            ? 'TO THE ORDER'
            : (($validated['consignee_name'] ?? '') ?: ($consigner?->name ?? ''));

        $invoice = DB::transaction(function () use ($validated, $consigner, $consigneeName, $items, $notifyParties, $calc) {
            return Invoice::create([
                'invoice_number' => $validated['invoice_number'],
                'invoice_date' => $validated['invoice_date'],
                'invoice_format' => $validated['invoice_format'],
                'delivery_term' => $validated['delivery_term'],
                'currency' => $calc['currency'],
                'exchange_rate' => $calc['exchange_rate'],
                'igst_rate' => $calc['igst_rate'],
                'taxable_value_inr' => $calc['taxable_value_inr'],
                'igst_amount_inr' => $calc['igst_amount_inr'],

                'consignee_mode' => $validated['consignee_mode'],
                'consigner_id' => $consigner?->id,
                'consigner_name' => $consigner?->name ?? $consigneeName,
                'consignee_name' => $consigneeName,
                'consignee_address' => $validated['consignee_mode'] === 'to_the_order' ? null : ($validated['consignee_address'] ?? ($consigner?->registered_address ?? $consigner?->factory_address)),
                'consignee_tax' => $validated['consignee_mode'] === 'to_the_order' ? null : ($validated['consignee_tax'] ?? ($consigner?->gstin ?? $consigner?->pan)),
                'consignee_phone' => $validated['consignee_mode'] === 'to_the_order' ? null : ($validated['consignee_phone'] ?? $consigner?->contact_phone),
                'consignee_email' => $validated['consignee_mode'] === 'to_the_order' ? null : ($validated['consignee_email'] ?? $consigner?->contact_email),

                'buyer_name' => $validated['buyer_name'] ?? '',
                'buyer_address' => $validated['buyer_address'] ?? '',
                'buyer_tax' => $validated['buyer_tax'] ?? '',
                'buyer_phone' => $validated['buyer_phone'] ?? '',
                'buyer_email' => $validated['buyer_email'] ?? '',

                'notify_parties' => $notifyParties,

                'container_number' => ! empty($validated['container_number']) ? $validated['container_number'] : ($items[0]['container_no'] ?? ''),
                'trailer_number' => ! empty($validated['trailer_number']) ? $validated['trailer_number'] : ($items[0]['trailer_no'] ?? ''),
                'container_type' => $validated['container_type'] ?? null,
                'no_of_containers' => !empty($validated['no_of_containers']) ? (int) $validated['no_of_containers'] : (count($items) > 0 ? count($items) : null),
                'gross_weight' => isset($validated['gross_weight']) && $validated['gross_weight'] !== '' ? (float) $validated['gross_weight'] : null,
                'net_weight' => isset($validated['net_weight']) && $validated['net_weight'] !== '' ? (float) $validated['net_weight'] : null,
                'total_packages' => (int) ($validated['total_packages'] ?? 0),
                'package_type' => $validated['package_type'] ?? 'Wooden Bundles / Pallets',
                'region' => $validated['region'] ?? ($consigner?->region ?: 'Gujarat'),
                'lut_no' => $validated['lut_no'] ?? null,
                'iso_certification' => $validated['iso_certification'] ?? null,

                'buyer_po' => $validated['buyer_po'] ?? '',
                'buyer_other' => [
                    'name' => $validated['buyer_name'] ?? '',
                    'address' => $validated['buyer_address'] ?? '',
                    'tax_code' => $validated['buyer_tax'] ?? '',
                    'phone' => $validated['buyer_phone'] ?? '',
                    'email' => $validated['buyer_email'] ?? '',
                ],
                'shipping_logistics' => [
                    'pre_carriage_by' => $validated['pre_carriage_by'] ?? '',
                    'place_of_receipt' => $validated['place_of_receipt'] ?? '',
                    'country_of_origin' => $validated['country_of_origin'] ?? '',
                    'country_of_destination' => $validated['country_of_destination'] ?? '',
                    'vessel_flight_no' => $validated['vessel_flight_no'] ?? '',
                    'port_of_loading' => $validated['port_of_loading'] ?? '',
                    'port_of_discharge' => $validated['port_of_discharge'] ?? '',
                    'final_destination' => $validated['final_destination'] ?? '',
                    'payment_terms' => $validated['payment_terms'] ?? '',
                ],
                'items' => $items,
                'total_quantity' => $calc['total_quantity'],
                'total_amount' => $calc['total_amount'],
                'rounded_amount' => $calc['rounded_amount'],
                'amount_in_words' => $calc['amount_in_words'],
                'status' => 'Active',
            ]);
        });

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Commercial Invoice #{$invoice->invoice_number} created successfully and ready for export!");
    }

    /**
     * Display a specific invoice for viewing or printing.
     */
    public function show(Invoice $invoice): View
    {
        $settings = InvoiceSetting::getSettings();
        $exporter = $this->buildExporterData($settings);
        $consigner = $invoice->consigner ?? ($invoice->consigner_id ? Consigner::find($invoice->consigner_id) : null);

        $invoiceData = array_merge([
            'invoice_number' => $invoice->invoice_number,
            'invoice_date' => $invoice->invoice_date,
            'invoice_format' => $invoice->invoice_format ?: 'LUT',
            'delivery_term' => $invoice->delivery_term ?: 'CIF',
            'currency' => $invoice->currency ?: 'USD',
            'exchange_rate' => $invoice->exchange_rate,
            'igst_rate' => $invoice->igst_rate ?: 18.00,
            'taxable_value_inr' => $invoice->taxable_value_inr,
            'igst_amount_inr' => $invoice->igst_amount_inr,
            'consignee_mode' => $invoice->consignee_mode ?: ($invoice->consignee_name === 'TO THE ORDER' ? 'to_the_order' : 'consigner'),
            'consignee_name' => $invoice->consignee_name ?: $invoice->consigner_name,
            'consignee_address' => $invoice->consignee_address,
            'consignee_tax' => $invoice->consignee_tax,
            'consignee_phone' => $invoice->consignee_phone,
            'consignee_email' => $invoice->consignee_email,
            'buyer_name' => $invoice->buyer_name ?: ($invoice->buyer_other['name'] ?? ''),
            'buyer_address' => $invoice->buyer_address ?: ($invoice->buyer_other['address'] ?? ''),
            'buyer_tax' => $invoice->buyer_tax ?: ($invoice->buyer_other['tax_code'] ?? ''),
            'buyer_phone' => $invoice->buyer_phone ?: ($invoice->buyer_other['phone'] ?? ''),
            'buyer_email' => $invoice->buyer_email ?: ($invoice->buyer_other['email'] ?? ''),
            'notify_parties' => $invoice->notify_parties ?: [],
            'container_type' => $invoice->container_type ?: '',
            'no_of_containers' => $invoice->no_of_containers ?: '',
            'gross_weight' => $invoice->gross_weight,
            'net_weight' => $invoice->net_weight,
            'total_packages' => $invoice->total_packages ?: 0,
            'package_type' => $invoice->package_type ?: '',
            'region' => $invoice->region ?: ($consigner?->region ?: 'Gujarat'),
            'container_number' => $invoice->container_number,
            'trailer_number' => $invoice->trailer_number,
            'buyer_po' => $invoice->buyer_po,
            'lut_no' => $invoice->lut_no ?: '',
            'iso_certification' => $invoice->iso_certification ?: '',
            'origin_clause' => $settings->origin_clause ?: 'GOODS ARE OF INDIAN ORIGIN',
            'declaration' => $settings->declaration,
        ], $invoice->shipping_logistics ?? []);

        $items = $invoice->items ?? [];
        $buyerOther = [
            'name' => $invoiceData['buyer_name'],
            'address' => $invoiceData['buyer_address'],
            'tax_code' => $invoiceData['buyer_tax'],
            'phone' => $invoiceData['buyer_phone'],
            'email' => $invoiceData['buyer_email'],
        ];
        $totalQuantity = $invoice->total_quantity;
        $totalAmount = $invoice->total_amount;
        $roundedAmount = $invoice->rounded_amount;
        $amountInWords = $invoice->amount_in_words;

        $bank = $this->buildBankData($settings, $consigner);
        $certifications = $this->buildCertificationsData($settings, $invoice);

        return view('invoices.show', [
            'invoiceModel' => $invoice,
            'invoice' => $invoiceData,
            'consigner' => $consigner,
            'exporter' => $exporter,
            'buyerOther' => $buyerOther,
            'items' => $items,
            'totalQuantity' => $totalQuantity,
            'totalAmount' => $totalAmount,
            'roundedAmount' => $roundedAmount,
            'amountInWords' => $amountInWords,
            'bank' => $bank,
            'certifications' => $certifications,
            'settings' => $settings,
        ]);
    }

    /**
     * Show the form for editing an existing commercial invoice.
     */
    public function edit(Invoice $invoice): View
    {
        $settings = InvoiceSetting::getSettings();
        $consigners = Consigner::orderBy('name', 'asc')->get();

        // If consignee_mode is to_the_order, do not default to first consigner
        $isToOrder = ($invoice->consignee_mode === 'to_the_order' || strtoupper($invoice->consignee_name ?? '') === 'TO THE ORDER');
        $selectedConsigner = $isToOrder ? null : ($invoice->consigner ?? ($invoice->consigner_id ? Consigner::find($invoice->consigner_id) : null));

        $exporter = $this->buildExporterData($settings);

        $invoiceData = array_merge([
            'invoice_number' => $invoice->invoice_number,
            'invoice_date' => $invoice->invoice_date,
            'invoice_format' => $invoice->invoice_format ?: 'LUT',
            'delivery_term' => $invoice->delivery_term ?: 'CIF',
            'currency' => $invoice->currency ?: 'USD',
            'exchange_rate' => $invoice->exchange_rate ?? 94.20,
            'igst_rate' => $invoice->igst_rate ?? 18.00,
            'taxable_value_inr' => $invoice->taxable_value_inr ?? 0.00,
            'igst_amount_inr' => $invoice->igst_amount_inr ?? 0.00,
            'consignee_mode' => $isToOrder ? 'to_the_order' : 'consigner',
            'consignee_name' => $invoice->consignee_name ?: $invoice->consigner_name,
            'consignee_address' => $invoice->consignee_address ?: ($selectedConsigner?->registered_address ?? $selectedConsigner?->factory_address),
            'consignee_tax' => $invoice->consignee_tax ?: ($selectedConsigner?->gstin ?? $selectedConsigner?->pan),
            'consignee_phone' => $invoice->consignee_phone ?: $selectedConsigner?->contact_phone,
            'consignee_email' => $invoice->consignee_email ?: $selectedConsigner?->contact_email,
            'buyer_name' => $invoice->buyer_name ?: ($invoice->buyer_other['name'] ?? ''),
            'buyer_address' => $invoice->buyer_address ?: ($invoice->buyer_other['address'] ?? ''),
            'buyer_tax' => $invoice->buyer_tax ?: ($invoice->buyer_other['tax_code'] ?? ''),
            'buyer_phone' => $invoice->buyer_phone ?: ($invoice->buyer_other['phone'] ?? ''),
            'buyer_email' => $invoice->buyer_email ?: ($invoice->buyer_other['email'] ?? ''),
            'notify_parties' => $invoice->notify_parties ?: [
                ['name' => '', 'address' => '', 'tax' => '', 'phone' => '', 'email' => ''],
                ['name' => '', 'address' => '', 'tax' => '', 'phone' => '', 'email' => ''],
                ['name' => '', 'address' => '', 'tax' => '', 'phone' => '', 'email' => ''],
                ['name' => '', 'address' => '', 'tax' => '', 'phone' => '', 'email' => ''],
            ],
            'container_type' => $invoice->container_type ?: '',
            'no_of_containers' => $invoice->no_of_containers ?: '',
            'gross_weight' => $invoice->gross_weight,
            'net_weight' => $invoice->net_weight,
            'total_packages' => $invoice->total_packages ?: 0,
            'package_type' => $invoice->package_type ?: '',
            'region' => $invoice->region ?: ($selectedConsigner?->region ?: 'Gujarat'),
            'container_number' => $invoice->container_number,
            'trailer_number' => $invoice->trailer_number,
            'buyer_po' => $invoice->buyer_po,
            'lut_no' => $invoice->lut_no ?: '',
            'iso_certification' => $invoice->iso_certification ?: '',
            'origin_clause' => $settings->origin_clause ?: 'GOODS ARE OF INDIAN ORIGIN',
            'declaration' => $settings->declaration,
        ], $invoice->shipping_logistics ?? []);

        $items = $invoice->items ?? [];
        $buyerOther = [
            'name' => $invoiceData['buyer_name'],
            'address' => $invoiceData['buyer_address'],
            'tax_code' => $invoiceData['buyer_tax'],
            'phone' => $invoiceData['buyer_phone'],
            'email' => $invoiceData['buyer_email'],
        ];
        $totalQuantity = $invoice->total_quantity;
        $totalAmount = $invoice->total_amount;
        $roundedAmount = $invoice->rounded_amount;
        $amountInWords = $invoice->amount_in_words;

        $bank = $this->buildBankData($settings, $selectedConsigner);
        $certifications = $this->buildCertificationsData($settings, $invoice);

        return view('invoices.edit', [
            'invoiceModel' => $invoice,
            'invoice' => $invoiceData,
            'consigners' => $consigners,
            'selectedConsigner' => $selectedConsigner,
            'exporter' => $exporter,
            'buyerOther' => $buyerOther,
            'notifyParties' => $invoiceData['notify_parties'],
            'items' => $items,
            'totalQuantity' => $totalQuantity,
            'totalAmount' => $totalAmount,
            'roundedAmount' => $roundedAmount,
            'amountInWords' => $amountInWords,
            'bank' => $bank,
            'certifications' => $certifications,
            'settings' => $settings,
        ]);
    }

    /**
     * Update an existing commercial invoice in database.
     */
    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_number' => ['required', 'string', 'max:100'],
            'invoice_date' => ['required', 'string', 'max:50'],
            'invoice_format' => ['required', 'in:LUT,IGST'],
            'delivery_term' => ['required', 'string', 'max:50'],
            'currency' => ['required', 'in:USD,INR'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0'],
            'igst_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'consignee_mode' => ['required', 'in:consigner,to_the_order'],
            'consigner_id' => ['nullable', 'exists:consigners,id'],
            'consignee_name' => ['nullable', 'string', 'max:255'],
            'consignee_address' => ['nullable', 'string'],
            'consignee_tax' => ['nullable', 'string', 'max:100'],
            'consignee_phone' => ['nullable', 'string', 'max:100'],
            'consignee_email' => ['nullable', 'string', 'max:100'],

            'buyer_name' => ['nullable', 'string', 'max:255'],
            'buyer_address' => ['nullable', 'string'],
            'buyer_tax' => ['nullable', 'string', 'max:100'],
            'buyer_phone' => ['nullable', 'string', 'max:100'],
            'buyer_email' => ['nullable', 'string', 'max:100'],

            'notify_parties_json' => ['nullable', 'string'],

            'container_type' => ['nullable', 'string', 'max:50'],
            'no_of_containers' => ['nullable', 'integer', 'min:1'],
            'gross_weight' => ['nullable', 'numeric', 'min:0'],
            'net_weight' => ['nullable', 'numeric', 'min:0'],
            'total_packages' => ['nullable', 'integer', 'min:0'],
            'package_type' => ['nullable', 'string', 'max:100'],
            'region' => ['nullable', 'string', 'max:50'],
            'lut_no' => ['nullable', 'string', 'max:255'],
            'iso_certification' => ['nullable', 'string', 'max:255'],

            'container_number' => ['nullable', 'string', 'max:100'],
            'trailer_number' => ['nullable', 'string', 'max:100'],
            'buyer_po' => ['nullable', 'string', 'max:100'],
            'pre_carriage_by' => ['nullable', 'string', 'max:100'],
            'place_of_receipt' => ['nullable', 'string', 'max:100'],
            'country_of_origin' => ['nullable', 'string', 'max:100'],
            'country_of_destination' => ['nullable', 'string', 'max:100'],
            'vessel_flight_no' => ['nullable', 'string', 'max:100'],
            'port_of_loading' => ['nullable', 'string', 'max:100'],
            'port_of_discharge' => ['nullable', 'string', 'max:100'],
            'final_destination' => ['nullable', 'string', 'max:100'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'items_json' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ]);

        $consigner = null;
        if ($validated['consignee_mode'] === 'consigner' && ! empty($validated['consigner_id'])) {
            $consigner = Consigner::find($validated['consigner_id']);
        }

        $items = ! empty($validated['items_json']) ? json_decode($validated['items_json'], true) : $invoice->items;
        if (! is_array($items)) {
            $items = $invoice->items ?? [];
        }

        $notifyParties = ! empty($validated['notify_parties_json']) ? json_decode($validated['notify_parties_json'], true) : ($invoice->notify_parties ?? []);
        if (! is_array($notifyParties)) {
            $notifyParties = [];
        }

        $calc = $this->calculationService->calculateTotals(
            $items,
            $validated['invoice_format'],
            $validated['currency'],
            ! empty($validated['exchange_rate']) ? (float) $validated['exchange_rate'] : null,
            ! empty($validated['igst_rate']) ? (float) $validated['igst_rate'] : 18.00
        );

        $consigneeName = ($validated['consignee_mode'] ?? 'consigner') === 'to_the_order'
            ? 'TO THE ORDER'
            : (($validated['consignee_name'] ?? '') ?: ($consigner?->name ?? $invoice->consignee_name));

        DB::transaction(function () use ($invoice, $validated, $consigner, $consigneeName, $items, $notifyParties, $calc) {
            $invoice->update([
                'invoice_number' => $validated['invoice_number'],
                'invoice_date' => $validated['invoice_date'],
                'invoice_format' => $validated['invoice_format'],
                'delivery_term' => $validated['delivery_term'],
                'currency' => $calc['currency'],
                'exchange_rate' => $calc['exchange_rate'],
                'igst_rate' => $calc['igst_rate'],
                'taxable_value_inr' => $calc['taxable_value_inr'],
                'igst_amount_inr' => $calc['igst_amount_inr'],

                'consignee_mode' => $validated['consignee_mode'],
                'consigner_id' => $consigner?->id ?? ($validated['consignee_mode'] === 'to_the_order' ? null : $invoice->consigner_id),
                'consigner_name' => $consigner?->name ?? $consigneeName,
                'consignee_name' => $consigneeName,
                'consignee_address' => $validated['consignee_mode'] === 'to_the_order' ? null : ($validated['consignee_address'] ?? $invoice->consignee_address),
                'consignee_tax' => $validated['consignee_mode'] === 'to_the_order' ? null : ($validated['consignee_tax'] ?? $invoice->consignee_tax),
                'consignee_phone' => $validated['consignee_mode'] === 'to_the_order' ? null : ($validated['consignee_phone'] ?? $invoice->consignee_phone),
                'consignee_email' => $validated['consignee_mode'] === 'to_the_order' ? null : ($validated['consignee_email'] ?? $invoice->consignee_email),

                'buyer_name' => $validated['buyer_name'] ?? $invoice->buyer_name,
                'buyer_address' => $validated['buyer_address'] ?? $invoice->buyer_address,
                'buyer_tax' => $validated['buyer_tax'] ?? $invoice->buyer_tax,
                'buyer_phone' => $validated['buyer_phone'] ?? $invoice->buyer_phone,
                'buyer_email' => $validated['buyer_email'] ?? $invoice->buyer_email,

                'notify_parties' => $notifyParties,

                'container_number' => ! empty($validated['container_number']) ? $validated['container_number'] : ($items[0]['container_no'] ?? $invoice->container_number),
                'trailer_number' => ! empty($validated['trailer_number']) ? $validated['trailer_number'] : ($items[0]['trailer_no'] ?? $invoice->trailer_number),
                'container_type' => $validated['container_type'] ?? $invoice->container_type,
                'no_of_containers' => !empty($validated['no_of_containers']) ? (int) $validated['no_of_containers'] : $invoice->no_of_containers,
                'gross_weight' => isset($validated['gross_weight']) && $validated['gross_weight'] !== '' ? (float) $validated['gross_weight'] : $invoice->gross_weight,
                'net_weight' => isset($validated['net_weight']) && $validated['net_weight'] !== '' ? (float) $validated['net_weight'] : $invoice->net_weight,
                'total_packages' => (int) ($validated['total_packages'] ?? $invoice->total_packages ?? 0),
                'package_type' => $validated['package_type'] ?? $invoice->package_type ?? 'Wooden Bundles / Pallets',
                'region' => $validated['region'] ?? $invoice->region,
                'lut_no' => $validated['lut_no'] ?? $invoice->lut_no,
                'iso_certification' => $validated['iso_certification'] ?? $invoice->iso_certification,

                'buyer_po' => $validated['buyer_po'] ?? $invoice->buyer_po,
                'buyer_other' => [
                    'name' => $validated['buyer_name'] ?? ($invoice->buyer_other['name'] ?? ''),
                    'address' => $validated['buyer_address'] ?? ($invoice->buyer_other['address'] ?? ''),
                    'tax_code' => $validated['buyer_tax'] ?? ($invoice->buyer_other['tax_code'] ?? ''),
                    'phone' => $validated['buyer_phone'] ?? ($invoice->buyer_other['phone'] ?? ''),
                    'email' => $validated['buyer_email'] ?? ($invoice->buyer_other['email'] ?? ''),
                ],
                'shipping_logistics' => [
                    'pre_carriage_by' => $validated['pre_carriage_by'] ?? ($invoice->shipping_logistics['pre_carriage_by'] ?? ''),
                    'place_of_receipt' => $validated['place_of_receipt'] ?? ($invoice->shipping_logistics['place_of_receipt'] ?? ''),
                    'country_of_origin' => $validated['country_of_origin'] ?? ($invoice->shipping_logistics['country_of_origin'] ?? ''),
                    'country_of_destination' => $validated['country_of_destination'] ?? ($invoice->shipping_logistics['country_of_destination'] ?? ''),
                    'vessel_flight_no' => $validated['vessel_flight_no'] ?? ($invoice->shipping_logistics['vessel_flight_no'] ?? ''),
                    'port_of_loading' => $validated['port_of_loading'] ?? ($invoice->shipping_logistics['port_of_loading'] ?? ''),
                    'port_of_discharge' => $validated['port_of_discharge'] ?? ($invoice->shipping_logistics['port_of_discharge'] ?? ''),
                    'final_destination' => $validated['final_destination'] ?? ($invoice->shipping_logistics['final_destination'] ?? ''),
                    'payment_terms' => $validated['payment_terms'] ?? ($invoice->shipping_logistics['payment_terms'] ?? ''),
                ],
                'items' => $items,
                'total_quantity' => $calc['total_quantity'],
                'total_amount' => $calc['total_amount'],
                'rounded_amount' => $calc['rounded_amount'],
                'amount_in_words' => $calc['amount_in_words'],
                'status' => $validated['status'] ?? $invoice->status,
            ]);
        });

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Invoice #{$invoice->invoice_number} updated successfully!");
    }

    /**
     * Display the commercial packing list for an invoice.
     */
    public function packingList(Invoice $invoice): View
    {
        $settings = InvoiceSetting::getSettings();
        $exporter = $this->buildExporterData($settings);
        $consigner = $invoice->consigner ?? ($invoice->consigner_id ? Consigner::find($invoice->consigner_id) : null);

        $invoiceData = array_merge([
            'invoice_number' => $invoice->invoice_number,
            'invoice_date' => $invoice->invoice_date,
            'invoice_format' => $invoice->invoice_format ?: 'LUT',
            'delivery_term' => $invoice->delivery_term ?: 'CIF',
            'currency' => $invoice->currency ?: 'USD',
            'consignee_mode' => $invoice->consignee_mode ?: ($invoice->consignee_name === 'TO THE ORDER' ? 'to_the_order' : 'consigner'),
            'consignee_name' => $invoice->consignee_name ?: $invoice->consigner_name,
            'consignee_address' => $invoice->consignee_address,
            'consignee_tax' => $invoice->consignee_tax,
            'consignee_phone' => $invoice->consignee_phone,
            'consignee_email' => $invoice->consignee_email,
            'buyer_name' => $invoice->buyer_name ?: ($invoice->buyer_other['name'] ?? ''),
            'buyer_address' => $invoice->buyer_address ?: ($invoice->buyer_other['address'] ?? ''),
            'buyer_tax' => $invoice->buyer_tax ?: ($invoice->buyer_other['tax_code'] ?? ''),
            'buyer_phone' => $invoice->buyer_phone ?: ($invoice->buyer_other['phone'] ?? ''),
            'buyer_email' => $invoice->buyer_email ?: ($invoice->buyer_other['email'] ?? ''),
            'notify_parties' => $invoice->notify_parties ?: [],
            'container_type' => $invoice->container_type ?: '',
            'no_of_containers' => $invoice->no_of_containers ?: '',
            'gross_weight' => $invoice->gross_weight,
            'net_weight' => $invoice->net_weight,
            'total_packages' => $invoice->total_packages ?: 0,
            'package_type' => $invoice->package_type ?: '',
            'region' => $invoice->region ?: ($consigner?->region ?: 'Gujarat'),
            'container_number' => $invoice->container_number,
            'trailer_number' => $invoice->trailer_number,
            'buyer_po' => $invoice->buyer_po,
            'lut_no' => $invoice->lut_no ?: '',
            'iso_certification' => $invoice->iso_certification ?: '',
        ], $invoice->shipping_logistics ?? []);

        $items = $invoice->items ?? [];
        $buyerOther = [
            'name' => $invoiceData['buyer_name'],
            'address' => $invoiceData['buyer_address'],
            'tax_code' => $invoiceData['buyer_tax'],
            'phone' => $invoiceData['buyer_phone'],
            'email' => $invoiceData['buyer_email'],
        ];

        $bank = $this->buildBankData($settings, $consigner);
        $certifications = $this->buildCertificationsData($settings, $invoice);

        return view('invoices.packing-list', [
            'invoiceModel' => $invoice,
            'invoice' => $invoiceData,
            'consigner' => $consigner,
            'exporter' => $exporter,
            'buyerOther' => $buyerOther,
            'items' => $items,
            'totalQuantity' => $invoice->total_quantity,
            'bank' => $bank,
            'certifications' => $certifications,
            'settings' => $settings,
            'packingListDetails' => $invoice->packing_list_details ?? [],
        ]);
    }

    /**
     * Delete an invoice from database.
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        $num = $invoice->invoice_number;
        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', "Commercial Invoice #{$num} was deleted successfully.");
    }

    // Helper functions
    private function buildExporterData(InvoiceSetting $settings): array
    {
        return [
            'name' => $settings->company_name,
            'tagline' => $settings->tagline,
            'address' => $settings->address,
            'phone' => $settings->phone,
            'email' => $settings->email,
            'website' => $settings->website,
            'iec' => $settings->iec,
            'gstin' => $settings->gstin,
            'pan' => $settings->pan,
            'arn' => $settings->lut_arn,
            'lut_rule' => $settings->lut_rule,
        ];
    }

    private function buildBankData(InvoiceSetting $settings, ?Consigner $consigner): array
    {
        return [
            'name' => $settings->bank_name,
            'branch' => $settings->bank_branch,
            'account_no' => $settings->bank_account_no,
            'swift_code' => $settings->bank_swift_code,
            'ad_code' => $consigner?->ad_code ?: $settings->bank_ad_code,
            'address' => $settings->bank_address,
        ];
    }

    private function buildCertificationsData(InvoiceSetting $settings, ?Invoice $invoice = null): array
    {
        return [
            'rcmc_fieo_no' => $settings->rcmc_fieo_no,
            'repc_no' => $settings->repc_no,
            'iso_certification' => $invoice?->iso_certification ?: $settings->iso_certification,
            'export_house_star' => $settings->export_house_star,
            'signatory_company' => $settings->signatory_company,
            'signatory_name' => $settings->signatory_name,
            'signatory_title' => $settings->signatory_title,
        ];
    }
}
