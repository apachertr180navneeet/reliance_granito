/**
 * Reliance Granito EDMS - Consigners (Consignor / Exporter) Master CRUD Controller
 * Manages full lifecycle (Create, Read, Update, Delete) of export consignor entities.
 * Includes localStorage persistence, search, filtering, CSV/JSON export, and PDF editor links.
 */

const CONSIGNERS_STORAGE_KEY = "reliance_edms_consigners";

// Default Master Seed Data based on Reliance Granito Export Workflow & Morbi Ceramic Cluster
const DEFAULT_CONSIGNERS_SEED = [
  {
    id: "EXP-001",
    name: "Reliance Granito Private Limited",
    entityType: "Manufacturer Exporter",
    iec: "ABEFR3842G",
    iecBranch: "0",
    gstin: "08ABEFR3842G1Z5",
    pan: "ABEFR3842G",
    adCode: "6390013",
    forexBank: "ICICI Bank Limited, Jodhpur Branch (A/C: ICIC0006827)",
    registeredAddress: "Sector 23, 23-A-6, Chopasni Housing Board, Opposite Ashok Udhyan Gate No. 2, Jodhpur, Rajasthan - 342008, India",
    factoryAddress: "National Highway 8-A, Old Ghuntu Road, Morbi - 363642, Gujarat, India",
    region: "Morbi",
    contactPerson: "Rajesh B. Patel",
    contactPhone: "+91 2822 240890",
    contactEmail: "export@reliancegranito.com",
    status: "Active",
    createdAt: "2026-01-10T09:00:00Z"
  },
  {
    id: "EXP-002",
    name: "Reliance Granito (Morbi Plant II)",
    entityType: "Manufacturing Facility / Plant",
    iec: "ABEFR3842G",
    iecBranch: "1",
    gstin: "24ABEFR3842G1Z2",
    pan: "ABEFR3842G",
    adCode: "6390013",
    forexBank: "State Bank of India, Morbi Main Commercial Branch (A/C: 30894102941)",
    registeredAddress: "Sector 23, Chopasni Housing Board, Jodhpur, Rajasthan - 342008, India",
    factoryAddress: "Survey No. 142/P, Lakhdhirpur Road, 8-A National Highway, Morbi - 363642, Gujarat, India",
    region: "Morbi",
    contactPerson: "Haresh V. Patel",
    contactPhone: "+91 98252 44101",
    contactEmail: "morbi.plant2@reliancegranito.com",
    status: "Active",
    createdAt: "2026-01-15T10:30:00Z"
  },
  {
    id: "EXP-003",
    name: "Reliance Ceramic Industries LLP",
    entityType: "Sister Firm / Associate",
    iec: "AABFR9182K",
    iecBranch: "0",
    gstin: "24AABFR9182K1Z9",
    pan: "AABFR9182K",
    adCode: "6391104",
    forexBank: "HDFC Bank Limited, Morbi Branch (A/C: 502000341098)",
    registeredAddress: "GIDC Industrial Estate, Phase II, Morbi - 363641, Gujarat, India",
    factoryAddress: "GIDC Industrial Estate, Phase II, Morbi - 363641, Gujarat, India",
    region: "Morbi",
    contactPerson: "Bhavin R. Patel",
    contactPhone: "+91 2822 251140",
    contactEmail: "bhavin@relianceceramics.in",
    status: "Active",
    createdAt: "2026-02-01T14:15:00Z"
  },
  {
    id: "EXP-004",
    name: "Marwar Granite & Quartz Export LLP",
    entityType: "Manufacturer Exporter",
    iec: "AARFM7721H",
    iecBranch: "0",
    gstin: "08AARFM7721H1Z1",
    pan: "AARFM7721H",
    adCode: "6389920",
    forexBank: "Axis Bank Limited, Mandore Road Branch, Jodhpur (A/C: 919020045582)",
    registeredAddress: "Plot No. 45-B, Mandore Industrial Area, Jodhpur - 342007, Rajasthan, India",
    factoryAddress: "RIICO Industrial Area, Phase II, Boranada, Jodhpur - 342012, Rajasthan, India",
    region: "Jodhpur",
    contactPerson: "Kishore Singh Rathore",
    contactPhone: "+91 291 2740911",
    contactEmail: "ks.rathore@marwargranite.com",
    status: "Active",
    createdAt: "2026-02-05T11:45:00Z"
  }
];

