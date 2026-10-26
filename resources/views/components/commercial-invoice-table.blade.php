@props([
  'exporter' => [],
  'invoice' => [],
  'consigner' => null,
  'buyerOther' => [],
  'items' => [],
  'totalQuantity' => 0,
  'totalAmount' => 0,
  'roundedAmount' => 0,
  'amountInWords' => '',
  'bank' => [],
  'certifications' => [],
  'settings' => null,
  'editable' => false
])

<div class="commercial-invoice-sheet bg-white p-3 p-md-4 shadow-sm border" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #000; max-width: 960px; margin: 0 auto; background: #fff;">
  
  <!-- Outer Table Shell -->
  <table class="table-invoice-main w-100" style="border: 2px solid #000; border-collapse: collapse; font-size: 11px; line-height: 1.35; width: 100%;">
    
    <!-- Top Header Title Row -->
    <thead>
      <tr style="border-bottom: 2px solid #000;">
        <th colspan="7" class="p-2 text-center" style="background: #fff; color: #000;">
          <div class="d-flex align-items-center justify-content-between px-2">
            <div class="d-flex align-items-center gap-2" style="min-width: 140px; text-align: left;">
              <div style="width: 32px; height: 32px; border: 2px solid #0284c7; display: flex; align-items: center; justify-content: center; font-weight: 900; color: #0284c7; font-size: 14px; clip-path: polygon(50% 0%, 0% 100%, 100% 100%);">
                ▲
              </div>
              <div style="font-size: 9px; font-weight: 800; line-height: 1.1; color: #0369a1;">
                RELIANCE GRANITO
              </div>
            </div>

            <div class="text-center flex-grow-1">
              <div class="fw-black text-uppercase" style="font-size: 16px; letter-spacing: 0.08em; font-weight: 900; color: #000;">
                COMMERCIAL INVOICE
              </div>
              <div style="font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; color: #111;">
                SUPPLY MEANT FOR EXPORT UNDER BOND OR LETTER OF UNDERTAKING WITHOUT PAYMENT OF IGST
              </div>
            </div>

            <div style="min-width: 140px; text-align: right; font-size: 9px;" class="d-none d-sm-block">
              <span class="badge border border-dark text-dark fw-bold" style="font-size: 9px;">FORM RG-EXP-01</span>
            </div>
          </div>
        </th>
      </tr>
    </thead>

    <tbody>
      <!-- Exporter Particulars & Invoice Metadata Block -->
      <tr style="border-bottom: 1.5px solid #000;">
        <!-- Left: Exporter Identity -->
        <td colspan="4" class="p-2 align-top" style="width: 52%; border-right: 1.5px solid #000;">
          <div class="text-decoration-underline fw-bold mb-1" style="font-size: 11px;">Exporter:</div>
          <div class="fw-black fs-6 text-uppercase mb-1" style="font-weight: 900; letter-spacing: 0.02em;">
            {{ $exporter['name'] ?? 'RELIANCE GRANITO' }}
          </div>
          <div style="font-size: 10px; font-weight: 600;">
            {{ $exporter['address'] ?? 'SECTOR 23, 23-A-6, CHOPASNI HOUSING BOARD, OPPOSITE ASHOK UDHYAN GATE NO. 2, JODHPUR, RAJASTHAN - 342008 (INDIA)' }}
          </div>
          <div style="font-size: 10px; margin-top: 3px;">
            <span><strong>TEL:</strong> {{ $exporter['phone'] ?? '+91- 9785065066, 9414154054' }}</span><br>
            <span><strong>E.mail:</strong> {{ $exporter['email'] ?? 'reliancegranito@gmail.com' }}</span><br>
            <span><strong>Website:</strong> {{ $exporter['website'] ?? 'www.reliancegranito.com' }}</span>
          </div>
        </td>

        <!-- Right: Invoice No, Date & Statutory Codes -->
        <td colspan="3" class="p-0 align-top" style="width: 48%;">
          <table class="w-100" style="border-collapse: collapse; font-size: 10px;">
            <tr style="border-bottom: 1px solid #000;">
              <td class="p-1 fw-bold text-center" style="width: 50%; border-right: 1px solid #000; background: #fafafa;">
                Invoice No. / Date
                <div class="fw-bold text-dark fs-7 mt-0.5" id="invNoDisplay">
                  @if(!empty($invoice['invoice_number']))
                    {{ $invoice['invoice_number'] }} &bull; {{ $invoice['invoice_date'] ?? '' }}
                  @else
                    {{ $invoice['invoice_date'] ?? '' }}
                  @endif
                </div>
              </td>
              <td class="p-1 fw-bold text-center" style="width: 50%; background: #fafafa;">
                IEC NO.
                <div class="fw-bold text-dark fs-7 mt-0.5">{{ $exporter['iec'] ?? '' }}</div>
              </td>
            </tr>
            <tr style="border-bottom: 1px solid #000;">
              <td colspan="2" class="p-1">
                <strong>GSTIN :</strong> <span class="font-monospace fw-bold">{{ $exporter['gstin'] ?? '' }}</span>
              </td>
            </tr>
            <tr style="border-bottom: 1px solid #000;">
              <td colspan="2" class="p-1" style="font-size: 9px;">
                <strong>Terms of Export :</strong> {{ $exporter['lut_rule'] ?? '' }}
              </td>
            </tr>
            <tr>
              <td class="p-1" style="border-right: 1px solid #000; width: 50%;">
                <div class="fw-bold text-uppercase" style="font-size: 9px;">CONTAINER NO.</div>
                <div class="fw-bold font-monospace" id="containerNoDisplay">{{ $invoice['container_number'] ?? '' }}</div>
              </td>
              <td class="p-1" style="width: 50%;">
                <div class="fw-bold text-uppercase" style="font-size: 9px;">BUYER'S P/O NO</div>
                <div class="fw-bold" id="buyerPoDisplay">{{ $invoice['buyer_po'] ?? '' }}</div>
              </td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- Consignee / Buyer & Other Buyer Block -->
      <tr style="border-bottom: 1.5px solid #000;">
        <!-- Consignee -->
        <td colspan="4" class="p-2 align-top" style="width: 52%; border-right: 1.5px solid #000;">
          <div class="text-decoration-underline fw-bold mb-1" style="font-size: 11px;">Consignee / Buyer:</div>
          <div class="fw-bold text-uppercase mb-1" id="consigneeNameDisplay" style="font-size: 11px;">
            {{ $consigner->name ?? '' }}
          </div>
          <div style="font-size: 10px;" id="consigneeAddressDisplay">
            @if(!empty($consigner?->registered_address) || !empty($consigner?->factory_address))
              <strong>ADD:</strong> {{ $consigner->registered_address ?? $consigner->factory_address }}
            @endif
          </div>
          <div style="font-size: 10px; margin-top: 2px;">
            <strong>TAX CODE:</strong> <span id="consigneeTaxDisplay">{{ $consigner->gstin ?? $consigner->pan ?? '' }}</span><br>
            <strong>TEL:</strong> <span id="consigneePhoneDisplay">{{ $consigner->contact_phone ?? '' }}</span><br>
            <strong>EMAIL:</strong> <span id="consigneeEmailDisplay">{{ $consigner->contact_email ?? '' }}</span>
          </div>
        </td>

        <!-- Buyer (If other than Consignee) -->
        <td colspan="3" class="p-2 align-top" style="width: 48%;">
          <div class="text-decoration-underline fw-bold mb-1" style="font-size: 11px;">Buyer (If other than Consignee):</div>
          <div class="fw-bold text-uppercase mb-1" id="buyerOtherNameDisplay" style="font-size: 11px;">
            {{ $buyerOther['name'] ?? '' }}
          </div>
          <div style="font-size: 10px;" id="buyerOtherAddressDisplay">
            @if(!empty($buyerOther['address']))
              <strong>ADD:</strong> {{ $buyerOther['address'] }}
            @endif
          </div>
          <div style="font-size: 10px; margin-top: 2px;">
            <strong>TAX CODE:</strong> <span id="buyerOtherTaxDisplay">{{ $buyerOther['tax_code'] ?? '' }}</span><br>
            <strong>TEL:</strong> <span id="buyerOtherPhoneDisplay">{{ $buyerOther['phone'] ?? '' }}</span><br>
            <strong>EMAIL:</strong> <span id="buyerOtherEmailDisplay">{{ $buyerOther['email'] ?? '' }}</span>
          </div>
        </td>
      </tr>

      <!-- Logistics, Pre-Carriage & Vessel Discharge Block -->
      <tr style="border-bottom: 1.5px solid #000; font-size: 10px;">
        <td class="p-1" style="width: 16%; border-right: 1px solid #000;">
          <div class="fw-bold" style="font-size: 9px;">Pre-Carriage by</div>
          <div class="fw-semibold" id="preCarriageDisplay">{{ $invoice['pre_carriage_by'] ?? '' }}</div>
        </td>
        <td class="p-1" style="width: 18%; border-right: 1px solid #000;">
          <div class="fw-bold" style="font-size: 9px;">Place of Receipt by Pre-Carrier</div>
          <div class="fw-semibold" id="receiptPlaceDisplay">{{ $invoice['place_of_receipt'] ?? '' }}</div>
        </td>
        <td colspan="2" class="p-1" style="width: 18%; border-right: 1.5px solid #000;">
          <div class="fw-bold" style="font-size: 9px;">Country of Origin of goods</div>
          <div class="fw-semibold" id="countryOriginDisplay">{{ $invoice['country_of_origin'] ?? 'INDIA' }}</div>
        </td>
        <td colspan="3" class="p-1" style="width: 48%;">
          <div class="fw-bold" style="font-size: 9px;">Country of Final Destination</div>
          <div class="fw-semibold" id="countryDestDisplay">{{ $invoice['country_of_destination'] ?? '' }}</div>
        </td>
      </tr>

      <tr style="border-bottom: 1.5px solid #000; font-size: 10px;">
        <td colspan="2" class="p-1" style="border-right: 1px solid #000;">
          <div class="fw-bold" style="font-size: 9px;">Vessel / Flight No.</div>
          <div class="fw-semibold" id="vesselFlightDisplay">{{ $invoice['vessel_flight_no'] ?? '' }}</div>
        </td>
        <td colspan="2" class="p-1" style="border-right: 1px solid #000;">
          <div class="fw-bold" style="font-size: 9px;">Port of Loading</div>
          <div class="fw-semibold" id="portLoadingDisplay">{{ $invoice['port_of_loading'] ?? '' }}</div>
        </td>
        <td colspan="3" rowspan="2" class="p-2 align-middle text-center" style="background: #fafafa;">
          <div class="fw-bold mb-1" style="font-size: 10px;">Terms of Delivery & Payment</div>
          <div class="fw-bold text-dark" id="paymentTermsDisplay" style="font-size: 11px;">
            {{ $invoice['payment_terms'] ?? '' }}
          </div>
        </td>
      </tr>

      <tr style="border-bottom: 2px solid #000; font-size: 10px;">
        <td colspan="2" class="p-1" style="border-right: 1px solid #000;">
          <div class="fw-bold" style="font-size: 9px;">Port of Discharge</div>
          <div class="fw-semibold" id="portDischargeDisplay">{{ $invoice['port_of_discharge'] ?? '' }}</div>
        </td>
        <td colspan="2" class="p-1" style="border-right: 1.5px solid #000;">
          <div class="fw-bold" style="font-size: 9px;">Final Destination</div>
          <div class="fw-semibold" id="finalDestinationDisplay">{{ $invoice['final_destination'] ?? '' }}</div>
        </td>
      </tr>

      <!-- Goods Table Column Headers -->
      <tr style="border-bottom: 1.5px solid #000; background: #f1f5f9; text-align: center; font-size: 10px; font-weight: bold;">
        <th class="p-1" style="width: 9%; border-right: 1px solid #000;">Marks & Nos</th>
        <th class="p-1" style="width: 14%; border-right: 1px solid #000;">Container No. Trailer</th>
        <th class="p-1" style="width: 32%; border-right: 1px solid #000;">Description of Goods</th>
        <th class="p-1" style="width: 10%; border-right: 1px solid #000;">HSN Code</th>
        <th class="p-1" style="width: 11%; border-right: 1px solid #000;">Quantity<br>(SQM.)</th>
        <th class="p-1" style="width: 10%; border-right: 1px solid #000;">Rate Per<br>(SQM.) CIF</th>
        <th class="p-1" style="width: 14%;">AMOUNT<br>₹ (INR)</th>
      </tr>
    </tbody>

    <!-- Dedicated Goods Item Rows Tbody (Multiple Items) -->
    <tbody id="invoiceItemsTbody">
      @forelse($items as $idx => $it)
      @if(!empty($it['description']) || !empty($it['quantity']) || !empty($it['marks_numbers']))
      <tr class="invoice-item-row" style="border-bottom: 1px solid #000; font-size: 10.5px;">
        <td class="p-1.5 text-center align-top item-marks" style="border-right: 1px solid #000;">
          <div class="fw-bold">{{ $it['marks_numbers'] ?? '' }}</div>
        </td>
        <td class="p-1.5 text-center align-top font-monospace item-container" style="border-right: 1px solid #000;">
          <div class="fw-bold">{{ $it['container_no'] ?? ($invoice['container_number'] ?? '') }}</div>
          @if(!empty($it['trailer_no']))
            <div class="small text-muted">{{ $it['trailer_no'] }}</div>
          @endif
        </td>
        <td class="p-1.5 align-top item-desc" style="border-right: 1px solid #000;">
          <div class="fw-bold text-uppercase">{!! nl2br(e($it['description'] ?? '')) !!}</div>
        </td>
        <td class="p-1.5 text-center align-top font-monospace item-hsn" style="border-right: 1px solid #000;">
          {{ $it['hsn_code'] ?? '68022390' }}
        </td>
        <td class="p-1.5 text-end align-top fw-bold font-monospace item-qty" style="border-right: 1px solid #000;">
          {{ !empty($it['quantity']) ? number_format($it['quantity'], 3) : '' }}
        </td>
        <td class="p-1.5 text-end align-top font-monospace item-rate" style="border-right: 1px solid #000;">
          {{ !empty($it['rate']) ? number_format($it['rate'], 3) : '' }}
        </td>
        <td class="p-1.5 text-end align-top fw-bold font-monospace item-amount">
          {{ !empty($it['amount']) ? number_format($it['amount'], 2) : '' }}
        </td>
      </tr>
      @endif
      @empty
      @endforelse
    </tbody>

    <!-- Totals & Sub-Sections Tbody -->
    <tbody>
      <!-- Origin & Subtotal Row -->
      <tr style="border-bottom: 1px solid #000; font-size: 10.5px;">
        <td colspan="4" class="p-1.5 fst-italic fw-bold" style="border-right: 1px solid #000;">
          {{ $invoice['origin_clause'] ?? 'GOODS ARE OF INDIAN ORIGIN' }}
        </td>
        <td colspan="2" class="p-1.5 text-end fw-bold" style="border-right: 1px solid #000;">
          Total:
        </td>
        <td class="p-1.5 text-end fw-bold font-monospace" id="totalAmountDisplay">
          ₹ {{ number_format($totalAmount, 2) }}
        </td>
      </tr>

      <!-- Total SQMT & Rounded Total Row -->
      <tr style="border-bottom: 1.5px solid #000; font-size: 11px; background: #fafafa;">
        <td colspan="3" class="p-1.5 text-end fw-bold" style="border-right: 1px solid #000;">
          TOTAL SQ MT.
        </td>
        <td class="p-1.5 text-center fw-bold font-monospace" id="totalQuantityDisplay" style="border-right: 1px solid #000;">
          {{ number_format($totalQuantity, 3) }}
        </td>
        <td colspan="2" class="p-1.5 text-end fw-bold" style="border-right: 1px solid #000;">
          Rounded to
        </td>
        <td class="p-1.5 text-end fw-black font-monospace fs-7" id="roundedAmountDisplay" style="font-weight: 900;">
          ₹ {{ number_format($roundedAmount, 2) }}
        </td>
      </tr>

      <!-- Amount In Words Row -->
      <tr style="border-bottom: 1.5px solid #000; font-size: 10.5px;">
        <td colspan="2" class="p-1.5 fw-bold" style="border-right: 1px solid #000; width: 23%;">
          Amount In Words (₹ / INR)
        </td>
        <td colspan="5" class="p-1.5 fw-black text-uppercase" id="amountInWordsDisplay" style="font-weight: 900; letter-spacing: 0.03em;">
          {{ $amountInWords ?? '' }}
        </td>
      </tr>

      <!-- Banking, Containers & Weights Block -->
      <tr style="border-bottom: 1.5px solid #000; font-size: 10px;">
        <!-- Bank Details -->
        <td colspan="4" class="p-2 align-top" style="width: 52%; border-right: 1px solid #000;">
          <div class="fw-bold text-uppercase mb-1" style="font-size: 10px;">
            BANK DETAILS : {{ $bank['name'] ?? 'ICICI BANK LIMITED' }}
          </div>
          <div style="font-size: 9.5px; line-height: 1.4;">
            <strong>A/C NO :</strong> <span class="font-monospace fw-bold">{{ $bank['account_no'] ?? '777705022062' }}</span> &nbsp;
            <strong>SWIFT CODE :</strong> <span class="font-monospace fw-bold">{{ $bank['swift_code'] ?? 'ICICINBBCTS' }}</span><br>
            <strong>A.D CODE :</strong> <span class="font-monospace fw-bold">{{ $consigner?->ad_code ?? ($bank['ad_code'] ?? '6390013') }}</span><br>
            <strong>BANK ADDRESS :</strong> {{ $bank['address'] ?? '14/67, Chopasni Housing Board, JODHPUR - 342003, RAJASTHAN (INDIA)' }}
          </div>
        </td>

        <!-- No of Containers -->
        <td class="p-2 text-center align-middle" style="width: 14%; border-right: 1px solid #000; background: #fafafa;">
          <div class="fw-bold" style="font-size: 9px;">NO OF CONTAINER</div>
          <div class="fw-bold fs-5 mt-1">{{ $invoice['no_of_containers'] ?? 1 }}</div>
        </td>

        <!-- Weight in Kgs -->
        <td colspan="2" class="p-2 align-middle font-monospace" style="width: 34%;">
          <div class="fw-bold mb-1" style="font-size: 10px;">Weight in Kgs :</div>
          <div class="d-flex justify-content-between mb-0.5">
            <span>G.WT :</span> <span class="fw-bold">{{ $invoice['gross_weight'] ?? '26500 KGS' }}</span>
          </div>
          <div class="d-flex justify-content-between">
            <span>N.WT :</span> <span class="fw-bold">{{ $invoice['net_weight'] ?? '25500 KGS' }}</span>
          </div>
        </td>
      </tr>

      <!-- Badges, Recognition & Authorised Signatory Block -->
      <tr style="border-bottom: 1.5px solid #000;">
        <!-- Logos & Industry Certifications -->
        <td colspan="4" class="p-1 align-middle" style="border-right: 1.5px solid #000; width: 52%;">
          <div class="invoice-badges-row text-center" style="display: flex !important; flex-direction: row !important; flex-wrap: nowrap !important; align-items: stretch !important; justify-content: space-between !important; gap: 3px !important; width: 100% !important;">
            
            <!-- FIEO Badge -->
            <div class="badge-item p-1 border rounded" style="flex: 1 1 0; min-width: 0; border: 1px solid #cbd5e1 !important; border-radius: 4px; padding: 2px 1px !important;">
              <div style="font-weight: 900; color: #0284c7; font-size: 10px; line-height: 1.1;">&lt;FIEO&gt;</div>
              <div style="font-size: 7px; line-height: 1; word-break: break-word;">{{ $certifications['rcmc_fieo_no'] ?? ($settings->rcmc_fieo_no ?? 'RCMC/FIEO/11176/2025-2027') }}</div>
            </div>

            <!-- FIGSI Badge -->
            <div class="badge-item p-1 border rounded" style="flex: 1 1 0; min-width: 0; border: 1px solid #cbd5e1 !important; border-radius: 4px; padding: 2px 1px !important;">
              <div style="font-weight: 900; color: #b91c1c; font-size: 10px; line-height: 1.1;">FIGSI</div>
              <div style="font-size: 7px; line-height: 1;">Granite &amp; Stone<br>Industry</div>
            </div>

            <!-- Two Star Export House -->
            <div class="badge-item p-1 border rounded" style="flex: 1.3 1 0; min-width: 0; border: 1px solid #cbd5e1 !important; border-radius: 4px; padding: 2px 1px !important;">
              <div style="font-weight: 900; color: #ea580c; font-size: 9.5px; line-height: 1.1; white-space: nowrap;">&#9733;&#9733; {{ $certifications['export_house_star'] ?? ($settings->export_house_star ?? 'Two Star Export House') }}</div>
              <div style="font-size: 7px; line-height: 1;">Govt. Recognized<br>Export House</div>
            </div>

            <!-- REPC -->
            <div class="badge-item p-1 border rounded" style="flex: 1 1 0; min-width: 0; border: 1px solid #cbd5e1 !important; border-radius: 4px; padding: 2px 1px !important;">
              <div style="font-weight: 900; color: #1e3a8a; font-size: 10px; line-height: 1.1;">REPC</div>
              <div style="font-size: 7px; line-height: 1; word-break: break-word;">{{ $certifications['repc_no'] ?? ($settings->repc_no ?? 'REPC-22-10349') }}</div>
            </div>

            <!-- ISO Certified -->
            <div class="badge-item p-1 border rounded" style="flex: 1.1 1 0; min-width: 0; border: 1px solid #cbd5e1 !important; border-radius: 4px; padding: 2px 1px !important;">
              <div style="font-weight: 900; color: #15803d; font-size: 9.5px; line-height: 1.1; white-space: nowrap;">ISO Certified</div>
              <div style="font-size: 7px; line-height: 1; word-break: break-word;">{{ $certifications['iso_certification'] ?? ($settings->iso_certification ?? 'ISO 9001:2015 IC/9534879/24') }}</div>
            </div>

          </div>
        </td>

        <!-- Authorised Signatory -->
        <td colspan="3" class="p-2 text-center align-bottom" style="height: 110px;">
          <div class="fw-bold mb-1" style="font-size: 11px;">For : {{ $certifications['signatory_company'] ?? ($exporter['name'] ?? 'RELIANCE GRANITO') }}</div>
          
          <!-- Blank Space for Physical Signature / Stamp -->
          <div style="height: 50px;"></div>
          
          <div class="fw-bold" style="font-size: 9.5px; border-top: 1px dotted #333; padding-top: 3px;">
            {{ $certifications['signatory_title'] ?? ($settings->signatory_title ?? 'Partner / Manager') }}
          </div>
        </td>
      </tr>

      <!-- Declaration Row -->
      <tr>
        <td colspan="7" class="p-2" style="font-size: 9.5px; background: #fff;">
          <div class="fw-bold text-center text-decoration-underline mb-0.5">Declaration</div>
          <div class="text-center">
            {{ $invoice['declaration'] ?? 'We Declare that this invoice shows the actual price of the goods described and all the details given are true and best as per E.& O.E.' }}
          </div>
        </td>
      </tr>

    </tbody>
  </table>
</div>
