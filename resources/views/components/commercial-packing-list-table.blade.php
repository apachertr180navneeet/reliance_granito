@props([
  'exporter' => [],
  'invoice' => [],
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
  $format = strtoupper($invoice['invoice_format'] ?? 'LUT');
  $term = strtoupper($invoice['delivery_term'] ?? 'CIF');
  $curr = strtoupper($invoice['currency'] ?? 'USD');
  $consigneeMode = $invoice['consignee_mode'] ?? 'consigner';
  $isToOrder = ($consigneeMode === 'to_the_order' || strtoupper($invoice['consignee_name'] ?? '') === 'TO THE ORDER');
  $noOfContainers = (int)($invoice['no_of_containers'] ?? 1);
  $grossWt = $invoice['gross_weight'] ?? (26500 * max(1, $noOfContainers));
  $netWt = $invoice['net_weight'] ?? (25500 * max(1, $noOfContainers));
  $isoCert = $invoice['iso_certification'] ?? ($certifications['iso_certification'] ?? ($settings->iso_certification ?? 'ISO 9001:2015 IC/9534879/24'));
  $notifyParties = is_array($invoice['notify_parties'] ?? null) ? $invoice['notify_parties'] : [];
  $pkgType = $invoice['package_type'] ?? 'Wooden Bundles / Pallets';

  // Compute total pieces/packages and item-level weights if not specifically overridden
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
          : ($itemCount > 0 ? round(($netWt / $itemCount), 2) : $netWt);
      $itemGross = isset($it['gross_weight']) && (float)$it['gross_weight'] > 0 
          ? (float)$it['gross_weight'] 
          : ($itemCount > 0 ? round(($grossWt / $itemCount), 2) : $grossWt);

      $computedNetWt += $itemNet;
      $computedGrossWt += $itemGross;

      $packingRows[] = [
          'marks_numbers' => $it['marks_numbers'] ?? ('20 FCL ( ' . ($idx + 1) . ' )'),
          'container_no' => $it['container_no'] ?? ($invoice['container_number'] ?? ''),
          'trailer_no' => $it['trailer_no'] ?? ($invoice['trailer_number'] ?? ''),
          'description' => $it['description'] ?? '',
          'hsn_code' => $it['hsn_code'] ?? '68022390',
          'packages' => $pkgs > 0 ? $pkgs : '-',
          'quantity' => $qty,
          'net_weight' => $itemNet,
          'gross_weight' => $itemGross,
      ];
  }

  // Fallback to top-level if computed is 0
  if ($totalPackages === 0 && !empty($invoice['total_packages'])) {
      $totalPackages = (int)$invoice['total_packages'];
  }
  $finalGross = $grossWt > 0 ? $grossWt : $computedGrossWt;
  $finalNet = $netWt > 0 ? $netWt : $computedNetWt;
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
            EXPORT CONSIGNMENT DETAILS &amp; CONTAINER SPECIFICATIONS
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
              <tr style="border-bottom: 1px solid #000;">
                <td colspan="2" class="p-1" style="font-size: 9px;">
                  <strong>Document Type :</strong> <span class="fw-semibold">COMMERCIAL PACKING LIST &amp; SPECIFICATIONS</span>
                </td>
              </tr>
              <tr>
                <td class="p-1" style="border-right: 1px solid #000; width: 50%;">
                  <div class="fw-bold text-uppercase" style="font-size: 9px;">CONTAINER NO.</div>
                  <div class="fw-bold font-monospace">{{ $invoice['container_number'] ?? '-' }}</div>
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

        <!-- Optional Notify Parties Section -->
        @if(!empty($notifyParties))
        <tr style="border-bottom: 1.5px solid #000; font-size: 10px;">
          <td colspan="2" class="p-2" style="background: #fafafa;">
            <div class="text-decoration-underline fw-bold mb-1" style="font-size: 10px;">Notify Party / Parties:</div>
            <div class="row g-2">
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
                  <div class="fw-semibold">{{ $invoice['pre_carriage_by'] ?? 'TRUCK' }}</div>
                </td>
                <td class="p-1" style="width: 50%;">
                  <div class="fw-bold" style="font-size: 9px;">Place of Receipt by Pre-Carrier</div>
                  <div class="fw-semibold">{{ $invoice['place_of_receipt'] ?? 'CHENNAI PORT' }}</div>
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
                  <div class="fw-semibold">{{ $invoice['country_of_origin'] ?? 'INDIA' }}</div>
                </td>
                <td class="p-1" style="width: 50%;">
                  <div class="fw-bold" style="font-size: 9px;">Country of Final Destination</div>
                  <div class="fw-semibold">{{ $invoice['country_of_destination'] ?? 'VIETNAM' }}</div>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Row 4: Vessel / Port & Container Particulars -->
        <tr style="border-bottom: 2px solid #000; font-size: 10px;">
          <!-- Left: Vessel & Ports (50% total) -->
          <td class="p-0 align-top" style="width: 50%; border-right: 1.5px solid #000;">
            <table class="w-100" style="border-collapse: collapse; table-layout: fixed;">
              <tr style="border-bottom: 1px solid #000;">
                <td class="p-1" style="width: 50%; border-right: 1px solid #000;">
                  <div class="fw-bold" style="font-size: 9px;">Vessel / Flight No.</div>
                  <div class="fw-semibold">{{ $invoice['vessel_flight_no'] ?? '-' }}</div>
                </td>
                <td class="p-1" style="width: 50%;">
                  <div class="fw-bold" style="font-size: 9px;">Port of Loading</div>
                  <div class="fw-semibold">{{ $invoice['port_of_loading'] ?? 'CHENNAI PORT' }}</div>
                </td>
              </tr>
              <tr>
                <td class="p-1" style="width: 50%; border-right: 1px solid #000;">
                  <div class="fw-bold" style="font-size: 9px;">Port of Discharge</div>
                  <div class="fw-semibold">{{ $invoice['port_of_discharge'] ?? 'CATLAI PORT, VIETNAM' }}</div>
                </td>
                <td class="p-1" style="width: 50%;">
                  <div class="fw-bold" style="font-size: 9px;">Final Destination</div>
                  <div class="fw-semibold">{{ $invoice['final_destination'] ?? 'CATLAI PORT, VIETNAM' }}</div>
                </td>
              </tr>
            </table>
          </td>

          <!-- Right: Container Particulars (50% total) -->
          <td class="p-2 align-middle text-center" style="width: 50%; background: #fafafa;">
            <div class="fw-bold mb-1" style="font-size: 10px;">Container Particulars</div>
            <div class="fw-bold text-dark font-monospace" style="font-size: 11px;">
              {{ $noOfContainers }} &times; {{ $invoice['container_type'] ?? '20 FCL' }}
            </div>
            <div class="small text-muted font-monospace mt-0.5">
              Trailer: {{ $invoice['trailer_number'] ?? '-' }}
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Packing List Table Column Headers & Item Rows -->
    <table class="table-packing-items w-100" style="border-collapse: collapse; table-layout: fixed; font-size: 10.5px; width: 100%;">
      <colgroup>
        <col style="width: 10%;">
        <col style="width: 14%;">
        <col style="width: 32%;">
        <col style="width: 10%;">
        <col style="width: 10%;">
        <col style="width: 12%;">
        <col style="width: 12%;">
      </colgroup>
      <thead>
        <tr style="border-bottom: 1.5px solid #000; background: #f1f5f9; text-align: center; font-size: 10px; font-weight: bold;">
          <th class="p-1" style="width: 10%; border-right: 1px solid #000;">Marks & Nos</th>
          <th class="p-1" style="width: 14%; border-right: 1px solid #000;">Container No. / Trailer</th>
          <th class="p-1" style="width: 32%; border-right: 1px solid #000;">Description of Goods</th>
          <th class="p-1" style="width: 10%; border-right: 1px solid #000;">HSN Code</th>
          <th class="p-1" style="width: 10%; border-right: 1px solid #000;">No. of Pkgs / Pieces</th>
          <th class="p-1" style="width: 12%; border-right: 1px solid #000;">Quantity<br>(SQM.)</th>
          <th class="p-1" style="width: 12%;">Weights (KGS)<br>Net / Gross</th>
        </tr>
      </thead>

      <!-- Packing List Item Rows -->
      <tbody>
        @forelse($packingRows as $r)
        <tr style="border-bottom: 1px solid #000; font-size: 10.5px;">
          <td class="p-1.5 text-center align-top font-monospace" style="border-right: 1px solid #000;">
            <div class="fw-bold">{{ $r['marks_numbers'] }}</div>
          </td>
          <td class="p-1.5 text-center align-top font-monospace" style="border-right: 1px solid #000;">
            <div class="fw-bold">{{ $r['container_no'] }}</div>
            @if(!empty($r['trailer_no']))
              <div class="small text-muted">{{ $r['trailer_no'] }}</div>
            @endif
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
          <td colspan="7" class="p-3 text-center text-muted">No items recorded in this packing list.</td>
        </tr>
        @endforelse
      </tbody>

      <!-- Totals & Sub-Sections Tbody -->
      <tbody>
        <!-- Origin & Subtotal Row -->
        <tr style="border-bottom: 1.5px solid #000; font-size: 11px; background: #fafafa;">
          <td colspan="4" class="p-1.5 fw-bold" style="border-right: 1px solid #000;">
            TOTAL SHIPMENT SUMMARY
          </td>
          <td class="p-1.5 text-center fw-bold font-monospace" style="border-right: 1px solid #000;">
            {{ $totalPackages > 0 ? $totalPackages . ' PKGS' : '-' }}
          </td>
          <td class="p-1.5 text-end fw-black font-monospace" style="border-right: 1px solid #000;">
            {{ number_format($totalQuantity > 0 ? $totalQuantity : $totalQty, 3) }} SQM
          </td>
          <td class="p-1.5 text-end font-monospace fw-bold">
            <div>N: {{ number_format($finalNet, 0, '.', '') }} KG</div>
            <div>G: {{ number_format($finalGross, 0, '.', '') }} KG</div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Lower Specifications & Authorised Signatory Block: Clean 50% / 50% Split -->
    <table class="w-100" style="border-collapse: collapse; table-layout: fixed; font-size: 10px; width: 100%;">
      <colgroup>
        <col style="width: 50%;">
        <col style="width: 50%;">
      </colgroup>
      <tbody>
        <!-- Consignment Specs & Certified Weights Breakdown -->
        <tr style="border-bottom: 1.5px solid #000;">
          <!-- Left: Consignment Specs (50%) -->
          <td class="p-2 align-top" style="width: 50%; border-right: 1.5px solid #000;">
            <div class="fw-bold mb-1">CONSIGNMENT SPECIFICATIONS:</div>
            <div><strong>Total Containers:</strong> {{ $noOfContainers }} &times; {{ $invoice['container_type'] ?? '20 FCL' }}</div>
            <div><strong>Primary Container No:</strong> <span class="font-monospace fw-bold">{{ $invoice['container_number'] ?? '-' }}</span></div>
            <div><strong>Trailer / Vehicle:</strong> <span class="font-monospace fw-bold">{{ $invoice['trailer_number'] ?? '-' }}</span></div>
            <div><strong>Port of Loading:</strong> {{ $invoice['port_of_loading'] ?? 'CHENNAI PORT' }}</div>
            <div><strong>Port of Discharge:</strong> {{ $invoice['port_of_discharge'] ?? 'CATLAI PORT, VIETNAM' }}</div>
          </td>

          <!-- Right: Certified Weights Breakdown (50%) -->
          <td class="p-2 align-middle font-monospace" style="width: 50%; background: #fafafa;">
            <div class="fw-bold mb-1.5 text-uppercase" style="font-size: 10.5px; border-bottom: 1px solid #ddd; padding-bottom: 2px;">
              Certified Weights Breakdown
            </div>
            <div class="d-flex justify-content-between mb-1">
              <span>TOTAL NET WEIGHT (N.WT):</span>
              <span class="fw-black fs-7">{{ number_format($finalNet, 0, '.', '') }} KGS</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
              <span>TOTAL GROSS WEIGHT (G.WT):</span>
              <span class="fw-black fs-7 text-primary">{{ number_format($finalGross, 0, '.', '') }} KGS</span>
            </div>
            <div class="d-flex justify-content-between text-muted small">
              <span>TOTAL ESTIMATED TARE:</span>
              <span>{{ number_format(max(0, $finalGross - $finalNet), 0, '.', '') }} KGS</span>
            </div>
          </td>
        </tr>

        <!-- Badges, Recognition & Authorised Signatory Block -->
        <tr style="border-bottom: 1.5px solid #000;">
          <!-- Logos & Industry Certifications (Left 50%) -->
          <td class="p-1 align-middle" style="width: 50%; border-right: 1.5px solid #000;">
            <div class="invoice-badges-row text-center" style="display: flex !important; flex-direction: row !important; flex-wrap: nowrap !important; align-items: stretch !important; justify-content: space-between !important; gap: 3px !important; width: 100% !important;">
              
              <div class="badge-item p-1 border rounded" style="flex: 1 1 0; min-width: 0; border: 1px solid #cbd5e1 !important; border-radius: 4px; padding: 2px 1px !important;">
                <div style="font-weight: 900; color: #0284c7; font-size: 10px; line-height: 1.1;">&lt;FIEO&gt;</div>
                <div style="font-size: 7px; line-height: 1; word-break: break-word;">{{ $certifications['rcmc_fieo_no'] ?? ($settings->rcmc_fieo_no ?? 'RCMC/FIEO/11176/2025-2027') }}</div>
              </div>

              <div class="badge-item p-1 border rounded" style="flex: 1 1 0; min-width: 0; border: 1px solid #cbd5e1 !important; border-radius: 4px; padding: 2px 1px !important;">
                <div style="font-weight: 900; color: #b91c1c; font-size: 10px; line-height: 1.1;">FIGSI</div>
                <div style="font-size: 7px; line-height: 1;">Granite &amp; Stone<br>Industry</div>
              </div>

              <div class="badge-item p-1 border rounded" style="flex: 1.3 1 0; min-width: 0; border: 1px solid #cbd5e1 !important; border-radius: 4px; padding: 2px 1px !important;">
                <div style="font-weight: 900; color: #ea580c; font-size: 9.5px; line-height: 1.1; white-space: nowrap;">&#9733;&#9733; {{ $certifications['export_house_star'] ?? ($settings->export_house_star ?? 'Two Star Export House') }}</div>
                <div style="font-size: 7px; line-height: 1;">Govt. Recognized<br>Export House</div>
              </div>

              <div class="badge-item p-1 border rounded" style="flex: 1 1 0; min-width: 0; border: 1px solid #cbd5e1 !important; border-radius: 4px; padding: 2px 1px !important;">
                <div style="font-weight: 900; color: #1e3a8a; font-size: 10px; line-height: 1.1;">REPC</div>
                <div style="font-size: 7px; line-height: 1; word-break: break-word;">{{ $certifications['repc_no'] ?? ($settings->repc_no ?? 'REPC-22-10349') }}</div>
              </div>

              <div class="badge-item p-1 border rounded" style="flex: 1.1 1 0; min-width: 0; border: 1px solid #cbd5e1 !important; border-radius: 4px; padding: 2px 1px !important;">
                <div style="font-weight: 900; color: #15803d; font-size: 9.5px; line-height: 1.1; white-space: nowrap;">ISO Certified</div>
                <div style="font-size: 7px; line-height: 1; word-break: break-word;">{{ $isoCert }}</div>
              </div>

            </div>
          </td>

          <!-- Authorised Signatory (Right 50%) -->
          <td class="p-2 text-center align-bottom" style="width: 50%; height: 110px;">
            <div class="fw-bold mb-1" style="font-size: 11px;">For : {{ $certifications['signatory_company'] ?? ($exporter['name'] ?? 'RELIANCE GRANITO') }}</div>
            
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
            <div class="text-center">
              We declare that this packing list shows the actual quantity, weights, and particulars of the goods described and all details given are true and best as per E.&amp; O.E.
            </div>
          </td>
        </tr>
      </tbody>
    </table>

  </div>
</div>