// App State
let consignersList = [];
let currentViewMode = "table"; // 'table' or 'grid'
let pendingDeleteId = null;
let activeViewConsigner = null;

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  loadConsigners();
  renderConsigners();
  updateConsignersStats();
});

/**
 * Load consigners from localStorage or fallback to default seeds
 */
function loadConsigners() {
  try {
    const raw = localStorage.getItem(CONSIGNERS_STORAGE_KEY);
    if (raw) {
      consignersList = JSON.parse(raw);
    } else {
      consignersList = JSON.parse(JSON.stringify(DEFAULT_CONSIGNERS_SEED));
      saveConsignersToStorage();
    }
  } catch (e) {
    console.error("Error loading consigners from storage:", e);
    consignersList = JSON.parse(JSON.stringify(DEFAULT_CONSIGNERS_SEED));
  }
}

/**
 * Save current consigners list to localStorage
 */
function saveConsignersToStorage() {
  try {
    localStorage.setItem(CONSIGNERS_STORAGE_KEY, JSON.stringify(consignersList));
  } catch (e) {
    console.error("Error saving consigners to storage:", e);
  }
}

/**
 * Update Key Performance Indicators / Statistics
 */
function updateConsignersStats() {
  const total = consignersList.length;
  const active = consignersList.filter(c => c.status === "Active").length;
  const morbi = consignersList.filter(c => (c.region === "Morbi" || (c.factoryAddress && c.factoryAddress.toLowerCase().includes("morbi")))).length;
  const jodhpur = consignersList.filter(c => (c.region === "Jodhpur" || (c.registeredAddress && c.registeredAddress.toLowerCase().includes("jodhpur")))).length;

  const elTotal = document.getElementById("statTotalConsigners");
  const elActive = document.getElementById("statActiveConsigners");
  const elMorbi = document.getElementById("statMorbiPlants");
  const elJodhpur = document.getElementById("statJodhpurUnits");
  const elBadge = document.getElementById("consignerTotalBadge");

  if (elTotal) elTotal.textContent = total;
  if (elActive) elActive.textContent = active;
  if (elMorbi) elMorbi.textContent = morbi;
  if (elJodhpur) elJodhpur.textContent = jodhpur;
  if (elBadge) elBadge.textContent = `${total} Consigners Registered`;
}

/**
 * Toggle between Table and Grid View
 */
function setViewMode(mode) {
  currentViewMode = mode;
  const tableBtn = document.getElementById("viewModeTableBtn");
  const gridBtn = document.getElementById("viewModeGridBtn");
  const tableWrapper = document.getElementById("consignersTableWrapper");
  const gridWrapper = document.getElementById("consignersGridWrapper");

  if (mode === "table") {
    tableBtn.classList.add("active");
    gridBtn.classList.remove("active");
    tableWrapper.classList.remove("d-none");
    gridWrapper.classList.add("d-none");
  } else {
    gridBtn.classList.add("active");
    tableBtn.classList.remove("active");
    gridWrapper.classList.remove("d-none");
    tableWrapper.classList.add("d-none");
  }
  renderConsigners();
}

/**
 * Filter consigners based on search text, status, and region
 */
