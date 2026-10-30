@extends('layouts.app')

@section('content')
<main class="content-area p-3 p-lg-4" id="invoiceShowApp">

  <!-- Breadcrumb & Page Header (Hidden when printing) -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 d-print-none">
    <div>
      <nav aria-label="breadcrumb" class="mb-1">
        <ol class="breadcrumb mb-0 small">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-house-door me-1"></i>Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Commercial Invoices</a></li>
          <li class="breadcrumb-item active" aria-current="page">Invoice #{{ $invoiceModel->invoice_number }}</li>
        </ol>
      </nav>
      <div class="d-flex align-items-center gap-2">
        <h2 class="fw-bold text-dark mb-0" style="font-size: 1.6rem; letter-spacing: -0.02em;">Commercial Invoice #{{ $invoiceModel->invoice_number }}</h2>
        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1" style="font-size: 0.78rem;">
          <i class="bi bi-shield-check me-1"></i> {{ $invoiceModel->status ?? 'Active' }}
        </span>
      </div>
      <p class="text-muted small mb-0 mt-1">
        Consignee: <strong class="text-dark">{{ $invoiceModel->consigner_name }}</strong> &bull; Container: <strong class="text-dark font-monospace">{{ $invoiceModel->container_number }}</strong> &bull; Date: <strong class="text-dark">{{ $invoiceModel->invoice_date }}</strong>
      </p>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
      <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-2">
        <i class="bi bi-arrow-left"></i>
        <span>All Invoices</span>
      </a>

      <a href="{{ route('invoices.create') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 px-3 py-2">
        <i class="bi bi-plus-circle"></i>
        <span>New Invoice</span>
      </a>

      <a href="{{ route('invoices.edit', $invoiceModel) }}" class="btn btn-warning d-inline-flex align-items-center gap-2 px-3 py-2 fw-semibold">
        <i class="bi bi-pencil-square"></i>
        <span>Edit Invoice</span>
      </a>

      <a href="{{ route('invoices.packing-list', $invoiceModel) }}" class="btn btn-outline-info d-inline-flex align-items-center gap-2 px-3 py-2 fw-semibold">
        <i class="bi bi-box2"></i>
        <span>Packing List</span>
      </a>

      <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3.5 py-2 shadow-sm fw-semibold" onclick="window.print()">
        <i class="bi bi-printer-fill"></i>
        <span>Print / Save PDF</span>
      </button>

      <form action="{{ route('invoices.destroy', $invoiceModel) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete Commercial Invoice #{{ $invoiceModel->invoice_number }}?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger d-inline-flex align-items-center gap-2 px-3 py-2" title="Delete Invoice">
          <i class="bi bi-trash"></i>
          <span>Delete</span>
        </button>
      </form>
    </div>
  </div>

  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4 d-print-none" role="alert" style="border-radius: 10px;">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-check-circle-fill fs-5 text-success"></i>
      <div class="fw-semibold">{{ session('success') }}</div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <!-- Invoice Quick Summary Bar (d-print-none) -->
  <div class="card border-0 shadow-sm mb-4 d-print-none" style="border-radius: 12px; border: 1px solid #E2E8F0;">
    <div class="card-body p-3">
      <div class="row g-3 align-items-center text-center text-md-start">
        <div class="col-6 col-md-3 border-end">
          <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Total Quantity</div>
          <div class="fs-5 fw-bold font-monospace text-dark">{{ number_format($invoiceModel->total_quantity, 3) }} SQ MT.</div>
        </div>
        <div class="col-6 col-md-3 border-end">
          <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Total CIF Amount</div>
          <div class="fs-5 fw-bold font-monospace text-primary">₹{{ number_format($invoiceModel->rounded_amount, 2) }}</div>
        </div>
        <div class="col-6 col-md-3 border-end">
          <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Line Items</div>
          <div class="fs-5 fw-bold text-dark">{{ count($items) }} Product {{ count($items) === 1 ? 'Item' : 'Items' }}</div>
        </div>
        <div class="col-6 col-md-3">
          <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Port Destination</div>
          <div class="fs-6 fw-bold text-dark text-truncate">{{ $invoice['port_of_discharge'] ?? '-' }}</div>
        </div>
      </div>
    </div>
  </div>

  <!-- The Full Commercial Invoice Table Sheet -->
  <div class="card border-0 shadow-sm mx-auto" style="border-radius: 12px; border: 1px solid #E2E8F0; overflow: hidden; max-width: 1080px;">
    
    <div class="card-header bg-white py-3 px-3 d-flex align-items-center justify-content-between border-bottom d-print-none">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-file-earmark-text text-primary"></i>
        <span class="fw-bold text-dark">Official Commercial Export Document Preview</span>
      </div>
      <div class="d-flex align-items-center gap-2">
        <span class="badge bg-light text-muted border font-monospace">A4 Legal Export Layout</span>
        <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2.5" onclick="window.print()">
          <i class="bi bi-printer me-1"></i> Print / PDF
        </button>
      </div>
    </div>

    <div class="card-body p-2 p-md-4 bg-light overflow-auto">
      <x-commercial-invoice-table 
        :exporter="$exporter"
        :invoice="$invoice"
        :consigner="$consigner"
        :buyerOther="$buyerOther"
        :items="$items"
        :totalQuantity="$totalQuantity"
        :totalAmount="$totalAmount"
        :roundedAmount="$roundedAmount"
        :amountInWords="$amountInWords"
        :bank="$bank"
        :certifications="$certifications"
        :settings="$settings"
      />
    </div>

  </div>

</main>

<style>
@media print {
  /* Hide extraneous elements */
  .app-sidebar, 
  .app-header, 
  .app-footer, 
  .d-print-none, 
  .content-area > nav, 
  .top-navbar, 
  .sidebar-backdrop {
    display: none !important;
  }
  
  body, .content-area, .main-wrapper, .app-container {
    background: #fff !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  .card {
    border: none !important;
    box-shadow: none !important;
    background: transparent !important;
  }

  .card-body {
    background: transparent !important;
    padding: 0 !important;
  }

  .commercial-invoice-sheet {
    box-shadow: none !important;
    border: none !important;
    padding: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
  }

  .table-invoice-main {
    width: 100% !important;
  }

  .invoice-badges-table {
    width: 100% !important;
    table-layout: fixed !important;
  }

  .invoice-badges-table .badge-item {
    padding: 2px 1px !important;
  }
}
</style>
@endsection
