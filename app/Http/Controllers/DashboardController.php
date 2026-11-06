<?php

namespace App\Http\Controllers;

use App\Models\Consignee;
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
        $activeShipment = Shipment::with(['consignee', 'consigner'])
            ->where('shipment_number', 'SHP-1469')
            ->first() ?? Shipment::first();

        $documents = Document::orderBy('is_primary', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        $totalConsignees = Consignee::count();
        $activeConsignees = Consignee::where('status', 'Active')->count();
        $morbiPlants = Consignee::whereIn('region', ['Gujarat', 'Morbi'])->count();
        $jodhpurUnits = Consignee::where('region', 'Jodhpur')->count();

        $stats = [
            'total_consignees' => $totalConsignees,
            'active_consignees' => $activeConsignees,
            'total_consigners' => $totalConsignees,
            'active_consigners' => $activeConsignees,
            'morbi_plants' => $morbiPlants,
            'jodhpur_units' => $jodhpurUnits,
            'total_documents' => Document::count(),
        ];

        return view('dashboard', [
            'shipment' => $activeShipment,
            'documents' => $documents,
            'stats' => $stats,
        ]);
    }
}