function getFilteredConsigners() {
  const searchInput = document.getElementById("searchConsignerInput");
  const query = searchInput ? searchInput.value.trim().toLowerCase() : "";
  const statusSelect = document.getElementById("filterStatusSelect");
  const statusFilter = statusSelect ? statusSelect.value : "ALL";
  const regionSelect = document.getElementById("filterRegionSelect");
  const regionFilter = regionSelect ? regionSelect.value : "ALL";

  return consignersList.filter(item => {
    // Status filter
    if (statusFilter !== "ALL" && item.status !== statusFilter) {
      return false;
    }

    // Region filter
    if (regionFilter !== "ALL") {
      const matchMorbi = (item.region === "Morbi" || (item.factoryAddress && item.factoryAddress.toLowerCase().includes("morbi")));
      const matchJodhpur = (item.region === "Jodhpur" || (item.registeredAddress && item.registeredAddress.toLowerCase().includes("jodhpur")));
      if (regionFilter === "Morbi" && !matchMorbi) return false;
      if (regionFilter === "Jodhpur" && !matchJodhpur) return false;
    }

    // Search query
    if (query) {
      const haystack = [
        item.name,
        item.iec,
        item.gstin,
        item.pan,
        item.adCode,
        item.contactPerson,
        item.contactEmail,
        item.registeredAddress,
        item.factoryAddress
      ].filter(Boolean).join(" ").toLowerCase();

      if (!haystack.includes(query)) return false;
    }

    return true;
  });
}

/**
 * Re-render list based on current filters
 */
function handleConsignerSearch() {
  renderConsigners();
}

function clearConsignerSearch() {
  const input = document.getElementById("searchConsignerInput");
  if (input) input.value = "";
  renderConsigners();
}

/**
 * Render Consigners in either Table or Grid View
 */
