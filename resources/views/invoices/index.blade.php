@extends('layouts.app')

@section('content')
<main class="content-area p-3 p-lg-4" id="invoicesIndexApp">

  <!-- Breadcrumb & Page Header -->
  <div class="mb-4">
    <nav aria-label="breadcrumb" class="mb-2">
      <ol class="breadcrumb mb-0 small">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-house-door me-1"></i>Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Commercial Invoices</li>
      </ol>
    </nav>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div>
        <div class="d-flex align-items-center gap-2 mb-1">
          <h2 class="fw-bold text-dark mb-0" style="font-size: 1.6rem; letter-spacing: -0.02em;">Commercial Export Invoices</h2>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1" style="font-size: 0.78rem;">
            {{ $invoices->total() }} Records
          </span>
        </div>
        <p class="text-muted small mb-0">
          Manage, generate, edit, and print statutory export commercial invoices for Indian Customs, DGFT, and overseas port clearance.
        </p>
      </div>

      <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="{{ route('invoices.settings') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3" style="font-size: 0.88rem; font-weight: 500;">
          <i class="bi bi-sliders"></i>
          <span>Invoice Settings</span>
        </a>
        <a href="{{ route('invoices.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3.5 py-2 rounded-3 shadow-sm fw-semibold" style="font-size: 0.88rem;">
          <i class="bi bi-plus-circle"></i>
          <span>Create New Invoice</span>
        </a>
      </div>
    </div>
  </div>

  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 10px;">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-check-circle-fill fs-5 text-success"></i>
      <div class="fw-semibold">{{ session('success') }}</div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <!-- Filters & Search Bar Card -->
  <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #E2E8F0;">
    <div class="card-body p-3">
      <form action="{{ route('invoices.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-6 col-lg-7">
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by Invoice No, Consignee name, or Container..." value="{{ request('search') }}">
          </div>
        </div>

        <div class="col-md-4 col-lg-3">
          <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active / Approved</option>
            <option value="Draft" {{ request('status') === 'Draft' ? 'selected' : '' }}>Draft</option>
            <option value="Dispatched" {{ request('status') === 'Dispatched' ? 'selected' : '' }}>Dispatched</option>
          </select>
        </div>

        <div class="col-md-2 col-lg-2 d-flex gap-2">
          <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
          @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('invoices.index') }}" class="btn btn-light border text-muted" title="Reset Filters"><i class="bi bi-x-lg"></i></a>
          @endif
        </div>
      </form>
    </div>
  </div>

  <!-- Invoices Table Card -->
  <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #E2E8F0; overflow: hidden;">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
        <thead class="bg-light text-uppercase text-muted" style="font-size: 0.75rem; letter-spacing: 0.05em;">
          <tr>
            <th class="py-3 px-3">Invoice Details</th>
            <th class="py-3 px-3">Consignee / Buyer</th>
            <th class="py-3 px-3">Logistics & Container</th>
            <th class="py-3 px-3 text-end">Total SQ MT.</th>
            <th class="py-3 px-3 text-end">Total Amount (₹)</th>
            <th class="py-3 px-3 text-center">Status</th>
            <th class="py-3 px-3 text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($invoices as $inv)
          <tr>
            <!-- Invoice Details -->
            <td class="py-3 px-3">
              <a href="{{ route('invoices.show', $inv) }}" class="fw-bold text-primary text-decoration-none font-monospace fs-6">
                {{ $inv->invoice_number }}
              </a>
              <div class="small text-muted mt-0.5">
                <i class="bi bi-calendar3 me-1"></i>{{ $inv->invoice_date }}
              </div>
            </td>

            <!-- Consignee -->
            <td class="py-3 px-3" style="max-width: 260px;">
              <div class="fw-semibold text-dark text-truncate" title="{{ $inv->consigner_name }}">
                {{ $inv->consigner_name ?? 'Consignee Not Assigned' }}
              </div>
              @if(!empty($inv->buyer_other['name']) && $inv->buyer_other['name'] !== $inv->consigner_name)
                <div class="small text-muted text-truncate" title="Other Buyer">
                  <span class="badge bg-light text-secondary border py-0 px-1 me-1">Buyer:</span>{{ $inv->buyer_other['name'] }}
                </div>
              @endif
              @if($inv->consigner)
                <span class="badge bg-light text-primary border font-monospace mt-1" style="font-size: 0.72rem;">
                  IEC: {{ $inv->consigner->iec }}
                </span>
              @endif
            </td>

            <!-- Logistics & Container -->
            <td class="py-3 px-3 font-monospace small">
              <div class="fw-bold text-dark">
                <i class="bi bi-box me-1 text-secondary"></i>{{ $inv->container_number ?? '-' }}
              </div>
              <div class="text-muted" style="font-size: 0.78rem;">
                {{ $inv->shipping_logistics['port_of_loading'] ?? 'CHENNAI' }} &rarr; {{ $inv->shipping_logistics['port_of_discharge'] ?? 'CATLAI' }}
              </div>
            </td>

            <!-- Total SQMT -->
            <td class="py-3 px-3 text-end font-monospace fw-bold">
              {{ number_format($inv->total_quantity, 3) }}
            </td>

            <!-- Total Amount -->
            <td class="py-3 px-3 text-end font-monospace">
              <div class="fw-bold text-dark">₹{{ number_format($inv->rounded_amount, 2) }}</div>
              <div class="small text-muted" style="font-size: 0.72rem;">Exact: ₹{{ number_format($inv->total_amount, 2) }}</div>
            </td>

            <!-- Status -->
            <td class="py-3 px-3 text-center">
              @if($inv->status === 'Active')
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Active</span>
              @elseif($inv->status === 'Draft')
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">Draft</span>
              @else
                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">{{ $inv->status }}</span>
              @endif
            </td>

            <!-- Actions -->
            <td class="py-3 px-3 text-end">
              <div class="d-inline-flex align-items-center gap-1.5">
                <a href="{{ route('invoices.show', $inv) }}" class="btn btn-sm btn-outline-primary py-1 px-2" title="View & Print Invoice">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="{{ route('invoices.edit', $inv) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Edit Invoice Docket">
                  <i class="bi bi-pencil"></i>
                </a>
                <form action="{{ route('invoices.destroy', $inv) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete Commercial Invoice #{{ $inv->invoice_number }}?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="Delete Invoice">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="py-5 text-center text-muted">
              <div class="p-3 bg-light rounded-circle d-inline-flex mb-2">
                <i class="bi bi-receipt text-secondary fs-2"></i>
              </div>
              <div class="fw-semibold">No commercial export invoices found</div>
              <p class="small text-muted mb-3">Create your first statutory export invoice docket with full table variables.</p>
              <a href="{{ route('invoices.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Create First Invoice
              </a>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($invoices->hasPages())
    <div class="card-footer bg-white py-3 px-3 border-top">
      {{ $invoices->links() }}
    </div>
    @endif
  </div>

</main>
@endsection
