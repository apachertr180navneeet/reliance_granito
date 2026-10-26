<?php

namespace App\Http\Controllers;

use App\Models\InvoiceSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceSettingController extends Controller
{
    /**
     * Show the configuration settings form for Commercial Invoices.
     */
    public function edit(): View
    {
        $setting = InvoiceSetting::getSettings();

        return view('invoices.settings', compact('setting'));
    }

    /**
     * Update the Commercial Invoice settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $setting = InvoiceSetting::getSettings();

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'iec' => ['required', 'string', 'max:50'],
            'gstin' => ['required', 'string', 'max:50'],
            'pan' => ['required', 'string', 'max:50'],
            'lut_arn' => ['nullable', 'string', 'max:100'],
            'lut_rule' => ['nullable', 'string'],

            'bank_name' => ['required', 'string', 'max:255'],
            'bank_branch' => ['nullable', 'string', 'max:255'],
            'bank_account_no' => ['required', 'string', 'max:100'],
            'bank_swift_code' => ['required', 'string', 'max:50'],
            'bank_ad_code' => ['nullable', 'string', 'max:50'],
            'bank_address' => ['nullable', 'string'],

            'invoice_prefix' => ['nullable', 'string', 'max:100'],
            'pre_carriage_by' => ['nullable', 'string', 'max:100'],
            'place_of_receipt' => ['nullable', 'string', 'max:100'],
            'country_of_origin' => ['nullable', 'string', 'max:100'],
            'default_port_of_loading' => ['nullable', 'string', 'max:100'],
            'default_port_of_discharge' => ['nullable', 'string', 'max:100'],
            'default_final_destination' => ['nullable', 'string', 'max:100'],
            'default_payment_terms' => ['nullable', 'string', 'max:255'],
            'origin_clause' => ['nullable', 'string', 'max:255'],
            'declaration' => ['nullable', 'string'],
            'currency' => ['required', 'string', 'max:10'],

            'rcmc_fieo_no' => ['nullable', 'string', 'max:100'],
            'repc_no' => ['nullable', 'string', 'max:100'],
            'iso_certification' => ['nullable', 'string', 'max:100'],
            'export_house_star' => ['nullable', 'string', 'max:100'],
            'signatory_company' => ['nullable', 'string', 'max:255'],
            'signatory_name' => ['nullable', 'string', 'max:255'],
            'signatory_title' => ['nullable', 'string', 'max:255'],
        ]);

        $setting->update($validated);

        return redirect()->route('invoices.settings')
            ->with('success', 'Commercial Invoice settings updated successfully! All invoice sections and previews are now in sync.');
    }
}