function renderConsigners() {
  const filtered = getFilteredConsigners();
  const tableBody = document.getElementById("consignersTableBody");
  const gridWrapper = document.getElementById("consignersGridWrapper");
  const emptyCard = document.getElementById("emptyStateCard");
  const tableWrapper = document.getElementById("consignersTableWrapper");

  if (filtered.length === 0) {
    if (emptyCard) emptyCard.classList.remove("d-none");
    if (tableWrapper) tableWrapper.classList.add("d-none");
    if (gridWrapper) gridWrapper.classList.add("d-none");
    return;
  } else {
    if (emptyCard) emptyCard.classList.add("d-none");
    if (currentViewMode === "table" && tableWrapper) tableWrapper.classList.remove("d-none");
    if (currentViewMode === "grid" && gridWrapper) gridWrapper.classList.remove("d-none");
  }

  // Render Table
  if (tableBody) {
    tableBody.innerHTML = filtered.map(item => {
      const isMorbi = (item.region === "Morbi" || (item.factoryAddress && item.factoryAddress.toLowerCase().includes("morbi")));
      const regionBadge = isMorbi 
        ? `<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size:0.7rem;"><i class="bi bi-geo-alt me-1"></i>Morbi Facility</span>`
        : `<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" style="font-size:0.7rem;"><i class="bi bi-building me-1"></i>Jodhpur Hub</span>`;

      const statusBadge = item.status === "Active"
        ? `<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Active</span>`
        : `<span class="badge bg-secondary-subtle text-secondary border px-2 py-1"><i class="bi bi-dash-circle me-1"></i>Inactive</span>`;

      return `
        <tr>
          <td class="ps-3 py-3">
            <div class="d-flex align-items-center gap-3">
              <div class="d-flex align-items-center justify-content-center bg-primary text-white rounded-3 fw-bold flex-shrink-0" style="width:40px; height:40px; font-size:0.85rem;">
                ${escapeHtml(item.name.substring(0, 2).toUpperCase())}
              </div>
              <div class="overflow-hidden">
                <div class="fw-bold text-dark text-truncate" title="${escapeHtml(item.name)}">${escapeHtml(item.name)}</div>
                <div class="text-muted small d-flex align-items-center gap-2">
                  <span class="badge bg-light text-secondary border" style="font-size:0.68rem;">${escapeHtml(item.entityType || 'Exporter')}</span>
                  ${regionBadge}
                </div>
              </div>
            </div>
          </td>

          <td class="py-3">
            <div class="d-flex flex-column gap-1">
              <div>
                <span class="text-muted small" style="font-size:0.72rem;">IEC:</span>
                <span class="font-monospace fw-bold text-dark small">${escapeHtml(item.iec || 'N/A')}</span>
                <span class="badge bg-light text-muted border font-monospace" style="font-size:0.65rem;">Br: ${escapeHtml(item.iecBranch || '0')}</span>
              </div>
              <div>
                <span class="text-muted small" style="font-size:0.72rem;">GSTIN:</span>
                <span class="font-monospace text-primary small" style="font-size:0.78rem;">${escapeHtml(item.gstin || 'N/A')}</span>
              </div>
            </div>
          </td>

          <td class="py-3">
            <div class="small text-truncate" style="max-width: 280px;" title="${escapeHtml(item.factoryAddress || item.registeredAddress)}">
              <i class="bi bi-buildings text-muted me-1"></i>
              <span class="text-dark">${escapeHtml(item.factoryAddress || item.registeredAddress || 'Registered Office')}</span>
            </div>
            <div class="text-muted small text-truncate" style="max-width: 280px; font-size:0.74rem;">
              <i class="bi bi-person text-muted me-1"></i>${escapeHtml(item.contactPerson || 'Authorized Signatory')} (${escapeHtml(item.contactPhone || 'No Phone')})
            </div>
          </td>

          <td class="py-3">
            <div class="small">
              <span class="text-muted" style="font-size:0.72rem;">AD Code:</span>
              <span class="font-monospace fw-bold text-dark">${escapeHtml(item.adCode || 'Pending')}</span>
            </div>
            <div class="text-muted small text-truncate" style="max-width: 180px; font-size:0.72rem;" title="${escapeHtml(item.forexBank || 'N/A')}">
              ${escapeHtml(item.forexBank || 'Forex Account Verified')}
            </div>
          </td>

          <td class="py-3 text-center">
            ${statusBadge}
          </td>

          <td class="pe-3 py-3 text-end">
            <div class="btn-group btn-group-sm" role="group">
              <button type="button" class="btn btn-outline-secondary" onclick="openViewConsignerModal('${escapeHtml(item.id)}')" title="View Details">
                <i class="bi bi-eye"></i>
              </button>
              <button type="button" class="btn btn-outline-primary" onclick="openEditConsignerModal('${escapeHtml(item.id)}')" title="Edit Consigner">
                <i class="bi bi-pencil"></i>
              </button>
              <button type="button" class="btn btn-outline-danger" onclick="openDeleteConsignerModal('${escapeHtml(item.id)}')" title="Delete Consigner">
                <i class="bi bi-trash3"></i>
              </button>
            </div>
          </td>
        </tr>
      `;
    }).join("");
  }

  // Render Grid
  if (gridWrapper) {
    gridWrapper.innerHTML = filtered.map(item => {
      const isMorbi = (item.region === "Morbi" || (item.factoryAddress && item.factoryAddress.toLowerCase().includes("morbi")));
      return `
        <div class="col-md-6 col-xl-3">
          <div class="card border-0 shadow-sm h-100" style="border-radius:10px;">
            <div class="card-body p-3 d-flex flex-column">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="badge ${item.status === 'Active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'}">
                  ${escapeHtml(item.status)}
                </span>
                <span class="badge bg-light text-muted border font-monospace" style="font-size:0.7rem;">${escapeHtml(item.id)}</span>
              </div>

              <div class="d-flex align-items-center gap-2 mb-2">
                <div class="d-flex align-items-center justify-content-center bg-primary text-white rounded-3 fw-bold flex-shrink-0" style="width:36px; height:36px; font-size:0.8rem;">
                  ${escapeHtml(item.name.substring(0, 2).toUpperCase())}
                </div>
                <h6 class="fw-bold text-dark mb-0 text-truncate" title="${escapeHtml(item.name)}">${escapeHtml(item.name)}</h6>
              </div>

              <div class="text-muted small mb-2" style="font-size:0.75rem;">
                <i class="bi bi-building me-1"></i>${escapeHtml(item.entityType || 'Exporter')}
              </div>

              <div class="bg-light p-2 rounded mb-3 small" style="font-size:0.75rem;">
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-muted">IEC Code:</span>
                  <span class="font-monospace fw-bold text-dark">${escapeHtml(item.iec)}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-muted">GSTIN:</span>
                  <span class="font-monospace text-primary">${escapeHtml(item.gstin)}</span>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="text-muted">AD Code:</span>
                  <span class="font-monospace text-dark">${escapeHtml(item.adCode || 'N/A')}</span>
                </div>
              </div>

              <div class="text-muted small mb-3 text-truncate flex-grow-1" style="font-size:0.74rem;" title="${escapeHtml(item.factoryAddress || item.registeredAddress)}">
                <i class="bi bi-geo-alt text-danger me-1"></i>${escapeHtml(item.factoryAddress || item.registeredAddress)}
              </div>

              <div class="border-top pt-2 mt-auto d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="openViewConsignerModal('${escapeHtml(item.id)}')">
                  <i class="bi bi-eye me-1"></i> View
                </button>
                <div class="btn-group btn-group-sm">
                  <button type="button" class="btn btn-outline-primary" onclick="openEditConsignerModal('${escapeHtml(item.id)}')">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button type="button" class="btn btn-outline-danger" onclick="openDeleteConsignerModal('${escapeHtml(item.id)}')">
                    <i class="bi bi-trash3"></i>
                  </button>
                </div>
              </div>

            </div>
          </div>
        </div>
      `;
    }).join("");
  }
}

