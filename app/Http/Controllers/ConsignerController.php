<?php

namespace App\Http\Controllers;

use App\Models\Consigner;
use Database\Seeders\ConsignerSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConsignerController extends Controller
{
    /**
     * Display a listing of consigners or return JSON.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Consigner::query();

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('iec', 'like', "%{$term}%")
                    ->orWhere('gstin', 'like', "%{$term}%")
                    ->orWhere('ad_code', 'like', "%{$term}%")
                    ->orWhere('region', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'ALL') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('region') && $request->input('region') !== 'ALL') {
            $query->where('region', $request->input('region'));
        }

        $consigners = $query->orderBy('id', 'asc')->get();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($consigners);
        }

        $stats = [
            'total' => Consigner::count(),
            'active' => Consigner::where('status', 'Active')->count(),
            'morbi' => Consigner::where('region', 'Morbi')->count(),
            'jodhpur' => Consigner::where('region', 'Jodhpur')->count(),
        ];

        return view('consigners.index', [
            'consigners' => $consigners,
            'stats' => $stats,
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
            $validated['region'] = stripos($address, 'Morbi') !== false ? 'Morbi' : 'Jodhpur';
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
    public function show(Consigner $consigner): JsonResponse
    {
        return response()->json($consigner);
    }

    /**
     * Show the form for editing the specified consigner.
     */
    public function edit(Consigner $consigner): View
    {
        return view('consigners.edit', compact('consigner'));
    }

    /**
     * Update the specified consigner in storage.
     */
    public function update(Request $request, Consigner $consigner): JsonResponse|RedirectResponse
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

        if (empty($validated['region'])) {
            $address = ($validated['factory_address'] ?? '').' '.$validated['registered_address'];
            $validated['region'] = stripos($address, 'Morbi') !== false ? 'Morbi' : 'Jodhpur';
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
     * Remove the specified consigner from storage.
     */
    public function destroy(Request $request, Consigner $consigner): JsonResponse|RedirectResponse
    {
        $name = $consigner->name;
        $consigner->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Consigner {$name} deleted successfully from MySQL.",
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
