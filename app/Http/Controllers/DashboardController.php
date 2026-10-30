<?php

namespace App\Http\Controllers;

use App\Models\Consigner;
use App\Models\Document;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the main Control Tower Dashboard.
     */
    public function index(Request $request): View
    {
        $activeShipment = Shipment::with('consigner')
            ->where('shipment_number', 'SHP-1469')
            ->first() ?? Shipment::first();

        $documents = Document::orderBy('is_primary', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        $stats = [
            'total_consigners' => Consigner::count(),
            'active_consigners' => Consigner::where('status', 'Active')->count(),
            'morbi_plants' => Consigner::whereIn('region', ['Gujarat', 'Morbi'])->count(),
            'jodhpur_units' => Consigner::where('region', 'Jodhpur')->count(),
            'total_documents' => Document::count(),
        ];

        return view('dashboard', [
            'shipment' => $activeShipment,
            'documents' => $documents,
            'stats' => $stats,
        ]);
    }
}