/**
 * Open Modal to Add New Consigner
 */
function openAddConsignerModal() {
  const form = document.getElementById("consignerForm");
  if (form) form.reset();

  document.getElementById("formConsignerId").value = "";
  document.getElementById("consignerFormModalLabel").textContent = "Add New Consigner";
  document.getElementById("formModalIcon").className = "bi bi-building-add";
  document.getElementById("formStatus").value = "Active";
  document.getElementById("formIecBranch").value = "0";

  const modal = new bootstrap.Modal(document.getElementById("consignerFormModal"));
  modal.show();
}

/**
 * Open Modal to Edit Consigner
 */
function openEditConsignerModal(id) {
  const item = consignersList.find(c => c.id === id);
  if (!item) {
    showToast("Consigner record not found", "danger");
    return;
  }

  document.getElementById("formConsignerId").value = item.id;
  document.getElementById("consignerFormModalLabel").textContent = `Edit Consigner (${item.name})`;
  document.getElementById("formModalIcon").className = "bi bi-pencil-square";

  document.getElementById("formName").value = item.name || "";
  document.getElementById("formEntityType").value = item.entityType || "Manufacturer Exporter";
  document.getElementById("formIec").value = item.iec || "";
  document.getElementById("formIecBranch").value = item.iecBranch || "0";
  document.getElementById("formGstin").value = item.gstin || "";
  document.getElementById("formPan").value = item.pan || "";
  document.getElementById("formAdCode").value = item.adCode || "";
  document.getElementById("formForexBank").value = item.forexBank || "";
  document.getElementById("formRegisteredAddress").value = item.registeredAddress || "";
  document.getElementById("formFactoryAddress").value = item.factoryAddress || "";
  document.getElementById("formContactPerson").value = item.contactPerson || "";
  document.getElementById("formContactPhone").value = item.contactPhone || "";
  document.getElementById("formContactEmail").value = item.contactEmail || "";
  document.getElementById("formStatus").value = item.status || "Active";

  const modal = new bootstrap.Modal(document.getElementById("consignerFormModal"));
  modal.show();
}

/**
 * Handle Form Submit (Create or Update)
 */
