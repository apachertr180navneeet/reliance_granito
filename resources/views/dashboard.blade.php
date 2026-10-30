@extends('layouts.app')

@section('content')
<main class="content-area p-3 p-lg-4" id="overviewSection">
    
    <!-- Welcome Banner / Active Export Docket Hero -->
    <div class="card border-0 shadow-sm mb-4 dashboard-hero-card">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <span class="badge bg-warning text-dark fw-bold mb-2 text-uppercase tracking-wider">Active Export Docket</span>
                    <h3 class="fw-bold mb-1">Reliance Granito &bull; Export Control Tower</h3>
                    <p class="text-white-50 mb-0">Consignment #1469 &bull; Jodhpur Export Office & Facility &bull; Destination: Haiphong Port, Vietnam</p>
                </div>
                <div>
                    <a href="{{ route('pdf-editor.index', ['doc' => '1469 SB ORIGINAL.PDF']) }}" class="btn btn-warning fw-bold d-flex align-items-center gap-2 shadow-sm px-4 py-2">
                        <i class="bi bi-file-earmark-diff fs-5"></i>
                        <span>Launch PDF Visual Editor</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Operational Action Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 dashboard-action-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">PRIMARY WORKFLOW</span>
                        <span class="badge bg-primary-subtle text-primary">PDF Tool</span>
                    </div>
                    <h5 class="fw-bold mb-1">PDF Corrections</h5>
                    <p class="text-muted small mb-3">Upload export PDFs, draw solid whiteout masks to hide sensitive prices, replace text values, and download updated copies.</p>
                    <a href="{{ route('pdf-editor.index') }}" class="btn btn-outline-primary btn-sm w-100">
                        Open PDF Editor &rarr;
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 dashboard-action-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">PARTY DIRECTORY</span>
                        <span class="badge bg-primary-subtle text-primary">{{ $stats['total_consigners'] ?? 4 }} Registered</span>
                    </div>
                    <h5 class="fw-bold mb-1">Consigners Master</h5>
                    <p class="text-muted small mb-3">Manage export consignors, IEC licenses, GSTIN, factory works addresses, and authorized banks.</p>
                    <a href="{{ route('consigners.index') }}" class="btn btn-primary btn-sm w-100">
                        Manage Consigners &rarr;
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 dashboard-action-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">ACTIVE DOCKET</span>
                        <span class="badge bg-success-subtle text-success">Verified</span>
                    </div>
                    <h5 class="fw-bold mb-1">Consignment 1469</h5>
                    <p class="text-muted small mb-3">Shipping Bill, Commercial Invoice & BL for CJ Stone Co. Ltd (Vietnam). Ready for review and redaction.</p>
                    <a href="{{ route('pdf-editor.index', ['doc' => '1469 INVOICE ORIGINAL.pdf']) }}" class="btn btn-outline-secondary btn-sm w-100">
                        Open Invoice in Editor &rarr;
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 dashboard-action-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">COMPLIANCE</span>
                        <span class="badge bg-info-subtle text-info">EDI Standard</span>
                    </div>
                    <h5 class="fw-bold mb-1">ICEGATE & Customs</h5>
                    <p class="text-muted small mb-3">Export Inspection Agency, Mundra Port Gateway, and RFID electronic seal verification dockets.</p>
                    <a href="{{ route('pdf-editor.index', ['doc' => '1469 SB ORIGINAL.PDF']) }}" class="btn btn-outline-secondary btn-sm w-100">
                        Open Shipping Bill &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Consignment Specification & Details -->
    @if($shipment)
    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
        <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary text-white font-monospace">{{ $shipment->shipment_number }}</span>
                <h5 class="fw-bold text-dark mb-0">Consignment Particulars &bull; CJ Stone Co. Ltd (Hai Phong, Vietnam)</h5>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                    <i class="bi bi-shield-check me-1"></i>{{ $shipment->status }}
                </span>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                    {{ $shipment->review_status }}
                </span>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <div class="text-muted small fw-semibold mb-1">BUYER / IMPORTER</div>
                        <div class="fw-bold text-dark">{{ $shipment->buyer_name }}</div>
                        <div class="small text-muted mt-1">{{ $shipment->buyer_address }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <div class="text-muted small fw-semibold mb-1">ROUTING & PORTS</div>
                        <div class="fw-bold text-dark">{{ $shipment->destination_port }}</div>
                        <div class="small text-muted mt-1">POL: {{ $shipment->pol_name }}</div>
                        <div class="badge bg-secondary-subtle text-dark mt-2">{{ $shipment->incoterms }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <div class="text-muted small fw-semibold mb-1">CARGO & QUANTITY</div>
                        <div class="fw-bold text-dark">{{ $shipment->total_quantity }}</div>
                        <div class="small text-muted mt-1">{{ $shipment->total_packages }} {{ $shipment->package_type }}</div>
                        <div class="small text-muted">Gross Wt: {{ $shipment->gross_weight }} &bull; Net: {{ $shipment->net_weight }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded-3 h-100">
                        <div class="text-muted small fw-semibold mb-1">CONTAINER & VALUE</div>
                        <div class="fw-bold text-primary font-monospace">{{ $shipment->container_number }}</div>
                        <div class="small text-muted mt-1">RFID Seal: <span class="font-monospace fw-semibold">{{ $shipment->seal_number }}</span></div>
                        <div class="fw-bold text-success mt-2">${{ number_format($shipment->invoice_value, 2) }} {{ $shipment->currency }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Authentic Export Documents Catalogue (MySQL Powered) -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px; overflow:hidden;">
        <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-folder-fill text-primary fs-5"></i>
                <h5 class="fw-bold text-dark mb-0">Authentic Export Documents Repository</h5>
                <span class="badge bg-primary-subtle text-primary">{{ $documents->count() }} Files in MySQL</span>
            </div>
            <div>
                <a href="{{ route('pdf-editor.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-file-earmark-diff me-1"></i> Open Empty Editor
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase small" style="font-size:0.75rem; letter-spacing:0.04em;">
                    <tr>
                        <th class="ps-4 py-3">Document Title & Filename</th>
                        <th class="py-3">Type / Category</th>
                        <th class="py-3">File Size</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="pe-4 py-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary-subtle text-primary rounded p-2 d-flex align-items-center justify-content-center" style="width:38px; height:38px;">
                                    <i class="bi bi-file-earmark-pdf fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark mb-0">{{ $doc->title }}</div>
                                    <div class="small text-muted font-monospace">{{ $doc->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($doc->doc_type === 'shipping_bill')
                                <span class="badge bg-info-subtle text-info border border-info-subtle">Shipping Bill</span>
                            @elseif($doc->doc_type === 'invoice')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Commercial Invoice</span>
                            @elseif($doc->doc_type === 'bill_of_lading')
                                <span class="badge bg-warning-subtle text-dark border border-warning-subtle">Bill of Lading</span>
                            @elseif($doc->doc_type === 'masked')
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Redacted Mask</span>
                            @else
                                <span class="badge bg-light text-dark border">Workflow Guide</span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            {{ number_format($doc->file_size / 1024, 1) }} KB
                        </td>
                        <td class="text-center">
                            @if($doc->status === 'Original')
                                <span class="badge bg-success-subtle text-success"><i class="bi bi-check2-circle me-1"></i>Original</span>
                            @elseif($doc->status === 'Masked')
                                <span class="badge bg-secondary-subtle text-secondary"><i class="bi bi-eye-slash me-1"></i>Masked</span>
                            @elseif($doc->status === 'Edited')
                                <span class="badge bg-primary-subtle text-primary"><i class="bi bi-pencil-square me-1"></i>Amended</span>
                            @else
                                <span class="badge bg-light text-muted">{{ $doc->status }}</span>
                            @endif
                        </td>
                        <td class="pe-4 text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('pdf-editor.index', ['doc' => $doc->name]) }}" class="btn btn-outline-primary" title="Open in Visual Editor">
                                    <i class="bi bi-file-earmark-diff me-1"></i> Edit in Tool
                                </a>
                                <a href="{{ route('documents.stream', ['filename' => $doc->name]) }}" target="_blank" class="btn btn-outline-secondary" title="View or Stream PDF">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No documents registered.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</main>
@endsection
