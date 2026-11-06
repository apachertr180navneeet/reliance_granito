<?php

namespace App\Http\Controllers;

use App\Models\Consignee;
use App\Models\InvoiceSetting;
use Database\Seeders\ConsignerSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConsigneeController extends Controller
{
    /**
     * Display a listing of consignees with server-side pagination.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Consignee::query();

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

        $consignees = $query->orderBy('id', 'asc')->paginate($perPage)->withQueryString();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($consignees);
        }

        $stats = [
            'total' => Consignee::count(),
            'active' => Consignee::where('status', 'Active')->count(),
            'morbi' => Consignee::whereIn('region', ['Gujarat', 'Morbi'])->count(),
            'jodhpur' => Consignee::where('region', 'Jodhpur')->count(),
        ];

        $isLegacy = str_starts_with($request->path(), 'consigners') || $request->routeIs('consigners.*');
        $viewName = $isLegacy ? 'consigners.index' : 'consignees.index';

        return view($viewName, [
            'consignees' => $consignees,
            'consigners' => $consignees, // backward compatibility for legacy templates
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
     * Show the form for creating a new consignee.
     */
    public function create(Request $request): View
    {
        $isLegacy = str_starts_with($request->path(), 'consigners') || $request->routeIs('consigners.*');
        $viewName = $isLegacy ? 'consigners.create' : 'consignees.create';

        return view($viewName);
    }

    /**
     * Store a newly created consignee in storage.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'entity_type' => ['nullable', 'string', 'max:100'],
            'iec' => ['nullable', 'string', 'max:20'],
            'iec_branch' => ['nullable', 'string', 'max:10'],
            'gstin' => ['nullable', 'string', 'max:25'],
            'tax_code' => ['nullable', 'string', 'max:100'],
            'pan' => ['nullable', 'string', 'max:20'],
            'ad_code' => ['nullable', 'string', 'max:25'],
            'forex_bank' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_branch' => ['nullable', 'string', 'max:255'],
            'bank_account_no' => ['nullable', 'string', 'max:100'],
            'bank_ifsc' => ['nullable', 'string', 'max:50'],
            'bank_swift' => ['nullable', 'string', 'max:50'],
            'bank_address' => ['nullable', 'string'],
            'registered_address' => ['required', 'string'],
            'factory_address' => ['nullable', 'string'],
            'region' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'string', 'max:100'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:100'],
            'status' => ['nullable', 'string', 'in:Active,Inactive'],
        ]);

        if (empty($validated['iec'])) {
            $validated['iec'] = 'ABEFR3842G';
        }
        if (! empty($validated['tax_code']) && empty($validated['gstin'])) {
            $validated['gstin'] = $validated['tax_code'];
        }
        if (! empty($validated['gstin']) && empty($validated['tax_code'])) {
            $validated['tax_code'] = $validated['gstin'];
        }
        if (! empty($validated['phone']) && empty($validated['contact_phone'])) {
            $validated['contact_phone'] = $validated['phone'];
        }
        if (! empty($validated['contact_phone']) && empty($validated['phone'])) {
            $validated['phone'] = $validated['contact_phone'];
        }
        if (! empty($validated['email']) && empty($validated['contact_email'])) {
            $validated['contact_email'] = $validated['email'];
        }
        if (! empty($validated['contact_email']) && empty($validated['email'])) {
            $validated['email'] = $validated['contact_email'];
        }
        if (empty($validated['bank_account_no'])) {
            $validated['bank_account_no'] = '777705052052';
        }

        // Auto-assign region if not provided
        if (empty($validated['region'])) {
            $address = ($validated['factory_address'] ?? '').' '.$validated['registered_address'];
            $validated['region'] = (stripos($address, 'Morbi') !== false || stripos($address, 'Gujarat') !== false) ? 'Gujarat' : 'Jodhpur';
        }

        // Generate next code
        $nextId = Consignee::max('id') + 1;
        $validated['consigner_code'] = 'EXP-'.str_pad((string) $nextId, 3, '0', STR_PAD_LEFT);

        $consignee = Consignee::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Consignee registered successfully in MySQL database.',
                'data' => $consignee,
            ], 201);
        }

        $redirectRoute = str_starts_with($request->path(), 'consigners') ? 'consigners.index' : 'consignees.index';

        return redirect()->route($redirectRoute)->with('success', 'Consignee registered successfully.');
    }

    /**
     * Display the specified consignee.
     */
    public function show(string|int|Consignee $consignee): JsonResponse
    {
        $consigneeModel = $consignee instanceof Consignee ? $consignee : Consignee::findOrFail($consignee);

        return response()->json($consigneeModel);
    }

    /**
     * Show the form for editing the specified consignee.
     */
    public function edit(string|int|Consignee $consignee): View
    {
        $consignee = $consignee instanceof Consignee ? $consignee : Consignee::findOrFail($consignee);
        $consigner = $consignee; // backward compatibility
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
            'name' => $consignee->bank_name ?: $settings->bank_name,
            'branch' => $consignee->bank_branch ?: $settings->bank_branch,
            'account_no' => $consignee->bank_account_no ?: ($settings->bank_account_no ?: '777705052052'),
            'swift_code' => $consignee->bank_swift ?: $settings->bank_swift_code,
            'ad_code' => $consignee->ad_code ?: $settings->bank_ad_code,
            'address' => $consignee->bank_address ?: $settings->bank_address,
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

        $isLegacy = str_starts_with(request()->path(), 'consigners') || request()->routeIs('consigners.*');
        $viewName = $isLegacy ? 'consigners.edit' : 'consignees.edit';

        return view($viewName, compact(
            'consignee',
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
     * Update the specified consignee in storage.
     */
    public function update(Request $request, string|int|Consignee $consignee): JsonResponse|RedirectResponse
    {
        $consignee = $consignee instanceof Consignee ? $consignee : Consignee::findOrFail($consignee);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'entity_type' => ['nullable', 'string', 'max:100'],
            'iec' => ['nullable', 'string', 'max:20'],
            'iec_branch' => ['nullable', 'string', 'max:10'],
            'gstin' => ['nullable', 'string', 'max:25'],
            'tax_code' => ['nullable', 'string', 'max:100'],
            'pan' => ['nullable', 'string', 'max:20'],
            'ad_code' => ['nullable', 'string', 'max:25'],
            'forex_bank' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_branch' => ['nullable', 'string', 'max:255'],
            'bank_account_no' => ['nullable', 'string', 'max:100'],
            'bank_ifsc' => ['nullable', 'string', 'max:50'],
            'bank_swift' => ['nullable', 'string', 'max:50'],
            'bank_address' => ['nullable', 'string'],
            'registered_address' => ['required', 'string'],
            'factory_address' => ['nullable', 'string'],
            'region' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'string', 'max:100'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:100'],
            'status' => ['nullable', 'string', 'in:Active,Inactive'],
        ]);

        if (empty($validated['iec'])) {
            $validated['iec'] = $consignee->iec ?: 'ABEFR3842G';
        }
        if (! empty($validated['tax_code']) && empty($validated['gstin'])) {
            $validated['gstin'] = $validated['tax_code'];
        }
        if (! empty($validated['gstin']) && empty($validated['tax_code'])) {
            $validated['tax_code'] = $validated['gstin'];
        }
        if (! empty($validated['phone']) && empty($validated['contact_phone'])) {
            $validated['contact_phone'] = $validated['phone'];
        }
        if (! empty($validated['contact_phone']) && empty($validated['phone'])) {
            $validated['phone'] = $validated['contact_phone'];
        }
        if (! empty($validated['email']) && empty($validated['contact_email'])) {
            $validated['contact_email'] = $validated['email'];
        }
        if (! empty($validated['contact_email']) && empty($validated['email'])) {
            $validated['email'] = $validated['contact_email'];
        }
        if (empty($validated['bank_account_no'])) {
            $validated['bank_account_no'] = $consignee->bank_account_no ?: '777705052052';
        }

        if (empty($validated['region'])) {
            $address = ($validated['factory_address'] ?? '').' '.$validated['registered_address'];
            $validated['region'] = (stripos($address, 'Morbi') !== false || stripos($address, 'Gujarat') !== false) ? 'Gujarat' : 'Jodhpur';
        }

        $consignee->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Consignee details updated successfully in MySQL database.',
                'data' => $consignee,
            ]);
        }

        $redirectRoute = str_starts_with($request->path(), 'consigners') ? 'consigners.index' : 'consignees.index';

        return redirect()->route($redirectRoute)->with('success', 'Consignee updated successfully.');
    }

    /**
     * Toggle or update consignee status via AJAX.
     */
    public function toggleStatus(Request $request, string|int|Consignee $consignee): JsonResponse
    {
        $consigneeModel = $consignee instanceof Consignee ? $consignee : Consignee::find($consignee);

        if (! $consigneeModel) {
            return response()->json([
                'success' => false,
                'message' => 'Consignee record not found or already removed.',
            ], 404);
        }

        $newStatus = $request->input('status');
        if (! in_array($newStatus, ['Active', 'Inactive'])) {
            $newStatus = $consigneeModel->status === 'Active' ? 'Inactive' : 'Active';
        }

        $consigneeModel->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => "Consignee status updated to {$newStatus}.",
            'status' => $newStatus,
            'data' => $consigneeModel,
            'stats' => [
                'total' => Consignee::count(),
                'active' => Consignee::where('status', 'Active')->count(),
            ],
        ]);
    }

    /**
     * Remove the specified consignee from storage.
     */
    public function destroy(Request $request, string|int|Consignee $consignee): JsonResponse|RedirectResponse
    {
        $consigneeModel = $consignee instanceof Consignee ? $consignee : Consignee::find($consignee);

        $isJson = $request->wantsJson()
            || $request->ajax()
            || $request->expectsJson()
            || $request->isJson()
            || str_contains((string) $request->header('Accept'), 'json')
            || $request->hasHeader('X-CSRF-TOKEN')
            || $request->hasHeader('X-HTTP-Method-Override');

        if (! $consigneeModel) {
            if ($isJson) {
                return response()->json([
                    'success' => true,
                    'message' => 'Consignee record already removed.',
                    'id' => is_numeric($consignee) ? (int) $consignee : null,
                    'total' => Consignee::count(),
                ]);
            }

            $redirectRoute = str_starts_with($request->path(), 'consigners') ? 'consigners.index' : 'consignees.index';

            return redirect()->route($redirectRoute)->with('success', 'Consignee record was already removed.');
        }

        $name = $consigneeModel->name;
        $id = $consigneeModel->id;
        $consigneeModel->delete();

        if ($isJson) {
            return response()->json([
                'success' => true,
                'message' => "Consignee {$name} deleted successfully.",
                'id' => $id,
                'total' => Consignee::count(),
                'active' => Consignee::where('status', 'Active')->count(),
            ]);
        }

        $redirectRoute = str_starts_with($request->path(), 'consigners') ? 'consigners.index' : 'consignees.index';

        return redirect()->route($redirectRoute)->with('success', "Consignee {$name} deleted successfully.");
    }

    /**
     * Export consignees to CSV or JSON format.
     */
    public function export(Request $request): StreamedResponse|JsonResponse
    {
        $format = $request->query('format', 'csv');
        $consignees = Consignee::all();

        if ($format === 'json') {
            return response()->json($consignees, 200, [
                'Content-Disposition' => 'attachment; filename="reliance_consignees_directory.json"',
            ]);
        }

        return response()->stream(function () use ($consignees) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Code', 'Company Name', 'Entity Type', 'IEC', 'Branch', 'GSTIN', 'PAN', 'AD Code', 'Bank', 'Region', 'Contact', 'Status']);

            foreach ($consignees as $c) {
                fputcsv($handle, [
                    $c->id,
                    $c->consignee_code ?? $c->consigner_code,
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
            'Content-Disposition' => 'attachment; filename="reliance_consignees_directory.csv"',
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
            'message' => 'Default verified consignees restored in MySQL database.',
            'data' => Consignee::all(),
        ]);
    }
}
