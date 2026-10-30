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

@php
  $format = strtoupper($invoice['invoice_format'] ?? 'LUT');
  $isIgst = ($format === 'IGST');
  $term = strtoupper($invoice['delivery_term'] ?? 'CIF');
  $curr = strtoupper($invoice['currency'] ?? 'USD');
  $currSymbol = $curr === 'USD' ? '$' : '₹';
  $currCode = $curr === 'USD' ? 'USD' : 'INR';
  $consigneeMode = $invoice['consignee_mode'] ?? 'consigner';
  $isToOrder = ($consigneeMode === 'to_the_order' || strtoupper($invoice['consignee_name'] ?? '') === 'TO THE ORDER');
  $noOfContainers = !empty($invoice['no_of_containers']) ? (int)$invoice['no_of_containers'] : '';
  $grossWt = $invoice['gross_weight'] ?? '';
  $netWt = $invoice['net_weight'] ?? '';
  $isoCert = $invoice['iso_certification'] ?? '';
  $lutRule = $invoice['lut_no'] ?? '';
  $notifyParties = is_array($invoice['notify_parties'] ?? null) ? $invoice['notify_parties'] : [];

  $rawPaymentTerms = $invoice['payment_terms'] ?? '';
  if ($isIgst && (empty($rawPaymentTerms) || $rawPaymentTerms === '100% AGAINST SCAN B/L COPY' || $rawPaymentTerms === ($term . ' - 100% AGAINST SCAN B/L COPY'))) {
      $paymentTerms = '80% AT PORT & 20% AFTER CHECKING';
  } elseif (!empty($rawPaymentTerms)) {
      $paymentTerms = $rawPaymentTerms;
  } else {
      $paymentTerms = $isIgst ? '80% AT PORT & 20% AFTER CHECKING' : '100% AGAINST SCAN B/L COPY';
  }
@endphp

