@extends('layouts.app')

@section('content')
<main class="content-area p-3 p-lg-4" id="packingListApp">

  <!-- Breadcrumb & Page Header (Hidden when printing) -->
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 d-print-none">
    <div>
      <nav aria-label="breadcrumb" class="mb-1">
        <ol class="breadcrumb mb-0 small">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-house-door me-1"></i>Dashboard</a></li>
          <li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Commercial Invoices</a></li>
          <li class="breadcrumb-item"><a href="{{ route('invoices.show', $invoiceModel) }}">#{{ $invoiceModel->invoice_number }}</a></li>
          <li class="breadcrumb-item active" aria-current="page">Packing List</li>
        </ol>
      </nav>
      <div class="d-flex align-items-center gap-2">
        <h2 class="fw-bold text-dark mb-0" style="font-size: 1.6rem; letter-spacing: -0.02em;">Commercial Packing List</h2>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1" style="font-size: 0.78rem;">
          <i class="bi bi-box2-fill me-1"></i> Invoice #{{ $invoiceModel->invoice_number }}
        </span>
      </div>
      <p class="text-muted small mb-0 mt-1">
        Consignee: <strong class="text-dark">{{ $invoice['consignee_name'] ?? $invoiceModel->consigner_name }}</strong> &bull; Container: <strong class="text-dark font-monospace">{{ $invoiceModel->container_number }}</strong> &bull; Total Packages: <strong class="text-dark">{{ $invoiceModel->total_packages ?: count($items) }}</strong>
      </p>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
      <a href="{{ route('invoices.show', $invoiceModel) }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-2">
        <i class="bi bi-arrow-left"></i>
        <span>Commercial Invoice</span>
      </a>

      <a href="{{ route('invoices.edit', $invoiceModel) }}" class="btn btn-outline-warning d-inline-flex align-items-center gap-2 px-3 py-2">
        <i class="bi bi-pencil-square"></i>
        <span>Edit Consignment</span>
      </a>

      <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3.5 py-2 shadow-sm fw-semibold" onclick="window.print()">
        <i class="bi bi-printer-fill"></i>
        <span>Print Packing List</span>
      </button>
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

  <!-- The Full Commercial Packing List Table Sheet -->
  <div class="card border-0 shadow-sm mx-auto" style="border-radius: 12px; border: 1px solid #E2E8F0; overflow: hidden; max-width: 1080px;">
    
    <div class="card-header bg-white py-3 px-3 d-flex align-items-center justify-content-between border-bottom d-print-none">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-box-seam-fill text-primary"></i>
        <span class="fw-bold text-dark">Official Commercial Packing List Preview</span>
      </div>
      <div class="d-flex align-items-center gap-2">
        <span class="badge bg-light text-muted border font-monospace">A4 Legal Export Layout</span>
        <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2.5" onclick="window.print()">
          <i class="bi bi-printer me-1"></i> Print / Save PDF
        </button>
      </div>
    </div>

    <div class="card-body p-2 p-md-4 bg-light overflow-auto">
      <x-commercial-packing-list-table 
        :exporter="$exporter"
        :invoice="$invoice"
        :consigner="$consigner"
        :buyerOther="$buyerOther"
        :items="$items"
        :totalQuantity="$totalQuantity"
        :bank="$bank"
        :certifications="$certifications"
        :settings="$settings"
      />
    </div>

  </div>

</main>

<style>
@media print {
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

  .commercial-packing-list-sheet {
    box-shadow: none !important;
    border: none !important;
    padding: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
  }

  .table-packing-main {
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

