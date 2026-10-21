<?php
// Reliance Granito Export Documentation Management System (EDMS)
// Master Control Tower Dashboard
$pageTitle = "Reliance Granito | Export Documentation Management System (EDMS) - Dashboard";
$activeNav = "dashboard";
include_once __DIR__ . '/includes/header.php';
?>

<!-- Main Content Area -->
<main class="content-area" id="overviewSection">
  <!-- Page Header -->
  <div class="page-header">
    <div class="page-title-group">
      <h2>Export Documentation Control Tower</h2>
      <p>End-to-end management of Commercial Invoices, ICEGATE Shipping Bills, Multimodal BLs, and 3-Way Discrepancy Audits</p>
    </div>
    <div class="header-actions">
      <button class="btn btn-secondary btn-sm" onclick="resetDemoData()">
        <i class="bi bi-arrow-counterclockwise"></i>
        <span>Restore Demo Data</span>
      </button>
      <button class="btn btn-primary btn-sm" onclick="openShipmentDetailModal('SHP-1469')">
        <i class="bi bi-box-arrow-up-right"></i>
        <span>Open Primary Consignment (SHP-1469)</span>
      </button>
    </div>
  </div>

  <!-- System Alert Banner for Active Discrepancy in SHP-1469 -->
  <div id="activeDiscrepancyBanner" style="background:#FEF2F2; border:1px solid #FECACA; border-radius:10px; padding:14px 18px; margin-bottom:24px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
    <div style="display:flex; align-items:center; gap:12px;">
      <div style="width:36px; height:36px; border-radius:50%; background:#FEE2E2; display:flex; align-items:center; justify-content:center; color:#DC2626; font-size:1.2rem; flex-shrink:0;">
        <i class="bi bi-exclamation-triangle-fill"></i>
      </div>
      <div>
        <div style="font-size:0.9rem; font-weight:700; color:#991B1B;">Action Required: Cross-Document Discrepancy in Consignment SHP-1469</div>
        <div style="font-size:0.78rem; color:#B91C1C; margin-top:2px;">
          Buyer destination amended from Da Nang to Hai Phong between Original Invoice and Bill of Lading. Review and record confirmed manual override before finalization.
        </div>
      </div>
    </div>
    <button type="button" class="btn btn-danger btn-sm" onclick="openDiscrepancyModal('SHP-1469')">
      <i class="bi bi-shield-exclamation"></i>
      <span>Resolve Discrepancy →</span>
    </button>
  </div>

  <!-- Shipment Statistics KPIs -->
  <div class="metrics-grid">
    <div class="metric-card">
      <div class="metric-header">
        <span class="metric-title">Active Export Shipments</span>
        <div class="metric-icon-wrap">
          <i class="bi bi-box-seam"></i>
        </div>
      </div>
      <div class="metric-value" id="kpiShipmentCount">3 Consignments</div>
      <div class="metric-footer">
        <span class="metric-trend trend-up">SHP-1469 Active</span>
        <span>Granite & Ceramic Tiles</span>
      </div>
    </div>

    <div class="metric-card">
      <div class="metric-header">
        <span class="metric-title">Pending Invoices & SBs</span>
        <div class="metric-icon-wrap" style="color:#D97706; background:#FEF3C7;">
          <i class="bi bi-receipt-cutoff" style="color:#D97706;"></i>
        </div>
      </div>
      <div class="metric-value">2 Documents</div>
      <div class="metric-footer">
        <span class="metric-trend trend-down">1 Awaiting Sign-off</span>
        <span>6-Page SB Mapped</span>
      </div>
    </div>

    <div class="metric-card">
      <div class="metric-header">
        <span class="metric-title">Discrepancies Flagged</span>
        <div class="metric-icon-wrap" style="color:#DC2626; background:#FEE2E2;">
          <i class="bi bi-shield-x" style="color:#DC2626;"></i>
        </div>
      </div>
      <div class="metric-value" id="kpiMismatchCount">1 Mismatch</div>
      <div class="metric-footer">
        <span class="metric-trend trend-down" id="kpiMismatchTag">Destination Address</span>
        <span>1 Valuation Warning</span>
      </div>
    </div>

    <div class="metric-card">
      <div class="metric-header">
        <span class="metric-title">Immutable Versions Archived</span>
        <div class="metric-icon-wrap" style="color:#059669; background:#DCFCE7;">
          <i class="bi bi-lock-fill" style="color:#059669;"></i>
        </div>
      </div>
      <div class="metric-value">4 Versions Locked</div>
      <div class="metric-footer">
        <span class="metric-trend trend-up">SHA-256 Checksums</span>
        <span>Non-Destructive Overrides</span>
      </div>
    </div>
  </div>

  <!-- Main Dashboard Split: Recent Shipments & Quick Widgets -->
  <div class="row g-4" id="shipmentsSection">
    <!-- Recent Shipments Table -->
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header">
          <div class="card-title">
            <i class="bi bi-truck text-primary"></i>
            <span>Recent Export Consignments</span>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm" onclick="openCreateShipmentModal()">
              <i class="bi bi-plus-lg"></i>
              <span>New Consignment</span>
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table" id="dashboardShipmentsTable">
            <thead>
              <tr>
                <th>Shipment ID</th>
                <th>Exporter & Buyer</th>
                <th>Cargo & Quantity</th>
                <th>Container & Seal</th>
                <th>Invoice Total</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="dashboardShipmentsBody">
              <!-- Populated via JS -->
            </tbody>
          </table>
        </div>
      </div>

      <!-- 3-Way Discrepancy & Validation Summary Widget -->
      <div class="card" id="discrepancySection">
        <div class="card-header">
          <div class="card-title">
            <i class="bi bi-arrow-left-right text-primary"></i>
            <span>Cross-Document Validation Status (Invoice ↔ Shipping Bill ↔ BL)</span>
          </div>
          <button type="button" class="btn btn-primary btn-sm" onclick="openDiscrepancyModal('SHP-1469')">
            <i class="bi bi-shield-check"></i>
            <span>Audit & Override →</span>
          </button>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table" style="font-size:0.84rem;">
              <thead>
                <tr>
                  <th>Comparison Parameter</th>
                  <th>Commercial Invoice</th>
                  <th>Shipping Bill (ICEGATE)</th>
                  <th>Bill of Lading (BL)</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody id="dashboardDiscrepancySummaryBody">
                <!-- Rendered dynamically -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Documents In Queue & Recent Activities -->
    <div class="col-lg-4">
      <!-- Documents In Queue -->
      <div class="card mb-4">
        <div class="card-header">
          <div class="card-title">
            <i class="bi bi-clock text-primary"></i>
            <span>Documents In Queue</span>
          </div>
          <span class="badge badge-production">2 Ready</span>
        </div>
        <div class="card-body p-3" style="display:flex; flex-direction:column; gap:12px;">
          <div style="background:#F8FAFC; border:1px solid #E2E8F0; padding:12px; border-radius:8px;">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <strong style="color:#2563EB; font-size:0.86rem;">INV-1469 (Edited v1.1)</strong>
              <span class="badge badge-production">Active</span>
            </div>
            <div style="font-size:0.75rem; color:#64748B;">Consignee address updated for Hai Phong discharge.</div>
            <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-light-subtle">
              <span style="font-size:0.72rem; color:#94A3B8;">SHP-1469 • $16,425.27 USD</span>
              <button type="button" class="btn btn-secondary btn-sm py-0 px-2" style="font-size:0.74rem;" onclick="openDocViewer('invoice')">Inspect Docket</button>
            </div>
          </div>

          <div style="background:#F8FAFC; border:1px solid #E2E8F0; padding:12px; border-radius:8px;">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <strong style="color:#2563EB; font-size:0.86rem;">SB-9716398 (6-Page)</strong>
              <span class="badge badge-confirmed">LEO Passed</span>
            </div>
            <div style="font-size:0.75rem; color:#64748B;">Customs House Chennai. Official signed PDF attached.</div>
            <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-light-subtle">
              <span style="font-size:0.72rem; color:#94A3B8;">SHP-1469 • 226 Pkgs</span>
              <button type="button" class="btn btn-secondary btn-sm py-0 px-2" style="font-size:0.74rem;" onclick="openDocViewer('shipping-bill')">View EDI</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Audit Trail Feed -->
      <div class="card" id="auditSection">
        <div class="card-header">
          <div class="card-title">
            <i class="bi bi-journal-text text-primary"></i>
            <span>Recent Audit History</span>
          </div>
          <span class="badge badge-draft">Live Feed</span>
        </div>
        <div class="card-body p-3">
          <div id="dashboardAuditFeed" style="display:flex; flex-direction:column; gap:12px; font-size:0.78rem;">
            <!-- Populated via JS -->
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- =========================================================================
     MODAL 1: SHIPMENT DETAILS & MASTER WORKFLOW HUB
     ========================================================================= -->