function handleConsignerFormSubmit(e) {
  e.preventDefault();

  const id = document.getElementById("formConsignerId").value;
  const name = document.getElementById("formName").value.trim();
  const entityType = document.getElementById("formEntityType").value;
  const iec = document.getElementById("formIec").value.trim().toUpperCase();
  const iecBranch = document.getElementById("formIecBranch").value.trim() || "0";
  const gstin = document.getElementById("formGstin").value.trim().toUpperCase();
  const pan = document.getElementById("formPan").value.trim().toUpperCase() || iec;
  const adCode = document.getElementById("formAdCode").value.trim();
  const forexBank = document.getElementById("formForexBank").value.trim();
  const registeredAddress = document.getElementById("formRegisteredAddress").value.trim();
  const factoryAddress = document.getElementById("formFactoryAddress").value.trim();
  const contactPerson = document.getElementById("formContactPerson").value.trim();
  const contactPhone = document.getElementById("formContactPhone").value.trim();
  const contactEmail = document.getElementById("formContactEmail").value.trim();
  const status = document.getElementById("formStatus").value;

  // Infer Region (Morbi / Jodhpur)
  let region = "Morbi";
  const combinedAddr = (factoryAddress + " " + registeredAddress).toLowerCase();
  if (combinedAddr.includes("jodhpur") || combinedAddr.includes("rajasthan")) {
    if (!combinedAddr.includes("morbi")) {
      region = "Jodhpur";
    }
  }

  if (id) {
    // UPDATE
    const index = consignersList.findIndex(c => c.id === id);
    if (index !== -1) {
      consignersList[index] = {
        ...consignersList[index],
        name,
        entityType,
        iec,
        iecBranch,
        gstin,
        pan,
        adCode,
        forexBank,
        registeredAddress,
        factoryAddress,
        region,
        contactPerson,
        contactPhone,
        contactEmail,
        status,
        updatedAt: new Date().toISOString()
      };
      saveConsignersToStorage();
      showToast(`Consigner "${name}" updated successfully.`, "success");
    }
  } else {
    // CREATE
    const newId = "EXP-" + String(Date.now()).slice(-4);
    const newConsigner = {
      id: newId,
      name,
      entityType,
      iec,
      iecBranch,
      gstin,
      pan,
      adCode,
      forexBank,
      registeredAddress,
      factoryAddress,
      region,
      contactPerson,
      contactPhone,
      contactEmail,
      status,
      createdAt: new Date().toISOString()
    };
    consignersList.unshift(newConsigner);
    saveConsignersToStorage();
    showToast(`New Consigner "${name}" registered successfully.`, "success");
  }

  // Hide Modal
  const modalEl = document.getElementById("consignerFormModal");
  const modalInstance = bootstrap.Modal.getInstance(modalEl);
  if (modalInstance) modalInstance.hide();

  renderConsigners();
  updateConsignersStats();
}

/**
 * Open Modal to View Consigner Full Legal Particulars
 */
