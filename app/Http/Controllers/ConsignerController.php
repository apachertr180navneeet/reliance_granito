<?php

namespace App\Http\Controllers;

use App\Models\Consigner;
use App\Models\InvoiceSetting;
use Database\Seeders\ConsignerSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConsignerController extends Controller
{
    /**
     * Display a listing of consigners with server-side pagination.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Consigner::query();

        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('iec', 'like', "%{$term}%")
                    ->orWhere('gstin', 'like', "%{$term}%")
                    ->orWhere('ad_code', 'like', "%{$term}%")
                    ->orWhere('region', 'like', "%{$term}%")
                    ->orWhere('registered_address', 'like', "%{$term}%")
                    ->orWhere('factory_address', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'ALL') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('region') && $request->input('region') !== 'ALL') {
            $query->where('region', $request->input('region'));
        }

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [5, 10, 25, 50])) {
            $perPage = 10;
        }

        $consigners = $query->orderBy('id', 'asc')->paginate($perPage)->withQueryString();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($consigners);
        }

        $stats = [
            'total' => Consigner::count(),
            'active' => Consigner::where('status', 'Active')->count(),
            'morbi' => Consigner::whereIn('region', ['Gujarat', 'Morbi'])->count(),
            'jodhpur' => Consigner::where('region', 'Jodhpur')->count(),
        ];

        return view('consigners.index', [
            'consigners' => $consigners,
            'stats' => $stats,
            'filters' => [
                'search' => $request->input('search', ''),
                'region' => $request->input('region', 'ALL'),
                'status' => $request->input('status', 'ALL'),
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Show the form for creating a new consigner.
     */
    public function create(): View
    {
        return view('consigners.create');
    }

    /**
     * Store a newly created consigner in storage.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'entity_type' => ['nullable', 'string', 'max:100'],
            'iec' => ['required', 'string', 'max:20'],
            'iec_branch' => ['nullable', 'string', 'max:10'],
            'gstin' => ['required', 'string', 'max:25'],
            'pan' => ['nullable', 'string', 'max:20'],
            'ad_code' => ['nullable', 'string', 'max:25'],
            'forex_bank' => ['nullable', 'string', 'max:255'],
            'registered_address' => ['required', 'string'],
            'factory_address' => ['nullable', 'string'],
            'region' => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:100'],
            'status' => ['nullable', 'string', 'in:Active,Inactive'],
        ]);

        // Auto-assign region if not provided
        if (empty($validated['region'])) {
            $address = ($validated['factory_address'] ?? '').' '.$validated['registered_address'];
            $validated['region'] = (stripos($address, 'Morbi') !== false || stripos($address, 'Gujarat') !== false) ? 'Gujarat' : 'Jodhpur';
        }

        // Generate next code
        $nextId = Consigner::max('id') + 1;
        $validated['consigner_code'] = 'EXP-'.str_pad((string) $nextId, 3, '0', STR_PAD_LEFT);

        $consigner = Consigner::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Consigner registered successfully in MySQL database.',
                'data' => $consigner,
            ], 201);
        }

        return redirect()->route('consigners.index')->with('success', 'Consigner registered successfully.');
    }

    /**
     * Display the specified consigner.
     */
    public function show(string|int|Consigner $consigner): JsonResponse
    {
        $consignerModel = $consigner instanceof Consigner ? $consigner : Consigner::findOrFail($consigner);

        return response()->json($consignerModel);
    }

    /**
     * Show the form for editing the specified consigner.
     */
    public function edit(string|int|Consigner $consigner): View
    {
        $consigner = $consigner instanceof Consigner ? $consigner : Consigner::findOrFail($consigner);
        $settings = InvoiceSetting::getSettings();

        $exporter = [
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

        $invoice = [
            'invoice_number' => $settings->invoice_prefix,
            'invoice_date' => now()->format('d.m.Y'),
            'lut_no' => $settings->lut_rule,
            'container_number' => 'DFSU3044515',
            'trailer_number' => 'TN02BB9434',
            'buyer_po' => '-',
            'pre_carriage_by' => $settings->pre_carriage_by,
            'place_of_receipt' => $settings->place_of_receipt,
            'country_of_origin' => $settings->country_of_origin,
            'country_of_destination' => $settings->default_final_destination,
            'vessel_flight_no' => 'MSC RAVEL V.1469W',
            'port_of_loading' => $settings->default_port_of_loading,
            'port_of_discharge' => $settings->default_port_of_discharge,
            'final_destination' => $settings->default_final_destination,
            'payment_terms' => $settings->default_payment_terms,
            'origin_clause' => $settings->origin_clause,
            'declaration' => $settings->declaration,
            'no_of_containers' => 1,
            'package_summary' => '20 FCL ( 1 )',
            'gross_weight' => '26500 KGS',
            'net_weight' => '25500 KGS',
            'currency' => $settings->currency,
        ];

        $items = [
            [
                'item_no' => 1,
                'marks_numbers' => '20 FCL ( 1 )',
                'container_no' => 'DFSU3044515',
                'trailer_no' => 'TN02BB9434',
                'description' => "NATURAL POLISHED GRANITE SLABS\nSIZE: Thick 18 mm x (90 - 210 cm x (60 - 100 cm))",
                'hsn_code' => '68022390',
                'quantity' => 430.350,
                'uom' => 'SQM.',
                'rate' => 13.250,
                'amount' => 5702.09,
            ],
        ];

        $totalQuantity = array_sum(array_column($items, 'quantity'));
        $totalAmount = array_sum(array_column($items, 'amount'));
        $roundedAmount = round($totalAmount);
        $amountInWords = 'FIVE THOUSAND SEVEN HUNDRED & TWO RUPEES ONLY';

        $bank = [
            'name' => $settings->bank_name,
            'branch' => $settings->bank_branch,
            'account_no' => $settings->bank_account_no,
            'swift_code' => $settings->bank_swift_code,
            'ad_code' => $consigner->ad_code ?: $settings->bank_ad_code,
            'address' => $settings->bank_address,
        ];

        $certifications = [
            'rcmc_fieo_no' => $settings->rcmc_fieo_no,
            'repc_no' => $settings->repc_no,
            'iso_certification' => $settings->iso_certification,
            'export_house_star' => $settings->export_house_star,
            'signatory_company' => $settings->signatory_company,
            'signatory_name' => $settings->signatory_name,
            'signatory_title' => $settings->signatory_title,
        ];

        $buyerOther = [
            'name' => 'GSGWEBSOFT',
            'address' => 'Plot no. 16 hanuman nagar',
            'tax_code' => 'Rajasthan',
            'phone' => '46541564156',
            'email' => 'navneetgehlot03061993@gmail.com',
        ];

        return view('consigners.edit', compact(
            'consigner',
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
     * Update the specified consigner in storage.
     */
    public function update(Request $request, string|int|Consigner $consigner): JsonResponse|RedirectResponse
    {
        $consigner = $consigner instanceof Consigner ? $consigner : Consigner::findOrFail($consigner);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'entity_type' => ['nullable', 'string', 'max:100'],
            'iec' => ['required', 'string', 'max:20'],
            'iec_branch' => ['nullable', 'string', 'max:10'],
            'gstin' => ['required', 'string', 'max:25'],
            'pan' => ['nullable', 'string', 'max:20'],
            'ad_code' => ['nullable', 'string', 'max:25'],
            'forex_bank' => ['nullable', 'string', 'max:255'],
            'registered_address' => ['required', 'string'],
            'factory_address' => ['nullable', 'string'],
            'region' => ['nullable', 'string', 'max:50'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:100'],
            'status' => ['nullable', 'string', 'in:Active,Inactive'],
        ]);

        if (empty($validated['region'])) {
            $address = ($validated['factory_address'] ?? '').' '.$validated['registered_address'];
            $validated['region'] = (stripos($address, 'Morbi') !== false || stripos($address, 'Gujarat') !== false) ? 'Gujarat' : 'Jodhpur';
        }

        $consigner->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Consigner details updated successfully in MySQL database.',
                'data' => $consigner,
            ]);
        }

        return redirect()->route('consigners.index')->with('success', 'Consigner updated successfully.');
    }

    /**
     * Toggle or update consigner status via AJAX.
     */
    public function toggleStatus(Request $request, string|int|Consigner $consigner): JsonResponse
    {
        $consignerModel = $consigner instanceof Consigner ? $consigner : Consigner::find($consigner);

        if (! $consignerModel) {
            return response()->json([
                'success' => false,
                'message' => 'Consigner record not found or already removed.',
            ], 404);
        }

        $newStatus = $request->input('status');
        if (! in_array($newStatus, ['Active', 'Inactive'])) {
            $newStatus = $consignerModel->status === 'Active' ? 'Inactive' : 'Active';
        }

        $consignerModel->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => "Consigner status updated to {$newStatus}.",
            'status' => $newStatus,
            'data' => $consignerModel,
            'stats' => [
                'total' => Consigner::count(),
                'active' => Consigner::where('status', 'Active')->count(),
            ],
        ]);
    }

    /**
     * Remove the specified consigner from storage.
     */
    public function destroy(Request $request, string|int|Consigner $consigner): JsonResponse|RedirectResponse
    {
        $consignerModel = $consigner instanceof Consigner ? $consigner : Consigner::find($consigner);

        $isJson = $request->wantsJson()
            || $request->ajax()
            || $request->expectsJson()
            || $request->isJson()
            || str_contains((string) $request->header('Accept'), 'json')
            || $request->hasHeader('X-CSRF-TOKEN')
            || $request->hasHeader('X-HTTP-Method-Override');

        if (! $consignerModel) {
            if ($isJson) {
                return response()->json([
                    'success' => true,
                    'message' => 'Consigner record already removed.',
                    'id' => is_numeric($consigner) ? (int) $consigner : null,
                    'total' => Consigner::count(),
                ]);
            }

            return redirect()->route('consigners.index')->with('success', 'Consigner record was already removed.');
        }

        $name = $consignerModel->name;
        $id = $consignerModel->id;
        $consignerModel->delete();

        if ($isJson) {
            return response()->json([
                'success' => true,
                'message' => "Consigner {$name} deleted successfully.",
                'id' => $id,
                'total' => Consigner::count(),
                'active' => Consigner::where('status', 'Active')->count(),
            ]);
        }

        return redirect()->route('consigners.index')->with('success', "Consigner {$name} deleted successfully.");
    }

    /**
     * Export consigners to CSV or JSON format.
     */
    public function export(Request $request): StreamedResponse|JsonResponse
    {
        $format = $request->query('format', 'csv');
        $consigners = Consigner::all();

        if ($format === 'json') {
            return response()->json($consigners, 200, [
                'Content-Disposition' => 'attachment; filename="reliance_consigners_directory.json"',
            ]);
        }

        // CSV export stream
        return response()->stream(function () use ($consigners) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Code', 'Company Name', 'Entity Type', 'IEC', 'Branch', 'GSTIN', 'PAN', 'AD Code', 'Bank', 'Region', 'Contact', 'Status']);

            foreach ($consigners as $c) {
                fputcsv($handle, [
                    $c->id,
                    $c->consigner_code,
                    $c->name,
                    $c->entity_type,
                    $c->iec,
                    $c->iec_branch,
                    $c->gstin,
                    $c->pan,
                    $c->ad_code,
                    $c->forex_bank,
                    $c->region,
                    $c->contact_person,
                    $c->status,
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="reliance_consigners_directory.csv"',
        ]);
    }

    /**
     * Reset Demo Data from Seeder.
     */
    public function resetDemo(): JsonResponse
    {
        (new ConsignerSeeder)->run();

        return response()->json([
            'success' => true,
            'message' => 'Default verified consigners restored in MySQL database.',
            'data' => Consigner::all(),
        ]);
    }
}