<div class="commercial-invoice-sheet bg-white p-3 p-md-4 shadow-sm border" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #000; max-width: 960px; margin: 0 auto; background: #fff;">
  
  <!-- Outer Framed Shell with 2px solid border -->
  <div class="table-invoice-main w-100" style="border: 2px solid #000; background: #fff;">
    
    <!-- Top Header Title Row -->
    <div style="border-bottom: 2px solid #000; padding: 8px 12px; background: #fff; color: #000;">
      <div class="d-flex align-items-center justify-content-between">
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
          <div id="invoiceFormatBanner" style="font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; color: #111;">
            @if($isIgst)
              SUPPLY MEANT FOR EXPORT WITH PAYMENT OF IGST
            @else
              SUPPLY MEANT FOR EXPORT UNDER BOND OR LETTER OF UNDERTAKING WITHOUT PAYMENT OF IGST
            @endif
          </div>
        </div>

        <div style="min-width: 140px; text-align: right; font-size: 9px;" class="d-none d-sm-block">
        </div>
      </div>
    </div>

    <!-- Upper Metadata Block: Clean 50% / 50% Split -->
    <table class="w-100" style="border-collapse: collapse; table-layout: fixed; font-size: 10px; line-height: 1.35; width: 100%;">
      <colgroup>
        <col style="width: 50%;">
        <col style="width: 50%;">
      </colgroup>
      <tbody>
        <!-- Row 1: Exporter (50%) & Invoice No / IEC / Statutory Codes (50%) -->
        <tr style="border-bottom: 1.5px solid #000;">
          <!-- Left: Exporter Identity (50%) -->
          <td class="p-2 align-top" style="width: 50%; border-right: 1.5px solid #000;">
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

          <!-- Right: Invoice No, Date & Statutory Codes (50%) -->
          <td class="p-0 align-top" style="width: 50%;">
            <table class="w-100" style="border-collapse: collapse; font-size: 10px; table-layout: fixed;">
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
              <tr style="border-bottom: 1px solid #000; {{ $isIgst ? 'display: none;' : '' }}" id="termsOfExportRow">
                <td colspan="2" class="p-1" style="font-size: 9px;">
                  <strong>Terms of Export :</strong> <span id="lutRuleDisplay">{{ $lutRule }}</span>
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

        <!-- Row 2: Consignee / Buyer (50%) & Buyer Other (50%) -->
        <tr style="border-bottom: 1.5px solid #000;">
          <!-- Consignee (50%) -->
          <td class="p-2 align-top" style="width: 50%; border-right: 1.5px solid #000;">
            <div class="text-decoration-underline fw-bold mb-1" style="font-size: 11px;">Consignee / Buyer:</div>
            <div class="fw-bold text-uppercase mb-1" id="consigneeNameDisplay" style="font-size: 11px;">
              @if($isToOrder)
                TO THE ORDER
              @else
                {{ $invoice['consignee_name'] ?? ($consigner->name ?? '') }}
              @endif
            </div>
            <div style="font-size: 10px;" id="consigneeAddressDisplay">
              @if(!$isToOrder)
                @php
                  $cAddr = $invoice['consignee_address'] ?? ($consigner->registered_address ?? ($consigner->factory_address ?? ''));
                @endphp
                @if(!empty($cAddr))
                  <strong>ADD:</strong> {{ $cAddr }}
                @endif
              @endif
            </div>
            <div style="font-size: 10px; margin-top: 2px;" id="consigneeMetaDisplay" style="{{ $isToOrder ? 'display:none;' : '' }}">
              @if(!$isToOrder)
                <strong>TAX CODE:</strong> <span id="consigneeTaxDisplay">{{ $invoice['consignee_tax'] ?? ($consigner->gstin ?? ($consigner->pan ?? '')) }}</span><br>
                <strong>TEL:</strong> <span id="consigneePhoneDisplay">{{ $invoice['consignee_phone'] ?? ($consigner->contact_phone ?? '') }}</span><br>
                <strong>EMAIL:</strong> <span id="consigneeEmailDisplay">{{ $invoice['consignee_email'] ?? ($consigner->contact_email ?? '') }}</span>
              @endif
            </div>
          </td>

          <!-- Buyer (If other than Consignee) (50%) -->
          <td class="p-2 align-top" style="width: 50%;">
            <div class="text-decoration-underline fw-bold mb-1" style="font-size: 11px;">Buyer (If other than Consignee):</div>
            <div class="fw-bold text-uppercase mb-1" id="buyerOtherNameDisplay" style="font-size: 11px;">
              {{ $invoice['buyer_name'] ?? ($buyerOther['name'] ?? '') }}
            </div>
            <div style="font-size: 10px;" id="buyerOtherAddressDisplay">
              @php
                $bAddr = $invoice['buyer_address'] ?? ($buyerOther['address'] ?? '');
              @endphp
              @if(!empty($bAddr))
                <strong>ADD:</strong> {{ $bAddr }}
              @endif
            </div>
            <div style="font-size: 10px; margin-top: 2px;">
              <strong>TAX CODE:</strong> <span id="buyerOtherTaxDisplay">{{ $invoice['buyer_tax'] ?? ($buyerOther['tax_code'] ?? '') }}</span><br>
              <strong>TEL:</strong> <span id="buyerOtherPhoneDisplay">{{ $invoice['buyer_phone'] ?? ($buyerOther['phone'] ?? '') }}</span><br>
              <strong>EMAIL:</strong> <span id="buyerOtherEmailDisplay">{{ $invoice['buyer_email'] ?? ($buyerOther['email'] ?? '') }}</span>
            </div>
          </td>
        </tr>

        <!-- Optional Notify Parties Section -->
        @if(!empty($notifyParties))
        <tr style="border-bottom: 1.5px solid #000; font-size: 10px;" id="notifyPartiesDisplayRow">
          <td colspan="2" class="p-2" style="background: #fafafa;">
            <div class="text-decoration-underline fw-bold mb-1" style="font-size: 10px;">Notify Party / Parties:</div>
            <div class="row g-2" id="notifyPartiesList">
              @foreach($notifyParties as $nIdx => $np)
                @if(!empty($np['name']) || !empty($np['address']))
                  <div class="col-md-6 col-12 mb-0.5">
                    <strong>Notify Party {{ $nIdx + 1 }}:</strong> <span class="fw-bold">{{ $np['name'] ?? '' }}</span>
                    @if(!empty($np['address'])) &bull; <span>ADD: {{ $np['address'] }}</span> @endif
                    @if(!empty($np['tax'])) &bull; <span>TAX: {{ $np['tax'] }}</span> @endif
                    @if(!empty($np['phone'])) &bull; <span>TEL: {{ $np['phone'] }}</span> @endif
                  </div>
                @endif
              @endforeach
            </div>
          </td>
        </tr>
        @endif

        <!-- Row 3: Pre-Carriage & Origin / Destination -->
        <tr style="border-bottom: 1px solid #000; font-size: 10px;">
          <!-- Left: Pre-Carriage (50% total: 25% / 25%) -->
          <td class="p-0 align-top" style="width: 50%; border-right: 1.5px solid #000;">
            <table class="w-100" style="border-collapse: collapse; table-layout: fixed;">
              <tr>
                <td class="p-1" style="width: 50%; border-right: 1px solid #000;">
                  <div class="fw-bold" style="font-size: 9px;">Pre-Carriage by</div>
                  <div class="fw-semibold" id="preCarriageDisplay">{{ $invoice['pre_carriage_by'] ?? '' }}</div>
                </td>
                <td class="p-1" style="width: 50%;">
                  <div class="fw-bold" style="font-size: 9px;">Place of Receipt by Pre-Carrier</div>
                  <div class="fw-semibold" id="receiptPlaceDisplay">{{ $invoice['place_of_receipt'] ?? '' }}</div>
                </td>
              </tr>
            </table>
          </td>

          <!-- Right: Country of Origin & Destination (50% total: 25% / 25%) -->
          <td class="p-0 align-top" style="width: 50%;">
            <table class="w-100" style="border-collapse: collapse; table-layout: fixed;">
              <tr>
                <td class="p-1" style="width: 50%; border-right: 1px solid #000;">
                  <div class="fw-bold" style="font-size: 9px;">Country of Origin of goods</div>
                  <div class="fw-semibold" id="countryOriginDisplay">{{ $invoice['country_of_origin'] ?? '' }}</div>
                </td>
                <td class="p-1" style="width: 50%;">
                  <div class="fw-bold" style="font-size: 9px;">Country of Final Destination</div>
                  <div class="fw-semibold" id="countryDestDisplay">{{ $invoice['country_of_destination'] ?? '' }}</div>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Row 4: Vessel / Port & Terms of Delivery -->
        <tr style="border-bottom: 2px solid #000; font-size: 10px;">
          <!-- Left: Vessel & Ports (50% total) -->
          <td class="p-0 align-top" style="width: 50%; border-right: 1.5px solid #000;">
            <table class="w-100" style="border-collapse: collapse; table-layout: fixed;">
              <tr style="border-bottom: 1px solid #000;">
                <td class="p-1" style="width: 50%; border-right: 1px solid #000;">
                  <div class="fw-bold" style="font-size: 9px;">Vessel / Flight No.</div>
                  <div class="fw-semibold" id="vesselFlightDisplay">{{ $invoice['vessel_flight_no'] ?? '' }}</div>
                </td>
                <td class="p-1" style="width: 50%;">
                  <div class="fw-bold" style="font-size: 9px;">Port of Loading</div>
                  <div class="fw-semibold" id="portLoadingDisplay">{{ $invoice['port_of_loading'] ?? '' }}</div>
                </td>
              </tr>
              <tr>
                <td class="p-1" style="width: 50%; border-right: 1px solid #000;">
                  <div class="fw-bold" style="font-size: 9px;">Port of Discharge</div>
                  <div class="fw-semibold" id="portDischargeDisplay">{{ $invoice['port_of_discharge'] ?? '' }}</div>
                </td>
                <td class="p-1" style="width: 50%;">
                  <div class="fw-bold" style="font-size: 9px;">Final Destination</div>
                  <div class="fw-semibold" id="finalDestinationDisplay">{{ $invoice['final_destination'] ?? '' }}</div>
                </td>
              </tr>
            </table>
          </td>

          <!-- Right: Terms of Delivery & Payment (50% total, spanning height) -->
          <td class="p-2 align-middle text-center" style="width: 50%; background: #fafafa;">
            <div class="fw-bold mb-1" style="font-size: 10px;">Terms of Delivery & Payment</div>
            <div class="fw-bold text-dark" id="paymentTermsDisplay" style="font-size: 11px;">
              {{ $paymentTerms }}
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Goods Table Column Headers & Dedicated Tbody -->
    <table class="table-invoice-items w-100" style="border-collapse: collapse; table-layout: fixed; font-size: 10.5px; width: 100%;">
      <colgroup>
        <col style="width: 9%;">
        <col style="width: 14%;">
        <col style="width: 32%;">
        <col style="width: 10%;">
        <col style="width: 11%;">
        <col style="width: 10%;">
        <col style="width: 14%;">
      </colgroup>
      <thead>
        <tr style="border-bottom: 1.5px solid #000; background: #f1f5f9; text-align: center; font-size: 10px; font-weight: bold;">
          <th class="p-1" style="width: 9%; border-right: 1px solid #000;">Marks & Nos</th>
          <th class="p-1" style="width: 14%; border-right: 1px solid #000;">Container No. Trailer</th>
          <th class="p-1" style="width: 32%; border-right: 1px solid #000;">Description of Goods</th>
          <th class="p-1" style="width: 10%; border-right: 1px solid #000;">HSN Code</th>
          <th class="p-1" style="width: 11%; border-right: 1px solid #000;">Quantity<br>(SQM.)</th>
          <th class="p-1" style="width: 10%; border-right: 1px solid #000;" id="tableRateHeader">Rate Per<br>(SQM.) <span id="rateTermSpan">{{ $term }}</span> (<span class="currency-symbol">{{ $currSymbol }}</span>)</th>
          <th class="p-1" style="width: 14%;" id="tableAmountHeader">AMOUNT<br><span id="amountCurrSpan"><span class="currency-symbol">{{ $currSymbol }}</span> (<span class="currency-code">{{ $currCode }}</span>)</span></th>
        </tr>
      </thead>

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
            {{ $it['hsn_code'] ?? '' }}
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
            <span class="currency-symbol">{{ $currSymbol }}</span> {{ number_format($totalAmount, 2) }}
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
            <span class="currency-symbol">{{ $currSymbol }}</span> {{ number_format($roundedAmount, 2) }}
          </td>
        </tr>

        <!-- Optional IGST Breakdown Row (When IGST Format selected) -->
        <tr style="border-bottom: 1px solid #000; font-size: 10.5px; background: #fdfdfd; {{ $isIgst ? '' : 'display: none;' }}" id="igstBreakdownRow">
          <td colspan="4" class="p-1.5" style="border-right: 1px solid #000;">
            <div id="igstFxContainer" style="{{ $curr === 'USD' ? '' : 'display: none;' }}">
              <strong>Exchange Rate:</strong> 1 USD = ₹ <span id="displayExchangeRate">{{ number_format($invoice['exchange_rate'] ?? 94.20, 2) }}</span>
            </div>
            <div><strong>Taxable Value (INR):</strong> ₹ <span id="displayTaxableValue">{{ number_format($invoice['taxable_value_inr'] ?? 0, 2) }}</span></div>
          </td>
          <td colspan="2" class="p-1.5 text-end fw-bold" style="border-right: 1px solid #000;">
            Integrated Tax / IGST (<span id="displayIgstRate">{{ number_format($invoice['igst_rate'] ?? 18, 0) }}</span>%):
          </td>
          <td class="p-1.5 text-end fw-bold font-monospace" id="displayIgstAmount">
            ₹ {{ number_format($invoice['igst_amount_inr'] ?? 0, 2) }}
          </td>
        </tr>

        <!-- Amount In Words Row -->
        <tr style="border-bottom: 1.5px solid #000; font-size: 10px;">
          <td colspan="7" class="py-1 px-2 fw-bold" style="line-height: 1.35; background: #fff;">
            <span>Amount In Words (<span class="currency-code">{{ $currCode }}</span>):</span>
            <span class="fw-bold text-uppercase ms-1" id="amountInWordsDisplay" style="font-weight: 800; letter-spacing: 0.02em;">{{ $amountInWords ?? '' }}</span>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Lower Particulars Block: Clean 50% / 50% Split -->
    <table class="w-100" style="border-collapse: collapse; table-layout: fixed; font-size: 10px; width: 100%;">
      <colgroup>
        <col style="width: 50%;">
        <col style="width: 50%;">
      </colgroup>
      <tbody>
        <!-- Banking, Containers & Weights Block -->
        <tr style="border-bottom: 1.5px solid #000;">
          <!-- Bank Details (Left 50%) -->
          <td class="p-2 align-top" style="width: 50%; border-right: 1.5px solid #000;">
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

          <!-- Right 50%: No of Containers & Weights -->
          <td class="p-0 align-top" style="width: 50%;">
            <table class="w-100 h-100" style="border-collapse: collapse; table-layout: fixed;">
              <tr style="height: 100%;">
                <td class="p-2 text-center align-middle" style="width: 35%; border-right: 1px solid #000; background: #fafafa;">
                  <div class="fw-bold" style="font-size: 9px;">NO OF CONTAINER</div>
                  <div class="fw-bold fs-5 mt-1" id="noOfContainersDisplay">{{ $noOfContainers }}</div>
                  <div class="small text-muted font-monospace" id="containerTypeDisplay">{{ $invoice['container_type'] ?? '' }}</div>
                </td>
                <td class="p-2 align-middle font-monospace" style="width: 65%;">
                  <div class="fw-bold mb-1" style="font-size: 10px;">Weight in Kgs :</div>
                  <div class="d-flex justify-content-between mb-0.5">
                    <span>G.WT :</span> <span class="fw-bold" id="grossWeightDisplay">{{ !empty($grossWt) ? (is_numeric($grossWt) ? number_format($grossWt, 0, '.', '') . ' KGS' : $grossWt) : '' }}</span>
                  </div>
                  <div class="d-flex justify-content-between">
                    <span>N.WT :</span> <span class="fw-bold" id="netWeightDisplay">{{ !empty($netWt) ? (is_numeric($netWt) ? number_format($netWt, 0, '.', '') . ' KGS' : $netWt) : '' }}</span>
                  </div>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Badges, Recognition & Authorised Signatory Block -->
        <tr style="border-bottom: 1.5px solid #000;">
          <!-- Logos & Industry Certifications (Left 50%) -->
          <td class="p-1 align-middle" style="width: 50%; border-right: 1.5px solid #000;">
            <table class="invoice-badges-table" style="width: 100%; table-layout: fixed; border-collapse: separate; border-spacing: 3px; text-align: center; margin: 0;">
              <colgroup>
                <col style="width: 18%;">
                <col style="width: 18%;">
                <col style="width: 28%;">
                <col style="width: 18%;">
                <col style="width: 18%;">
              </colgroup>
              <tbody>
                <tr>
                  <!-- FIEO Badge -->
                  <td class="badge-item" style="border: 1px solid #cbd5e1; border-radius: 4px; padding: 3px 1px; vertical-align: middle; background: #fff;">
                    <div style="font-weight: 900; color: #0284c7; font-size: 9px; line-height: 1.1;">&lt;FIEO&gt;</div>
                    <div style="font-size: 6.5px; line-height: 1.1; word-break: break-word; color: #1e293b;">{{ $certifications['rcmc_fieo_no'] ?? ($settings->rcmc_fieo_no ?? 'RCMC/FIEO/11176/2025-2027') }}</div>
                  </td>

                  <!-- FIGSI Badge -->
                  <td class="badge-item" style="border: 1px solid #cbd5e1; border-radius: 4px; padding: 3px 1px; vertical-align: middle; background: #fff;">
                    <div style="font-weight: 900; color: #b91c1c; font-size: 9px; line-height: 1.1;">FIGSI</div>
                    <div style="font-size: 6.5px; line-height: 1.1; color: #1e293b;">Granite &amp; Stone<br>Industry</div>
                  </td>

                  <!-- Two Star Export House -->
                  <td class="badge-item" style="border: 1px solid #cbd5e1; border-radius: 4px; padding: 3px 1px; vertical-align: middle; background: #fff;">
                    <div style="font-weight: 900; color: #ea580c; font-size: 8px; line-height: 1.15; word-break: break-word;">&#9733;&#9733; {{ $certifications['export_house_star'] ?? ($settings->export_house_star ?? 'Two Star Export House') }}</div>
                    <div style="font-size: 6.5px; line-height: 1.1; color: #1e293b;">Govt. Recognized<br>Export House</div>
                  </td>

                  <!-- REPC -->
                  <td class="badge-item" style="border: 1px solid #cbd5e1; border-radius: 4px; padding: 3px 1px; vertical-align: middle; background: #fff;">
                    <div style="font-weight: 900; color: #1e3a8a; font-size: 9px; line-height: 1.1;">REPC</div>
                    <div style="font-size: 6.5px; line-height: 1.1; word-break: break-word; color: #1e293b;">{{ $certifications['repc_no'] ?? ($settings->repc_no ?? 'REPC-22-10349') }}</div>
                  </td>

                  <!-- ISO Certified -->
                  <td class="badge-item" style="border: 1px solid #cbd5e1; border-radius: 4px; padding: 3px 1px; vertical-align: middle; background: #fff;">
                    <div style="font-weight: 900; color: #15803d; font-size: 8px; line-height: 1.1; word-break: break-word;">ISO Certified</div>
                    <div style="font-size: 6.5px; line-height: 1.1; word-break: break-word; color: #1e293b;" id="isoCertDisplay">{{ $isoCert }}</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>

          <!-- Authorised Signatory (Right 50%) -->
          <td class="p-2 text-center align-bottom" style="width: 50%; height: 110px;">
            <div class="fw-bold mb-1" style="font-size: 11px;">For : {{ $certifications['signatory_company'] ?? ($exporter['name'] ?? 'RELIANCE GRANITO') }}</div>
            
            <!-- Blank Space for Physical Signature / Stamp -->
            <div style="height: 50px;"></div>
            
            <div class="fw-bold" style="font-size: 9.5px; border-top: 1px dotted #333; padding-top: 3px;">
              {{ $certifications['signatory_title'] ?? ($settings->signatory_title ?? 'Partner / Manager') }}
            </div>
          </td>
        </tr>

        <!-- Declaration Row (100% width) -->
        <tr>
          <td colspan="2" class="p-2" style="font-size: 9.5px; background: #fff;">
            <div class="fw-bold text-center text-decoration-underline mb-0.5">Declaration</div>
            <div class="text-center" id="declarationDisplay">
              {{ $invoice['declaration'] ?? 'We Declare that this invoice shows the actual price of the goods described and all the details given are true and best as per E.& O.E.' }}
            </div>
          </td>
        </tr>
      </tbody>
    </table>

  </div>
</div>