function openViewConsignerModal(id) {
  const item = consignersList.find(c => c.id === id);
  if (!item) return;

  activeViewConsigner = item;

  const titleEl = document.getElementById("viewModalCompanyName");
  const subEl = document.getElementById("viewModalSubTitle");
  const contentEl = document.getElementById("viewModalContent");

  if (titleEl) titleEl.textContent = item.name;
  if (subEl) subEl.textContent = `ID: ${item.id} • IEC: ${item.iec} • AD Code: ${item.adCode || 'Verified'}`;

  if (contentEl) {
    contentEl.innerHTML = `
      <div class="row g-3">
        
        <!-- Top Regulatory Badges -->
        <div class="col-12">
          <div class="p-3 rounded border bg-light d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
              <span class="badge ${item.status === 'Active' ? 'bg-success' : 'bg-secondary'} me-1">${escapeHtml(item.status)}</span>
              <span class="badge bg-primary-subtle text-primary border">${escapeHtml(item.entityType || 'Manufacturer Exporter')}</span>
              <span class="badge bg-info-subtle text-info border">${escapeHtml(item.region || 'Morbi')} Cluster</span>
            </div>
            <div class="font-monospace small text-muted">
              Record ID: <strong>${escapeHtml(item.id)}</strong>
            </div>
          </div>
        </div>

        <!-- Compliance & Statutory IDs -->
        <div class="col-md-6">
          <div class="card h-100 border-0 bg-light-subtle shadow-sm p-3" style="border-radius:8px;">
            <h6 class="fw-bold text-primary mb-2 small text-uppercase"><i class="bi bi-shield-check me-1"></i> Statutory Registrations</h6>
            <div class="d-flex justify-content-between py-1 border-bottom small">
              <span class="text-muted">Import Export Code (IEC):</span>
              <span class="font-monospace fw-bold text-dark">${escapeHtml(item.iec)} (Br: ${escapeHtml(item.iecBranch || '0')})</span>
            </div>
            <div class="d-flex justify-content-between py-1 border-bottom small">
              <span class="text-muted">GST Identification (GSTIN):</span>
              <span class="font-monospace fw-bold text-primary">${escapeHtml(item.gstin)}</span>
            </div>
            <div class="d-flex justify-content-between py-1 border-bottom small">
              <span class="text-muted">Permanent Account Number (PAN):</span>
              <span class="font-monospace fw-bold text-dark">${escapeHtml(item.pan || item.iec)}</span>
            </div>
            <div class="d-flex justify-content-between py-1 small">
              <span class="text-muted">Authorized Dealer (AD) Code:</span>
              <span class="font-monospace fw-bold text-success">${escapeHtml(item.adCode || 'Verified (Customs EDI)')}</span>
            </div>
          </div>
        </div>

        <!-- Authorized Bank Details -->
        <div class="col-md-6">
          <div class="card h-100 border-0 bg-light-subtle shadow-sm p-3" style="border-radius:8px;">
            <h6 class="fw-bold text-primary mb-2 small text-uppercase"><i class="bi bi-bank me-1"></i> Authorized Forex Bank</h6>
            <p class="small text-dark mb-2"><strong>Bank & Account Details:</strong><br>${escapeHtml(item.forexBank || 'ICICI Bank / SBI Jodhpur & Morbi Commercial Branch')}</p>
            <div class="border-top pt-2 mt-auto small">
              <div class="text-muted">Authorized Signatory:</div>
              <div class="fw-bold text-dark">${escapeHtml(item.contactPerson || 'Rajesh B. Patel')}</div>
              <div class="text-muted">${escapeHtml(item.contactPhone || '')} • ${escapeHtml(item.contactEmail || '')}</div>
            </div>
          </div>
        </div>

        <!-- Registered Address -->
        <div class="col-12">
          <div class="card border-0 bg-light-subtle shadow-sm p-3" style="border-radius:8px;">
            <h6 class="fw-bold text-primary mb-1 small text-uppercase"><i class="bi bi-geo-alt me-1"></i> Registered Principal Place of Business</h6>
            <p class="small text-dark mb-2">${escapeHtml(item.registeredAddress)}</p>

            <h6 class="fw-bold text-primary mb-1 small text-uppercase mt-2"><i class="bi bi-fire me-1"></i> Manufacturing / Ceramic Works Address</h6>
            <p class="small text-dark mb-0">${escapeHtml(item.factoryAddress || item.registeredAddress)}</p>
          </div>
        </div>

      </div>
    `;
  }

  const modal = new bootstrap.Modal(document.getElementById("consignerViewModal"));
  modal.show();
}

function openEditFromViewModal() {
  if (!activeViewConsigner) return;
  const viewModalEl = document.getElementById("consignerViewModal");
  const modalInstance = bootstrap.Modal.getInstance(viewModalEl);
  if (modalInstance) modalInstance.hide();

  openEditConsignerModal(activeViewConsigner.id);
}

/**
 * Open PDF Visual Editor with this Consigner pre-selected for quick stamping
 */
function openConsignerInPdfEditor() {
  if (!activeViewConsigner) return;
  // Store selected consigner in sessionStorage so pdf-editor.php can pick it up
  sessionStorage.setItem("reliance_stamp_consigner", JSON.stringify(activeViewConsigner));
  window.location.href = "pdf-editor.php?action=stamp_consigner";
}

/**
 * Open Modal to Confirm Delete
 */
function openDeleteConsignerModal(id) {
  const item = consignersList.find(c => c.id === id);
  if (!item) return;

  pendingDeleteId = id;
  const nameEl = document.getElementById("deleteConsignerName");
  if (nameEl) nameEl.textContent = item.name;

  const modal = new bootstrap.Modal(document.getElementById("consignerDeleteModal"));
  modal.show();
}

/**
 * Execute Deletion
 */