<div class="modal fade" id="shipmentDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div class="d-flex align-items-center gap-3">
          <div class="brand-logo-icon" style="width:38px; height:38px; font-size:1rem;">RG</div>
          <div>
            <h5 class="modal-title mb-0" id="detailModalShipmentNumber">Consignment SHP-1469</h5>
            <div style="font-size:0.78rem; color:#64748B;" id="detailModalSubtitle">Exporter: Reliance Granito • Importer: CJ Stone Co. Ltd</div>
          </div>
          <span class="badge badge-discrepancy ms-2" id="detailModalStatusBadge">Discrepancy Flagged</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="background:#F8FAFC; padding:20px;">
        <!-- Navigation Tabs Inside Modal -->
        <ul class="nav nav-tabs mb-3" id="shipmentModalTabs" role="tablist">
          <li class="nav-item">
            <button class="nav-link active" id="tab-overview" data-bs-toggle="tab" data-bs-target="#pane-overview" type="button">Consignment Overview</button>
          </li>
          <li class="nav-item">
            <button class="nav-link" id="tab-parties" data-bs-toggle="tab" data-bs-target="#pane-parties" type="button">Buyer & Destination</button>
          </li>
          <li class="nav-item">
            <button class="nav-link" id="tab-containers" data-bs-toggle="tab" data-bs-target="#pane-containers" type="button">Cargo & Container</button>
          </li>
          <li class="nav-item">
            <button class="nav-link" id="tab-docket" data-bs-toggle="tab" data-bs-target="#pane-docket" type="button">Invoice Docket Representation</button>
          </li>
        </ul>

        <div class="tab-content">
          <!-- Pane 1: Overview -->
          <div class="tab-pane fade show active" id="pane-overview">
            <div class="row g-3">
              <div class="col-md-4">
                <div class="card p-3 mb-0">
                  <span class="text-muted" style="font-size:0.75rem;">COMMERCIAL INVOICE NO</span>
                  <h5 class="mt-1 mb-0" style="color:#0F172A;">INV-1469</h5>
                  <div style="font-size:0.75rem; color:#64748B; margin-top:4px;">Date: 2026-02-14 • Incoterms: CIF Hai Phong</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card p-3 mb-0">
                  <span class="text-muted" style="font-size:0.75rem;">CUSTOMS SHIPPING BILL</span>
                  <h5 class="mt-1 mb-0" style="color:#0F172A;">SB-9716398</h5>
                  <div style="font-size:0.75rem; color:#64748B; margin-top:4px;">Port: INMAA1 (Chennai Sea) • 6-Page EDI</div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card p-3 mb-0">
                  <span class="text-muted" style="font-size:0.75rem;">BILL OF LADING (BL)</span>
                  <h5 class="mt-1 mb-0" style="color:#0F172A;">BL-MAEU248911</h5>
                  <div style="font-size:0.75rem; color:#64748B; margin-top:4px;">Carrier: Maersk Line • Vessel: MV Northern Guard</div>
                </div>
              </div>
            </div>

            <div class="card mt-3 mb-0 p-3">
              <h6 style="color:#0F172A; font-size:0.9rem; margin-bottom:12px;">Active Consignment Parameters</h6>
              <div class="row g-3" style="font-size:0.84rem;">
                <div class="col-sm-3">
                  <span class="text-muted d-block">Export Product</span>
                  <strong>Indian Polished Granite Slabs</strong>
                </div>
                <div class="col-sm-3">
                  <span class="text-muted d-block">HSN Classification</span>
                  <strong>68022310 (Granite)</strong>
                </div>
                <div class="col-sm-3">
                  <span class="text-muted d-block">Total Quantity</span>
                  <strong>518.69 SQM (226 Packages)</strong>
                </div>
                <div class="col-sm-3">
                  <span class="text-muted d-block">Gross / Net Weight</span>
                  <strong>28,450 / 28,100 KG</strong>
                </div>
                <div class="col-sm-3">
                  <span class="text-muted d-block">Container No</span>
                  <strong style="font-family:monospace;">TCLU7789571 (20' Heavy)</strong>
                </div>
                <div class="col-sm-3">
                  <span class="text-muted d-block">Customs RFID Seal</span>
                  <strong style="font-family:monospace;">14697 / INMAA1</strong>
                </div>
                <div class="col-sm-3">
                  <span class="text-muted d-block">FOB Cargo Value</span>
                  <strong>$15,425.27 USD</strong>
                </div>
                <div class="col-sm-3">
                  <span class="text-muted d-block">Total CIF Value</span>
                  <strong style="color:#059669;">$16,425.27 USD</strong>
                </div>
              </div>
            </div>
          </div>

          <!-- Pane 2: Parties -->
          <div class="tab-pane fade" id="pane-parties">
            <div class="row g-3">
              <div class="col-md-6">
                <div class="card p-3 mb-0">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0" style="color:#0F172A;">Exporter (Manufacturer)</h6>
                    <span class="badge badge-confirmed">Indian Exporter</span>
                  </div>
                  <strong>Reliance Granito</strong>
                  <div style="font-size:0.82rem; color:#64748B; margin-top:4px;">
                    Sector 23, Chopasni Housing Board, Opposite Ashok Udhyan, Jodhpur, Rajasthan - 342008, India.<br>
                    Factory: Morbi Ceramic Complex, Gujarat.<br>
                    IEC: <strong>ABEFR3842G</strong> &bull; GSTIN: <strong>08AABCR1234F1Z8</strong>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="card p-3 mb-0">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0" style="color:#0F172A;">Consignee / Importer</h6>
                    <span class="badge badge-production">Address Amended</span>
                  </div>
                  <strong>CJ Stone Company Limited</strong>
                  <div style="font-size:0.82rem; margin-top:6px;">
                    <div style="background:#FEF2F2; border-left:3px solid #EF4444; padding:6px 10px; border-radius:4px; margin-bottom:6px;">
                      <span style="font-size:0.72rem; color:#B91C1C; font-weight:700;">ORIGINAL INVOICE (v1.0):</span>
                      <div style="color:#0F172A;">No 10, Nghe Tinh, Da Nang, Vietnam</div>
                    </div>
                    <div style="background:#F0FDF4; border-left:3px solid #10B981; padding:6px 10px; border-radius:4px;">
                      <span style="font-size:0.72rem; color:#15803D; font-weight:700;">AMENDED INVOICE (v1.1) & BILL OF LADING:</span>
                      <div style="color:#0F172A;">Dong Hai Ward, Hai Phong City, Vietnam</div>
                    </div>
                    <div style="color:#64748B; margin-top:6px; font-size:0.78rem;">
                      Tax ID: 040124712551 &bull; Contact: imp.cjstone@gmail.com
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Pane 3: Cargo & Container -->
          <div class="tab-pane fade" id="pane-containers">
            <div class="card p-3 mb-0">
              <h6 style="color:#0F172A; font-size:0.9rem; margin-bottom:12px;">Equipment & RFID Electronic Seals</h6>
              <div class="table-responsive">
                <table class="table" style="font-size:0.84rem;">
                  <thead>
                    <tr>
                      <th>Container No</th>
                      <th>Type / Size</th>
                      <th>Customs Seal #</th>
                      <th>Packages Loaded</th>
                      <th>Gross Weight</th>
                      <th>Tare Weight</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td style="font-family:monospace; font-weight:700;">TCLU7789571</td>
                      <td>20' Heavy Duty Box</td>
                      <td style="font-family:monospace; color:#2563EB;">14697</td>
                      <td>226 Wooden Crates</td>
                      <td>28,450 KG</td>
                      <td>2,200 KG</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Pane 4: Docket Representation -->
          <div class="tab-pane fade" id="pane-docket">
            <div style="background:#F1F5F9; border:1px solid #E2E8F0; border-radius:8px; padding:20px;">
              <div class="printable-document">
                <div class="doc-header">
                  <div>
                    <div class="doc-company-name">RELIANCE GRANITO</div>
                    <div class="doc-company-sub">
                      Manufacturers & Exporters of Granite Slabs & Premium Vitrified Tiles<br>
                      Jodhpur Office & Morbi Ceramic Facility &bull; IEC: ABEFR3842G
                    </div>
                  </div>
                  <div class="doc-badge-title">
                    <h3>COMMERCIAL INVOICE</h3>
                    <div style="font-size:0.8rem; color:#475569;">Invoice No: <strong>1469</strong> &bull; Date: <strong>14-FEB-2026</strong></div>
                  </div>
                </div>

                <div class="doc-info-grid">
                  <div class="doc-info-block">
                    <h4>Buyer / Consignee:</h4>
                    <strong>CJ STONE COMPANY LIMITED</strong><br>
                    Dong Hai Ward, Hai Phong City, Vietnam<br>
                    Tax Code: 040124712551
                  </div>
                  <div class="doc-info-block">
                    <h4>Shipment Transport:</h4>
                    Port of Loading: <strong>Chennai Sea (INMAA1)</strong><br>
                    Port of Discharge: <strong>Hai Phong, Vietnam</strong><br>
                    Container: <strong>TCLU7789571</strong> / Seal: <strong>14697</strong>
                  </div>
                </div>

                <table class="doc-table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Description of Goods</th>
                      <th>HSN Code</th>
                      <th>Quantity (SQM)</th>
                      <th>Unit Rate (USD)</th>
                      <th>Amount (USD)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>1</td>
                      <td><strong>Indian Polished Granite Slabs</strong> (Tan Brown & Galaxy)</td>
                      <td>68022310</td>
                      <td>518.69</td>
                      <td>$29.74</td>
                      <td>$15,425.27</td>
                    </tr>
                  </tbody>
                </table>

                <div class="doc-totals">
                  <div class="doc-totals-box">
                    <div class="doc-total-row"><span>FOB Cargo Value:</span><span>$15,425.27</span></div>
                    <div class="doc-total-row"><span>Ocean Freight (USD):</span><span>$900.00</span></div>
                    <div class="doc-total-row"><span>Transit Marine Insurance:</span><span>$100.00</span></div>
                    <div class="doc-total-row grand"><span>TOTAL CIF INVOICE VALUE:</span><span>$16,425.27 USD</span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-danger btn-sm" onclick="openDiscrepancyModal('SHP-1469')">
          <i class="bi bi-shield-exclamation"></i>
          <span>Review & Resolve Discrepancies</span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- =========================================================================
     MODAL 2: 3-WAY DISCREPANCY & MANUAL OVERRIDE RESOLVER
     ========================================================================= -->
<div class="modal fade" id="discrepancyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-shield-exclamation text-danger" style="font-size:1.3rem;"></i>
          <h5 class="modal-title mb-0">Cross-Document Discrepancy Governance (SHP-1469)</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="background:#F8FAFC; padding:22px;">
        <div class="alert alert-danger d-flex align-items-start gap-2 mb-3" style="font-size:0.84rem; background:#FEF2F2; border-color:#FECACA; color:#991B1B;">
          <i class="bi bi-exclamation-triangle-fill fs-5 mt-1 flex-shrink-0"></i>
          <div>
            <strong>Consignee Destination Mismatch Flagged:</strong><br>
            Commercial Invoice v1.0 declared destination as <strong>Da Nang, Vietnam</strong>, whereas Shipping Bill and Bill of Lading BL-MAEU248911 declare <strong>Hai Phong City, Vietnam</strong>.
          </div>
        </div>

        <div class="card p-3 mb-3">
          <h6 style="font-size:0.88rem; color:#0F172A; margin-bottom:8px;">Authorized Non-Destructive Override</h6>
          <p style="font-size:0.78rem; color:#64748B; margin-bottom:12px;">
            Per export audit protocol, original source PDF figures and ICEGATE records remain immutable. Authorizing this override links Commercial Invoice v1.1 to the Bill of Lading with full cryptographic audit stamping.
          </p>

          <div class="mb-3">
            <label class="form-label" style="font-size:0.82rem; font-weight:600;">Resolution Justification & Buyer PO Amendment Reference</label>
            <textarea class="form-control" id="overrideReason" rows="3" placeholder="e.g., Buyer CJ Stone Co. requested discharge change to Hai Phong per email authorization dated 2026-02-14...">Buyer CJ Stone requested port delivery diversion to Hai Phong City. Commercial Invoice updated to v1.1 and confirmed with Maersk Line BL.</textarea>
          </div>

          <div class="row g-2">
            <div class="col-sm-6">
              <label class="form-label" style="font-size:0.82rem; font-weight:600;">Authorized By</label>
              <input type="text" class="form-control" id="overrideAuthorizer" value="Rajesh B. Patel (Export Manager)" readonly>
            </div>
            <div class="col-sm-6">
              <label class="form-label" style="font-size:0.82rem; font-weight:600;">Override Action</label>
              <select class="form-select" id="overrideActionType">
                <option value="Approved With Override">Accept Amended Address (Hai Phong)</option>
                <option value="Hold For Correction">Hold Consignment for Customs Re-filing</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success btn-sm" onclick="saveDiscrepancyResolution()">
          <i class="bi bi-check2-circle"></i>
          <span>Confirm Override & Sign-off</span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- =========================================================================
     MODAL 3: CREATE NEW SHIPMENT WIZARD
     ========================================================================= -->
<div class="modal fade" id="createShipmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-box-seam text-primary" style="font-size:1.3rem;"></i>
          <h5 class="modal-title mb-0">Create New Export Consignment</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form onsubmit="handleCreateShipment(event)">
        <div class="modal-body" style="background:#F8FAFC; padding:22px;">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Shipment Reference Number</label>
              <input type="text" class="form-control" id="newShipmentId" value="SHP-1472" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Buyer / Consignee</label>
              <select class="form-select" id="newBuyerId" required>
                <option value="PRT-002">CJ Stone Company Limited (Vietnam)</option>
                <option value="PRT-003">Baumarkt Import GmbH (Germany)</option>
                <option value="PRT-004">Al-Futtaim Granite Trading (UAE)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Port of Loading (POL)</label>
              <select class="form-select" id="newPol">
                <option value="Chennai Sea (INMAA1)">Chennai Sea (INMAA1)</option>
                <option value="Mundra Port (INMUN1)">Mundra Port (INMUN1)</option>
                <option value="Nhava Sheva (INNSA1)">Nhava Sheva (INNSA1)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Port of Discharge (POD)</label>
              <input type="text" class="form-control" id="newPod" value="Hai Phong, Vietnam" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Quantity (SQM)</label>
              <input type="number" class="form-control" id="newQty" value="480.00" step="0.01" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Packages / Crates</label>
              <input type="number" class="form-control" id="newPackages" value="210" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Total Value (USD)</label>
              <input type="number" class="form-control" id="newValue" value="14850" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-check-lg"></i>
            <span>Initialize Consignment</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- =========================================================================
     MODAL 4: DOCUMENT VIEWER
     ========================================================================= -->
<div class="modal fade" id="docViewerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="docViewerTitle">Document Representation</h5>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-primary btn-sm" onclick="printDocViewer()">
            <i class="bi bi-printer"></i> Print / PDF
          </button>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>
      <div class="modal-body" style="background:#F1F5F9; padding:24px;">
        <div id="docViewerContent" class="printable-document">
          <!-- Dynamically populated -->
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    renderDashboard();
  });

  function renderDashboard() {
    const data = getExportData();
    renderShipmentsTable(data);
    renderDiscrepancySummary(data);
    renderAuditFeed(data);
  }

  function renderShipmentsTable(data) {
    const tbody = document.getElementById("dashboardShipmentsBody");
    tbody.innerHTML = "";

    data.shipments.forEach(s => {
      const buyer = data.parties.find(p => p.id === s.buyerPartyId) || { name: "CJ Stone Co. Ltd", country: "Vietnam" };
      let statusBadge = "badge-draft";
      if (s.status === "Approved") statusBadge = "badge-approved";
      if (s.status === "Discrepancy Flagged") statusBadge = "badge-discrepancy";
      if (s.status === "In-Review") statusBadge = "badge-review";
      if (s.status === "Finalized") statusBadge = "badge-finalized";

      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>
          <a href="javascript:void(0)" onclick="openShipmentDetailModal('${s.shipmentNumber}')" style="font-weight:700; color:#2563EB;">
            ${s.shipmentNumber}
          </a>
          <div style="font-size:0.72rem; color:var(--text-dim);">${s.createdAt}</div>
        </td>
        <td>
          <div style="font-weight:600; color:#0F172A;">${buyer.name}</div>
          <div style="font-size:0.75rem; color:#D97706;">${s.podName} (${s.incoterms})</div>
        </td>
        <td>
          <div>${s.totalQuantitySqm.toLocaleString()} SQM</div>
          <div style="font-size:0.72rem; color:var(--text-dim);">${s.totalPackages} ${s.packageType} (${(s.grossWeightKg/1000).toFixed(1)} MT)</div>
        </td>
        <td>
          <div style="font-family:monospace; color:#0F172A;">${s.containers[0]?.containerNumber || 'TCLU7789571'}</div>
          <div style="font-size:0.72rem; color:var(--text-dim);">Seal: ${s.containers[0]?.sealNumber || '14697'}</div>
        </td>
        <td>
          <div style="font-weight:700; color:#059669;">$${s.totalInvoiceValue.toLocaleString()} USD</div>
        </td>
        <td>
          <span class="badge ${statusBadge}">${s.status}</span>
        </td>
        <td>
          <button type="button" class="btn btn-secondary btn-sm py-1 px-2" style="font-size:0.75rem;" onclick="openShipmentDetailModal('${s.shipmentNumber}')">
            Manage →
          </button>
        </td>
      `;
      tbody.appendChild(tr);
    });

    // Update KPI
    const kpiShipments = document.getElementById("kpiShipmentCount");
    if (kpiShipments) kpiShipments.textContent = `${data.shipments.length} Consignments`;
  }

  function renderDiscrepancySummary(data) {
    const tbody = document.getElementById("dashboardDiscrepancySummaryBody");
    tbody.innerHTML = "";

    const results = evaluateCrossDocumentValidation("SHP-1469");
    results.slice(0, 4).forEach(r => {
      let badge = '<span class="badge badge-confirmed">MATCH</span>';
      if (r.matchStatus === "MISMATCH") badge = '<span class="badge badge-hold">MISMATCH</span>';
      if (r.matchStatus === "WARNING") badge = '<span class="badge badge-production">WARNING</span>';

      const tr = document.createElement("tr");
      tr.className = r.matchStatus === "MISMATCH" ? "discrepancy-row-mismatch" : "";
      tr.innerHTML = `
        <td><strong>${r.fieldName}</strong></td>
        <td style="color:#0F172A;">${r.invoiceVal}</td>
        <td style="color:#0F172A;">${r.sbVal}</td>
        <td style="color:#0F172A;">${r.blVal}</td>
        <td>${badge}</td>
      `;
      tbody.appendChild(tr);
    });
  }

  function renderAuditFeed(data) {
    const container = document.getElementById("dashboardAuditFeed");
    container.innerHTML = "";

    data.auditLogs.slice(0, 4).forEach(log => {
      const item = document.createElement("div");
      item.style.borderLeft = "3px solid #2563EB";
      item.style.paddingLeft = "10px";
      item.style.background = "#F8FAFC";
      item.style.padding = "8px 10px";
      item.style.borderRadius = "4px";
      item.innerHTML = `
        <div class="d-flex justify-content-between">
          <strong style="color:#0F172A;">${log.action}</strong>
          <span style="color:#94A3B8; font-size:0.7rem;">${log.timestamp.split(' ')[1] || ''}</span>
        </div>
        <div style="color:#64748B; margin-top:2px;">${log.newValue}</div>
        <div style="color:#94A3B8; font-size:0.7rem; margin-top:2px;">By ${log.user} • ${log.shipmentNumber}</div>
      `;
      container.appendChild(item);
    });
  }

  function handleGlobalSearch(q) {
    q = q.toLowerCase();
    const data = getExportData();
    const filtered = data.shipments.filter(s => 
      s.shipmentNumber.toLowerCase().includes(q) ||
      s.podName.toLowerCase().includes(q) ||
      (s.containers[0]?.containerNumber || "").toLowerCase().includes(q)
    );
    renderShipmentsTable({ shipments: filtered, parties: data.parties });
  }

  // Modal Helpers
  function openShipmentDetailModal(shipmentId) {
    const modal = new bootstrap.Modal(document.getElementById("shipmentDetailModal"));
    modal.show();
  }

  function openDiscrepancyModal(shipmentId) {
    const modal = new bootstrap.Modal(document.getElementById("discrepancyModal"));
    modal.show();
  }

  function openCreateShipmentModal() {
    const modal = new bootstrap.Modal(document.getElementById("createShipmentModal"));
    modal.show();
  }

  function handleCreateShipment(e) {
    e.preventDefault();
    const id = document.getElementById("newShipmentId").value;
    const buyerId = document.getElementById("newBuyerId").value;
    const pol = document.getElementById("newPol").value;
    const pod = document.getElementById("newPod").value;
    const qty = parseFloat(document.getElementById("newQty").value);
    const packages = parseInt(document.getElementById("newPackages").value);
    const val = parseFloat(document.getElementById("newValue").value);

    const data = getExportData();
    data.shipments.unshift({
      id: "SHP-" + Date.now(),
      shipmentNumber: id,
      buyerPartyId: buyerId,
      sellerPartyId: "PRT-001",
      createdAt: "2026-02-16",
      status: "In-Review",
      incoterms: "CIF",
      currency: "USD",
      polName: pol,
      podName: pod,
      containers: [{ containerNumber: "MSKU" + Math.floor(1000000 + Math.random()*9000000), sealNumber: "2841" + Math.floor(10 + Math.random()*90) }],
      totalPackages: packages,
      packageType: "Wooden Crates",
      totalQuantitySqm: qty,
      grossWeightKg: Math.round(qty * 54.8),
      netWeightKg: Math.round(qty * 54.1),
      totalInvoiceValue: val
    });

    logAuditEvent(id, "Shipment Initialized", "Consignment created via Quick Tower Wizard", "Draft");
    saveExportData(data);
    renderDashboard();

    const modalEl = document.getElementById("createShipmentModal");
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();

    showToast(`Consignment ${id} created successfully!`, "success");
  }

  function saveDiscrepancyResolution() {
    const reason = document.getElementById("overrideReason").value;
    const data = getExportData();

    // Update primary shipment status
    const shp = data.shipments.find(s => s.shipmentNumber === "SHP-1469");
    if (shp) {
      shp.status = "Approved";
    }

    logAuditEvent("SHP-1469", "Discrepancy Resolved", "Consignee address override confirmed: " + reason, "Approved");
    saveExportData(data);

    // Hide banner and update UI
    const banner = document.getElementById("activeDiscrepancyBanner");
    if (banner) banner.style.display = "none";

    const badge = document.getElementById("sidebarMismatchBadge");
    if (badge) {
      badge.className = "nav-badge";
      badge.textContent = "0 Mismatch";
    }

    const topBadge = document.getElementById("topNavMismatchBadge");
    if (topBadge) {
      topBadge.className = "badge bg-success ms-1";
      topBadge.textContent = "0 Mismatch";
    }

    const kpiMismatch = document.getElementById("kpiMismatchCount");
    if (kpiMismatch) kpiMismatch.textContent = "0 Mismatches";

    const kpiTag = document.getElementById("kpiMismatchTag");
    if (kpiTag) {
      kpiTag.className = "metric-trend trend-up";
      kpiTag.textContent = "Resolved & Signed";
    }

    const modalEl = document.getElementById("discrepancyModal");
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();

    renderDashboard();
    showToast("Discrepancy resolved and signed off! Consignment approved.", "success");
  }

  function openDocViewer(type) {
    const title = document.getElementById("docViewerTitle");
    const content = document.getElementById("docViewerContent");

    if (type === "invoice") {
      title.textContent = "Commercial Invoice INV-1469 (Amended v1.1)";
      content.innerHTML = `
        <div class="doc-header">
          <div>
            <div class="doc-company-name">RELIANCE GRANITO</div>
            <div class="doc-company-sub">
              Sector 23, Chopasni Housing Board, Jodhpur, Rajasthan - 342008 &bull; Morbi Facility<br>
              IEC: ABEFR3842G &bull; GSTIN: 08AABCR1234F1Z8
            </div>
          </div>
          <div class="doc-badge-title">
            <h3>COMMERCIAL INVOICE</h3>
            <div style="font-size:0.8rem; color:#475569;">Invoice No: <strong>1469 (v1.1)</strong> &bull; Date: <strong>14-FEB-2026</strong></div>
          </div>
        </div>
        <div class="doc-info-grid">
          <div class="doc-info-block">
            <h4>Buyer / Consignee:</h4>
            <strong>CJ STONE COMPANY LIMITED</strong><br>
            Dong Hai Ward, Hai Phong City, Vietnam<br>
            Tax Code: 040124712551
          </div>
          <div class="doc-info-block">
            <h4>Port & Vessel:</h4>
            Port of Loading: <strong>Chennai Sea (INMAA1)</strong><br>
            Port of Discharge: <strong>Hai Phong, Vietnam</strong><br>
            Terms: <strong>CIF Hai Phong</strong>
          </div>
        </div>
        <table class="doc-table">
          <thead>
            <tr><th>Item</th><th>Description</th><th>HSN Code</th><th>Quantity</th><th>Unit Price</th><th>Total USD</th></tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td><strong>Indian Polished Granite Slabs</strong> (Mirror Polished, 20mm)</td>
              <td>68022310</td>
              <td>518.69 SQM</td>
              <td>$29.74 USD</td>
              <td>$15,425.27 USD</td>
            </tr>
          </tbody>
        </table>
        <div class="doc-totals">
          <div class="doc-totals-box">
            <div class="doc-total-row"><span>FOB Value:</span><span>$15,425.27 USD</span></div>
            <div class="doc-total-row"><span>Freight Charges:</span><span>$900.00 USD</span></div>
            <div class="doc-total-row"><span>Marine Insurance:</span><span>$100.00 USD</span></div>
            <div class="doc-total-row grand"><span>TOTAL CIF INVOICE VALUE:</span><span>$16,425.27 USD</span></div>
          </div>
        </div>
      `;
    } else if (type === "shipping-bill") {
      title.textContent = "Customs Shipping Bill SB-9716398 (ICEGATE 6-Page EDI Summary)";
      content.innerHTML = `
        <div class="doc-header">
          <div>
            <div class="doc-company-name">INDIAN CUSTOMS EDI SYSTEM - ICEGATE</div>
            <div class="doc-company-sub">Customs House, Chennai (INMAA1) &bull; Export Commissionerate</div>
          </div>
          <div class="doc-badge-title">
            <h3>SHIPPING BILL DOCKET</h3>
            <div style="font-size:0.8rem; color:#475569;">SB No: <strong>9716398</strong> &bull; Date: <strong>14-FEB-2026</strong></div>
          </div>
        </div>
        <div class="sb-grid mb-3">
          <div class="sb-cell"><div class="sb-cell-label">Exporter Name</div><div class="sb-cell-val">RELIANCE GRANITO</div></div>
          <div class="sb-cell"><div class="sb-cell-label">IEC Number</div><div class="sb-cell-val">ABEFR3842G</div></div>
          <div class="sb-cell"><div class="sb-cell-label">Consignee</div><div class="sb-cell-val">CJ STONE COMPANY LIMITED</div></div>
          <div class="sb-cell"><div class="sb-cell-label">Port of Discharge</div><div class="sb-cell-val">HAI PHONG (VNHPH)</div></div>
          <div class="sb-cell"><div class="sb-cell-label">FOB Value (INR)</div><div class="sb-cell-val">₹ 13,39,685.00</div></div>
          <div class="sb-cell"><div class="sb-cell-label">Total Packages</div><div class="sb-cell-val">226 Wooden Crates</div></div>
          <div class="sb-cell"><div class="sb-cell-label">Gross / Net Weight</div><div class="sb-cell-val">28,450 / 28,100 KG</div></div>
          <div class="sb-cell"><div class="sb-cell-label">LEO Status</div><div class="sb-cell-val" style="color:#059669;">LEO GRANTED (PASS)</div></div>
        </div>
      `;
    } else {
      title.textContent = "Multimodal Bill of Lading BL-MAEU248911 & HIDE Mode Masking";
      content.innerHTML = `
        <div class="doc-header">
          <div>
            <div class="doc-company-name">MAERSK LINE &bull; OCEAN BILL OF LADING</div>
            <div class="doc-company-sub">Multimodal Transport Document &bull; Vessel: MV Northern Guard v.2602</div>
          </div>
          <div class="doc-badge-title">
            <h3>ORIGINAL BILL OF LADING</h3>
            <div style="font-size:0.8rem; color:#475569;">B/L No: <strong>BL-MAEU248911</strong></div>
          </div>
        </div>
        <div class="doc-info-grid">
          <div class="doc-info-block">
            <h4>Shipper:</h4>
            <strong>RELIANCE GRANITO</strong><br>
            Jodhpur & Morbi Facility, India
          </div>
          <div class="doc-info-block">
            <h4>Consignee:</h4>
            <strong>CJ STONE COMPANY LIMITED</strong><br>
            Dong Hai Ward, Hai Phong City, Vietnam
          </div>
        </div>
        <div class="alert alert-warning mb-3" style="font-size:0.8rem; background:#FEF3C7; color:#92400E; border:1px solid #FDE68A;">
          <i class="bi bi-shield-lock-fill"></i> <strong>HIDE Mode Masking Enabled:</strong> In commercial third-party copies, sensitive freight charges and supplier margins are hidden per business workflow rules.
        </div>
        <table class="doc-table">
          <thead>
            <tr><th>Container / Seal</th><th>Packages</th><th>Description of Goods</th><th>Gross Weight</th></tr>
          </thead>
          <tbody>
            <tr>
              <td><strong>TCLU7789571</strong><br>Seal: 14697</td>
              <td>226 Crates</td>
              <td>Indian Polished Granite Slabs (Mirror Polished, 20mm)</td>
              <td>28,450.00 KG</td>
            </tr>
          </tbody>
        </table>
      `;
    }

    const modal = new bootstrap.Modal(document.getElementById("docViewerModal"));
    modal.show();
  }

  function printDocViewer() {
    window.print();
  }
</script>

<?php
include_once __DIR__ . '/includes/footer.php';
?>
