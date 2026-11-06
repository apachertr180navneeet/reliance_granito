@props([
  'exporter' => [],
  'invoice' => [],
  'consignee' => null,
  'consigner' => null,
  'buyerOther' => [],
  'items' => [],
  'totalQuantity' => 0,
  'bank' => [],
  'certifications' => [],
  'settings' => null,
  'packingListDetails' => []
])

@php
  $consigner = $consignee ?? $consigner;
  $format = strtoupper($invoice['invoice_format'] ?? 'LUT');
  $term = strtoupper($invoice['delivery_term'] ?? 'CIF');
  $curr = strtoupper($invoice['currency'] ?? 'USD');
  $consigneeMode = $invoice['consignee_mode'] ?? 'consigner';
  $isToOrder = ($consigneeMode === 'to_the_order' || strtoupper($invoice['consignee_name'] ?? '') === 'TO THE ORDER');
  $noOfContainers = !empty($invoice['no_of_containers']) ? (int)$invoice['no_of_containers'] : '';
  $grossWt = $invoice['gross_weight'] ?? 0;
  $netWt = $invoice['net_weight'] ?? 0;

  // Aggregate multi-container display string for the header
  $containerList = [];
  if (!empty($invoice['containers_json']) && is_array($invoice['containers_json'])) {
      foreach ($invoice['containers_json'] as $c) {
          if (!empty($c['container_no'])) $containerList[] = trim($c['container_no']);
      }
  }
  if (!empty($invoice['container_number'])) {
      foreach (explode(',', $invoice['container_number']) as $cn) {
          $cnTrim = trim($cn);
          if (!empty($cnTrim)) $containerList[] = $cnTrim;
      }
  }
  if (!empty($items)) {
      foreach ($items as $it) {
          if (!empty($it['container_no'])) {
              $cnTrim = trim($it['container_no']);
              if (!empty($cnTrim)) $containerList[] = $cnTrim;
          }
      }
  }
  $containerDisplayStr = !empty($containerList) ? implode(', ', array_unique($containerList)) : ($invoice['container_number'] ?? '-');

  // Compute total pieces/packages and item-level weights
  $totalPackages = 0;
  $totalQty = 0.0;
  $computedNetWt = 0.0;
  $computedGrossWt = 0.0;

  $packingRows = [];
  $itemCount = count($items);

  foreach ($items as $idx => $it) {
      if (empty($it['description']) && empty($it['marks_numbers']) && empty($it['quantity'])) {
          continue;
      }
      $qty = (float)($it['quantity'] ?? 0);
      $totalQty += $qty;

      // Pieces / Packages per row
      $pkgs = isset($it['packages']) && is_numeric($it['packages']) 
          ? (int)$it['packages'] 
          : (isset($it['pieces']) && is_numeric($it['pieces']) ? (int)$it['pieces'] : ($idx === 0 && !empty($invoice['total_packages']) ? (int)$invoice['total_packages'] : 0));
      $totalPackages += $pkgs;

      // Item proportional weight or manual weight
      $itemNet = isset($it['net_weight']) && (float)$it['net_weight'] > 0 
          ? (float)$it['net_weight'] 
          : ($itemCount > 0 && is_numeric($netWt) && (float)$netWt > 0 ? round(((float)$netWt / $itemCount), 2) : 0);
      $itemGross = isset($it['gross_weight']) && (float)$it['gross_weight'] > 0 
          ? (float)$it['gross_weight'] 
          : ($itemCount > 0 && is_numeric($grossWt) && (float)$grossWt > 0 ? round(((float)$grossWt / $itemCount), 2) : 0);

      $computedNetWt += $itemNet;
      $computedGrossWt += $itemGross;

      $packingRows[] = [
          'marks_numbers' => $it['marks_numbers'] ?? '',
          'container_no' => $it['container_no'] ?? ($invoice['container_number'] ?? ''),
          'trailer_no' => $it['trailer_no'] ?? ($invoice['trailer_number'] ?? ''),
          'description' => $it['description'] ?? '',
          'hsn_code' => $it['hsn_code'] ?? '',
          'packages' => $pkgs > 0 ? $pkgs : '-',
          'quantity' => $qty,
          'net_weight' => $itemNet,
          'gross_weight' => $itemGross,
      ];
  }

  if ($totalPackages === 0 && !empty($invoice['total_packages'])) {
      $totalPackages = (int)$invoice['total_packages'];
  }
  $finalGross = (float)$grossWt > 0 ? (float)$grossWt : $computedGrossWt;
  $finalNet = (float)$netWt > 0 ? (float)$netWt : $computedNetWt;
  $finalTotalQty = (float)$totalQuantity > 0 ? (float)$totalQuantity : $totalQty;