function executeDeleteConsigner() {
  if (!pendingDeleteId) return;

  const item = consignersList.find(c => c.id === pendingDeleteId);
  const deletedName = item ? item.name : "Consigner";

  consignersList = consignersList.filter(c => c.id !== pendingDeleteId);
  saveConsignersToStorage();

  pendingDeleteId = null;

  const modalEl = document.getElementById("consignerDeleteModal");
  const modalInstance = bootstrap.Modal.getInstance(modalEl);
  if (modalInstance) modalInstance.hide();

  showToast(`Consigner "${deletedName}" has been deleted.`, "warning");
  renderConsigners();
  updateConsignersStats();
}

/**
 * Reset default sample consigners
 */
function resetDefaultConsigners() {
  if (confirm("Restore the default Reliance Granito export consigners dataset? Any custom additions will be reset.")) {
    consignersList = JSON.parse(JSON.stringify(DEFAULT_CONSIGNERS_SEED));
    saveConsignersToStorage();
    renderConsigners();
    updateConsignersStats();
    showToast("Master consigners reset to default factory records.", "info");
  }
}

/**
 * Export Consigners to JSON File
 */
function exportConsignersJSON() {
  const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(consignersList, null, 2));
  const downloadAnchor = document.createElement("a");
  downloadAnchor.setAttribute("href", dataStr);
  downloadAnchor.setAttribute("download", `reliance_granito_consigners_${new Date().toISOString().slice(0, 10)}.json`);
  document.body.appendChild(downloadAnchor);
  downloadAnchor.click();
  downloadAnchor.remove();
  showToast("Consigners exported to JSON file.", "success");
}

/**
 * Export Consigners to CSV File
 */
function exportConsignersCSV() {
  const headers = ["ID", "Name", "EntityType", "IEC", "Branch", "GSTIN", "PAN", "ADCode", "ForexBank", "RegisteredAddress", "FactoryAddress", "Region", "ContactPerson", "ContactPhone", "ContactEmail", "Status"];
  const rows = consignersList.map(c => [
    c.id,
    `"${(c.name || '').replace(/"/g, '""')}"`,
    `"${(c.entityType || '').replace(/"/g, '""')}"`,
    c.iec || '',
    c.iecBranch || '0',
    c.gstin || '',
    c.pan || '',
    c.adCode || '',
    `"${(c.forexBank || '').replace(/"/g, '""')}"`,
    `"${(c.registeredAddress || '').replace(/"/g, '""')}"`,
    `"${(c.factoryAddress || '').replace(/"/g, '""')}"`,
    c.region || '',
    `"${(c.contactPerson || '').replace(/"/g, '""')}"`,
    `"${(c.contactPhone || '').replace(/"/g, '""')}"`,
    c.contactEmail || '',
    c.status || 'Active'
  ]);

  const csvContent = "data:text/csv;charset=utf-8," + [headers.join(","), ...rows.map(r => r.join(","))].join("\n");
  const downloadAnchor = document.createElement("a");
  downloadAnchor.setAttribute("href", encodeURI(csvContent));
  downloadAnchor.setAttribute("download", `reliance_granito_consigners_${new Date().toISOString().slice(0, 10)}.csv`);
  document.body.appendChild(downloadAnchor);
  downloadAnchor.click();
  downloadAnchor.remove();
  showToast("Consigners exported to CSV file.", "success");
}

/**
 * Toast notifications helper
 */
function showToast(message, type = "info") {
  const toastEl = document.getElementById("consignerToast");
  const msgEl = document.getElementById("toastMessage");
  const iconEl = document.getElementById("toastIcon");

  if (!toastEl || !msgEl) return;

  msgEl.textContent = message;

  if (iconEl) {
    if (type === "success") {
      iconEl.className = "bi bi-check-circle-fill text-success fs-5";
    } else if (type === "warning") {
      iconEl.className = "bi bi-exclamation-triangle-fill text-warning fs-5";
    } else if (type === "danger") {
      iconEl.className = "bi bi-x-circle-fill text-danger fs-5";
    } else {
      iconEl.className = "bi bi-info-circle-fill text-info fs-5";
    }
  }

  const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
  toast.show();
}

/**
 * Helper: Escape HTML to avoid XSS
 */
function escapeHtml(str) {
  if (!str) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}
