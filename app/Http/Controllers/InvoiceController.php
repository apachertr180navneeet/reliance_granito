<?php

namespace App\Http\Controllers;

use App\Models\Consigner;
use App\Models\Invoice;
use App\Models\InvoiceSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
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

        // Invoice Metadata & Shipment Specifics - ALL BLANK BY DEFAULT
        $invoice = [
            'invoice_number' => $invPrefix,
            'invoice_date' => $request->query('inv_date', now()->format('d.m.Y')),
            'lut_no' => $settings->lut_rule,
            'container_number' => $request->query('container', ''),
            'trailer_number' => $request->query('trailer', ''),
            'buyer_po' => $request->query('buyer_po', ''),
            'pre_carriage_by' => $request->query('pre_carriage', ''),
            'place_of_receipt' => $request->query('place_receipt', ''),
            'country_of_origin' => $request->query('country_origin', 'INDIA'),
            'country_of_destination' => $request->query('country_dest', ''),
            'vessel_flight_no' => $request->query('vessel', ''),
            'port_of_loading' => $request->query('port_loading', ''),
            'port_of_discharge' => $request->query('port_discharge', ''),
            'final_destination' => $request->query('final_dest', ''),
            'payment_terms' => $request->query('payment_terms', ''),
            'origin_clause' => $settings->origin_clause ?: 'GOODS ARE OF INDIAN ORIGIN',
            'declaration' => $settings->declaration,
            'currency' => 'INR',
        ];

        // Buyer (If other than Consignee) - Specific to Invoice (ALL BLANK)
        $buyerOther = [
            'name' => $request->query('buyer_other_name', ''),
            'address' => $request->query('buyer_other_address', ''),
            'tax_code' => $request->query('buyer_other_tax', ''),
            'phone' => $request->query('buyer_other_phone', ''),
            'email' => $request->query('buyer_other_email', ''),
        ];

        // Line Items Table - Clean empty row ready for user entry
        $items = [
            [
                'item_no' => 1,
                'marks_numbers' => '',
                'container_no' => '',
                'trailer_no' => '',
                'description' => '',
                'hsn_code' => '68022390',
                'quantity' => 0,
                'rate' => 0,
                'amount' => 0,
            ],
        ];

        // Calculate Totals
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
            'consigner_id' => ['nullable', 'exists:consigners,id'],
            'container_number' => ['nullable', 'string', 'max:100'],
            'trailer_number' => ['nullable', 'string', 'max:100'],
            'buyer_po' => ['nullable', 'string', 'max:100'],
            'buyer_other_name' => ['nullable', 'string', 'max:255'],
            'buyer_other_address' => ['nullable', 'string'],
            'buyer_other_tax' => ['nullable', 'string', 'max:100'],
            'buyer_other_phone' => ['nullable', 'string', 'max:50'],
            'buyer_other_email' => ['nullable', 'string', 'max:100'],
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
            'total_quantity' => ['nullable', 'numeric'],
            'total_amount' => ['nullable', 'numeric'],
            'rounded_amount' => ['nullable', 'numeric'],
            'amount_in_words' => ['nullable', 'string'],
        ]);

        $consigner = ! empty($validated['consigner_id']) ? Consigner::find($validated['consigner_id']) : null;

        // Parse items JSON
        $items = ! empty($validated['items_json']) ? json_decode($validated['items_json'], true) : [];

        $invoice = Invoice::create([
            'invoice_number' => $validated['invoice_number'],
            'invoice_date' => $validated['invoice_date'],
            'consigner_id' => $consigner?->id,
            'consigner_name' => $consigner?->name ?? '',
            'container_number' => $validated['container_number'] ?? '',
            'trailer_number' => $validated['trailer_number'] ?? '',
            'buyer_po' => $validated['buyer_po'] ?? '',
            'buyer_other' => [
                'name' => $validated['buyer_other_name'] ?? '',
                'address' => $validated['buyer_other_address'] ?? '',
                'tax_code' => $validated['buyer_other_tax'] ?? '',
                'phone' => $validated['buyer_other_phone'] ?? '',
                'email' => $validated['buyer_other_email'] ?? '',
            ],
            'shipping_logistics' => [
                'pre_carriage_by' => $validated['pre_carriage_by'] ?? '',
                'place_of_receipt' => $validated['place_of_receipt'] ?? '',
                'country_of_origin' => $validated['country_of_origin'] ?? 'INDIA',
                'country_of_destination' => $validated['country_of_destination'] ?? '',
                'vessel_flight_no' => $validated['vessel_flight_no'] ?? '',
                'port_of_loading' => $validated['port_of_loading'] ?? '',
                'port_of_discharge' => $validated['port_of_discharge'] ?? '',
                'final_destination' => $validated['final_destination'] ?? '',
                'payment_terms' => $validated['payment_terms'] ?? '',
            ],
            'items' => $items,
            'total_quantity' => floatval($validated['total_quantity'] ?? array_sum(array_column($items, 'quantity'))),
            'total_amount' => floatval($validated['total_amount'] ?? array_sum(array_column($items, 'amount'))),
            'rounded_amount' => floatval($validated['rounded_amount'] ?? round(array_sum(array_column($items, 'amount')))),
            'amount_in_words' => $validated['amount_in_words'] ?? '',
            'currency' => 'INR',
            'status' => 'Active',
        ]);

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
            'container_number' => $invoice->container_number,
            'trailer_number' => $invoice->trailer_number,
            'buyer_po' => $invoice->buyer_po,
            'lut_no' => $settings->lut_rule,
            'origin_clause' => $settings->origin_clause ?: 'GOODS ARE OF INDIAN ORIGIN',
            'declaration' => $settings->declaration,
            'currency' => $invoice->currency ?: 'INR',
        ], $invoice->shipping_logistics ?? []);

        $items = $invoice->items ?? [];
        $buyerOther = $invoice->buyer_other ?? [];
        $totalQuantity = $invoice->total_quantity;
        $totalAmount = $invoice->total_amount;
        $roundedAmount = $invoice->rounded_amount;
        $amountInWords = $invoice->amount_in_words;

        $bank = $this->buildBankData($settings, $consigner);
        $certifications = $this->buildCertificationsData($settings);

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
        $selectedConsigner = $invoice->consigner ?? $consigners->first();
        $exporter = $this->buildExporterData($settings);

        $invoiceData = array_merge([
            'invoice_number' => $invoice->invoice_number,
            'invoice_date' => $invoice->invoice_date,
            'container_number' => $invoice->container_number,
            'trailer_number' => $invoice->trailer_number,
            'buyer_po' => $invoice->buyer_po,
            'lut_no' => $settings->lut_rule,
            'origin_clause' => $settings->origin_clause ?: 'GOODS ARE OF INDIAN ORIGIN',
            'declaration' => $settings->declaration,
            'currency' => $invoice->currency ?: 'INR',
        ], $invoice->shipping_logistics ?? []);

        $items = $invoice->items ?? [];
        $buyerOther = $invoice->buyer_other ?? [];
        $totalQuantity = $invoice->total_quantity;
        $totalAmount = $invoice->total_amount;
        $roundedAmount = $invoice->rounded_amount;
        $amountInWords = $invoice->amount_in_words;

        $bank = $this->buildBankData($settings, $selectedConsigner);
        $certifications = $this->buildCertificationsData($settings);

        return view('invoices.edit', [
            'invoiceModel' => $invoice,
            'invoice' => $invoiceData,
            'consigners' => $consigners,
            'selectedConsigner' => $selectedConsigner,
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
     * Update an existing commercial invoice in database.
     */
    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_number' => ['required', 'string', 'max:100'],
            'invoice_date' => ['required', 'string', 'max:50'],
            'consigner_id' => ['nullable', 'exists:consigners,id'],
            'container_number' => ['nullable', 'string', 'max:100'],
            'trailer_number' => ['nullable', 'string', 'max:100'],
            'buyer_po' => ['nullable', 'string', 'max:100'],
            'buyer_other_name' => ['nullable', 'string', 'max:255'],
            'buyer_other_address' => ['nullable', 'string'],
            'buyer_other_tax' => ['nullable', 'string', 'max:100'],
            'buyer_other_phone' => ['nullable', 'string', 'max:50'],
            'buyer_other_email' => ['nullable', 'string', 'max:100'],
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
            'total_quantity' => ['nullable', 'numeric'],
            'total_amount' => ['nullable', 'numeric'],
            'rounded_amount' => ['nullable', 'numeric'],
            'amount_in_words' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ]);

        $consigner = ! empty($validated['consigner_id']) ? Consigner::find($validated['consigner_id']) : null;

        $items = ! empty($validated['items_json']) ? json_decode($validated['items_json'], true) : $invoice->items;

        $invoice->update([
            'invoice_number' => $validated['invoice_number'],
            'invoice_date' => $validated['invoice_date'],
            'consigner_id' => $consigner?->id ?? $invoice->consigner_id,
            'consigner_name' => $consigner?->name ?? $invoice->consigner_name,
            'container_number' => $validated['container_number'] ?? $invoice->container_number,
            'trailer_number' => $validated['trailer_number'] ?? $invoice->trailer_number,
            'buyer_po' => $validated['buyer_po'] ?? $invoice->buyer_po,
            'buyer_other' => [
                'name' => $validated['buyer_other_name'] ?? ($invoice->buyer_other['name'] ?? ''),
                'address' => $validated['buyer_other_address'] ?? ($invoice->buyer_other['address'] ?? ''),
                'tax_code' => $validated['buyer_other_tax'] ?? ($invoice->buyer_other['tax_code'] ?? ''),
                'phone' => $validated['buyer_other_phone'] ?? ($invoice->buyer_other['phone'] ?? ''),
                'email' => $validated['buyer_other_email'] ?? ($invoice->buyer_other['email'] ?? ''),
            ],
            'shipping_logistics' => [
                'pre_carriage_by' => $validated['pre_carriage_by'] ?? ($invoice->shipping_logistics['pre_carriage_by'] ?? ''),
                'place_of_receipt' => $validated['place_of_receipt'] ?? ($invoice->shipping_logistics['place_of_receipt'] ?? ''),
                'country_of_origin' => $validated['country_of_origin'] ?? ($invoice->shipping_logistics['country_of_origin'] ?? 'INDIA'),
                'country_of_destination' => $validated['country_of_destination'] ?? ($invoice->shipping_logistics['country_of_destination'] ?? ''),
                'vessel_flight_no' => $validated['vessel_flight_no'] ?? ($invoice->shipping_logistics['vessel_flight_no'] ?? ''),
                'port_of_loading' => $validated['port_of_loading'] ?? ($invoice->shipping_logistics['port_of_loading'] ?? ''),
                'port_of_discharge' => $validated['port_of_discharge'] ?? ($invoice->shipping_logistics['port_of_discharge'] ?? ''),
                'final_destination' => $validated['final_destination'] ?? ($invoice->shipping_logistics['final_destination'] ?? ''),
                'payment_terms' => $validated['payment_terms'] ?? ($invoice->shipping_logistics['payment_terms'] ?? ''),
            ],
            'items' => $items,
            'total_quantity' => floatval($validated['total_quantity'] ?? $invoice->total_quantity),
            'total_amount' => floatval($validated['total_amount'] ?? $invoice->total_amount),
            'rounded_amount' => floatval($validated['rounded_amount'] ?? $invoice->rounded_amount),
            'amount_in_words' => $validated['amount_in_words'] ?? $invoice->amount_in_words,
            'status' => $validated['status'] ?? $invoice->status,
        ]);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Invoice #{$invoice->invoice_number} updated successfully!");
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

    private function buildCertificationsData(InvoiceSetting $settings): array
    {
        return [
            'rcmc_fieo_no' => $settings->rcmc_fieo_no,
            'repc_no' => $settings->repc_no,
            'iso_certification' => $settings->iso_certification,
            'export_house_star' => $settings->export_house_star,
            'signatory_company' => $settings->signatory_company,
            'signatory_name' => $settings->signatory_name,
            'signatory_title' => $settings->signatory_title,
        ];
    }
}