@endphp

<div class="commercial-packing-list-sheet bg-white p-3 p-md-4 shadow-sm border" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #000; max-width: 960px; margin: 0 auto; background: #fff;">
  
  <!-- Outer Framed Shell with 2px solid border -->
  <div class="table-packing-main w-100" style="border: 2px solid #000; background: #fff;">
    
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
            COMMERCIAL PACKING LIST
          </div>
          <div style="font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; color: #111;">
            CONSIGNMENT CONTAINER &amp; GOODS SPECIFICATIONS
          </div>
        </div>

        <div style="min-width: 140px; text-align: right; font-size: 9px;" class="d-none d-sm-block">
        </div>
      </div>
    </div>

    <!-- Upper Metadata Block: Exporter (50%) & Consignment Meta (50%) -->
    <table class="w-100" style="border-collapse: collapse; table-layout: fixed; font-size: 10px; line-height: 1.35; width: 100%;">
      <colgroup>
        <col style="width: 50%;">
        <col style="width: 50%;">
      </colgroup>
      <tbody>
        <!-- Row 1: Exporter & Invoice Meta -->
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
                  <div class="fw-bold text-dark fs-7 mt-0.5">
                    @if(!empty($invoice['invoice_number']))
                      {{ $invoice['invoice_number'] }} &bull; {{ $invoice['invoice_date'] ?? '' }}
                    @else
                      {{ $invoice['invoice_date'] ?? '' }}
                    @endif
                  </div>
                </td>
                <td class="p-1 fw-bold text-center" style="width: 50%; background: #fafafa;">
                  IEC NO.
                  <div class="fw-bold text-dark fs-7 mt-0.5">{{ $exporter['iec'] ?? '-' }}</div>
                </td>
              </tr>
              <tr style="border-bottom: 1px solid #000;">
                <td colspan="2" class="p-1">
                  <strong>GSTIN :</strong> <span class="font-monospace fw-bold">{{ $exporter['gstin'] ?? '-' }}</span>
                </td>
              </tr>
              <tr>
                <td class="p-1" style="border-right: 1px solid #000; width: 50%;">
                  <div class="fw-bold text-uppercase" style="font-size: 9px;">CONTAINER NO.</div>
                  <div class="fw-bold font-monospace">{{ $containerDisplayStr }}</div>
                </td>
                <td class="p-1" style="width: 50%;">
                  <div class="fw-bold text-uppercase" style="font-size: 9px;">BUYER'S P/O NO</div>
                  <div class="fw-bold">{{ $invoice['buyer_po'] ?? '-' }}</div>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Row 2: Consignee & Buyer Other (50% / 50%) -->
        <tr style="border-bottom: 1.5px solid #000;">
          <!-- Consignee (50%) -->
          <td class="p-2 align-top" style="width: 50%; border-right: 1.5px solid #000;">
            <div class="text-decoration-underline fw-bold mb-1" style="font-size: 11px;">Consignee:</div>
            <div class="fw-bold text-uppercase mb-1" style="font-size: 11px;">
              @if($isToOrder)
                TO THE ORDER
              @else
                {{ $invoice['consignee_name'] ?? ($consigner->name ?? '') }}
              @endif
            </div>
            <div style="font-size: 10px;">
              @if(!$isToOrder)
                @php
                  $cAddr = $invoice['consignee_address'] ?? ($consigner->registered_address ?? ($consigner->factory_address ?? ''));
                @endphp
                @if(!empty($cAddr))
                  <strong>ADD:</strong> {{ $cAddr }}
                @endif
              @endif
            </div>
            <div style="font-size: 10px; margin-top: 2px;">
              @if(!$isToOrder)
                <strong>TAX CODE:</strong> <span>{{ $invoice['consignee_tax'] ?? ($consigner->gstin ?? ($consigner->pan ?? '')) }}</span><br>
                <strong>TEL:</strong> <span>{{ $invoice['consignee_phone'] ?? ($consigner->contact_phone ?? '') }}</span><br>
                <strong>EMAIL:</strong> <span>{{ $invoice['consignee_email'] ?? ($consigner->contact_email ?? '') }}</span>
              @endif
            </div>
          </td>

          <!-- Buyer (If other than Consignee) (50%) -->
          <td class="p-2 align-top" style="width: 50%;">
            <div class="text-decoration-underline fw-bold mb-1" style="font-size: 11px;">Buyer (If other than Consignee):</div>
            <div class="fw-bold text-uppercase mb-1" style="font-size: 11px;">
              {{ $invoice['buyer_name'] ?? ($buyerOther['name'] ?? '') }}
            </div>
            <div style="font-size: 10px;">
              @php
                $bAddr = $invoice['buyer_address'] ?? ($buyerOther['address'] ?? '');
              @endphp
              @if(!empty($bAddr))
                <strong>ADD:</strong> {{ $bAddr }}
              @endif
            </div>
            <div style="font-size: 10px; margin-top: 2px;">
              <strong>TAX CODE:</strong> <span>{{ $invoice['buyer_tax'] ?? ($buyerOther['tax_code'] ?? '') }}</span><br>
              <strong>TEL:</strong> <span>{{ $invoice['buyer_phone'] ?? ($buyerOther['phone'] ?? '') }}</span><br>
              <strong>EMAIL:</strong> <span>{{ $invoice['buyer_email'] ?? ($buyerOther['email'] ?? '') }}</span>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Packing List Table Column Headers & Item Rows -->
    <table class="table-packing-items w-100" style="border-collapse: collapse; table-layout: fixed; font-size: 10.5px; width: 100%;">
      <colgroup>
        <col style="width: 9%;">
        <col style="width: 13%;">
        <col style="width: 11%;">
        <col style="width: 27%;">
        <col style="width: 9%;">
        <col style="width: 10%;">
        <col style="width: 10%;">
        <col style="width: 11%;">
      </colgroup>
      <thead>
        <tr style="border-bottom: 1.5px solid #000; background: #f1f5f9; text-align: center; font-size: 10px; font-weight: bold;">
          <th class="p-1" style="border-right: 1px solid #000;">Marks &amp; Nos</th>
          <th class="p-1" style="border-right: 1px solid #000;">Container No.</th>
          <th class="p-1" style="border-right: 1px solid #000;">Trailer No.</th>
          <th class="p-1" style="border-right: 1px solid #000;">Description of Goods</th>
          <th class="p-1" style="border-right: 1px solid #000;">HSN Code</th>
          <th class="p-1" style="border-right: 1px solid #000;">No. of Pkgs / Pieces</th>
          <th class="p-1" style="border-right: 1px solid #000;">Quantity<br>(SQM.)</th>
          <th class="p-1">Weights (KGS)<br>Net / Gross</th>
        </tr>
      </thead>

      <!-- Packing List Item Rows -->
      <tbody>
        @forelse($packingRows as $r)
        <tr style="border-bottom: 1px solid #000; font-size: 10px;">
          <td class="p-1.5 text-center align-top font-monospace" style="border-right: 1px solid #000;">
            <div class="fw-bold">{{ $r['marks_numbers'] }}</div>
          </td>
          <td class="p-1.5 text-center align-top font-monospace" style="border-right: 1px solid #000;">
            <div class="fw-bold">{{ $r['container_no'] }}</div>
          </td>
          <td class="p-1.5 text-center align-top font-monospace" style="border-right: 1px solid #000;">
            <div>{{ $r['trailer_no'] ?: '-' }}</div>
          </td>
          <td class="p-1.5 align-top" style="border-right: 1px solid #000;">
            <div class="fw-bold text-uppercase">{!! nl2br(e($r['description'])) !!}</div>
          </td>
          <td class="p-1.5 text-center align-top font-monospace" style="border-right: 1px solid #000;">
            {{ $r['hsn_code'] }}
          </td>
          <td class="p-1.5 text-center align-top font-monospace fw-bold" style="border-right: 1px solid #000;">
            {{ $r['packages'] }}
          </td>
          <td class="p-1.5 text-end align-top fw-bold font-monospace" style="border-right: 1px solid #000;">
            {{ number_format($r['quantity'], 3) }}
          </td>
          <td class="p-1.5 text-end align-top font-monospace">
            <div><span class="text-muted small">N:</span> {{ number_format($r['net_weight'], 0, '.', '') }}</div>
            <div><span class="text-muted small">G:</span> {{ number_format($r['gross_weight'], 0, '.', '') }}</div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" class="p-3 text-center text-muted">No items recorded in this packing list.</td>
        </tr>
        @endforelse
      </tbody>

      <!-- Totals Summary Row -->
      <tbody>
        <tr style="border-bottom: 1.5px solid #000; font-size: 11px; background: #fafafa;">
          <td colspan="5" class="p-1.5 fw-bold text-end" style="border-right: 1px solid #000;">
            TOTAL CONSIGNMENT SUMMARY:
          </td>
          <td class="p-1.5 text-center fw-bold font-monospace" style="border-right: 1px solid #000;">
            {{ $totalPackages > 0 ? $totalPackages . ' PKGS' : '-' }}
          </td>
          <td class="p-1.5 text-end fw-black font-monospace" style="border-right: 1px solid #000;">
            {{ number_format($finalTotalQty, 3) }} SQM
          </td>
          <td class="p-1.5 text-end font-monospace fw-bold">
            <div>N: {{ number_format($finalNet, 0, '.', '') }} KG</div>
            <div>G: {{ number_format($finalGross, 0, '.', '') }} KG</div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Bottom Declaration & Authorised Signatory Block: Clean 50% / 50% Split -->
    <table class="w-100" style="border-collapse: collapse; table-layout: fixed; font-size: 10px; width: 100%;">
      <colgroup>
        <col style="width: 50%;">
        <col style="width: 50%;">
      </colgroup>
      <tbody>
        <tr style="border-bottom: 1.5px solid #000;">
          <!-- Left: Declaration (50%) -->
          <td class="p-3 align-top" style="width: 50%; border-right: 1.5px solid #000; background: #fff;">
            <div class="fw-bold text-decoration-underline mb-1">Declaration</div>
            <div style="font-size: 9.5px; line-height: 1.5; color: #222;">
              We declare that this Commercial Packing List shows the actual quantity, packages, weights, and specifications of the goods described and all particulars given are true and correct as per E.&amp; O.E.
            </div>
          </td>

          <!-- Right: Authorised Signatory (50%) -->
          <td class="p-3 text-center align-bottom" style="width: 50%; height: 110px; background: #fff;">
            <div class="fw-bold mb-1" style="font-size: 11px;">For : {{ $certifications['signatory_company'] ?? ($exporter['name'] ?? 'RELIANCE GRANITO') }}</div>
            
            <div style="height: 45px;"></div>
            
            <div class="fw-bold" style="font-size: 9.5px; border-top: 1px dotted #333; padding-top: 3px; display: inline-block; min-width: 200px;">
              {{ $certifications['signatory_title'] ?? ($settings->signatory_title ?? 'Partner / Manager') }}
            </div>
          </td>
        </tr>
      </tbody>
    </table>

  </div>
</div>
