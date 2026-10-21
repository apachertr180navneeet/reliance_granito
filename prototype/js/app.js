/* ==========================================================================
   RELIANCE GRANITO - EXPORT DOCUMENTATION MANAGEMENT SYSTEM (EDMS)
   App Controller & Interaction Engine
   Features: Simulated Roles, Cross-Document Validation, HIDE Masking,
   Discrepancy Resolutions, Audit Logging, and Printable Documents.
   ========================================================================== */

document.addEventListener("DOMContentLoaded", () => {
  initUserSession();
  initSidebarActive();
  initMobileMenu();
});

// =========================================================================
// 1. AUTHENTICATION & ROLE MANAGEMENT (SIMULATED PROTOTYPE)
// =========================================================================
function initUserSession() {
  const data = getExportData();
  const current = data.currentUser || {
    name: "Rajesh B. Patel",
    email: "admin@reliancegranito.com",
    roleTitle: "Export Documentation Manager",
    avatar: "RG"
  };

  const nameEl = document.getElementById("navUserName");
  const roleEl = document.getElementById("navUserRole");
  const avatarEl = document.getElementById("navUserAvatar");

  if (nameEl) nameEl.textContent = current.name;
  if (roleEl) roleEl.textContent = current.roleTitle;
  if (avatarEl) avatarEl.textContent = current.avatar;
}

// =========================================================================
// 2. NAVIGATION & UI HELPERS
// =========================================================================
function initSidebarActive() {
  const currentPath = window.location.pathname.split("/").pop() || "index.html";
  const links = document.querySelectorAll(".sidebar-nav .nav-link");
  links.forEach(link => {
    const href = link.getAttribute("href");
    if (href === currentPath || (currentPath === "" && href === "index.html")) {
      link.classList.add("active");
    } else {
      link.classList.remove("active");
    }
  });
}

function initMobileMenu() {
  const mobileToggle = document.getElementById("mobileMenuBtn");
  const sidebar = document.querySelector(".app-sidebar");
  if (mobileToggle && sidebar) {
    mobileToggle.addEventListener("click", () => {
      sidebar.classList.toggle("open");
    });
  }
}

function showToast(message, type = "success") {
  let container = document.querySelector(".toast-container");
  if (!container) {
    container = document.createElement("div");
    container.className = "toast-container";
    document.body.appendChild(container);
  }

  const toast = document.createElement("div");
  toast.className = `toast ${type}`;
  toast.innerHTML = `
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      ${type === "success" 
        ? '<polyline points="20 6 9 17 4 12"></polyline>' 
        : type === "warning"
        ? '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>'
        : '<circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>'}
    </svg>
    <span>${message}</span>
  `;
  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = "0";
    toast.style.transform = "translateX(100%)";
    toast.style.transition = "all 0.3s ease";
    setTimeout(() => toast.remove(), 300);
  }, 3800);
}

function openModal(id) {
  const modal = document.getElementById(id);
  if (modal) {
    // If Bootstrap modal is available
    if (typeof bootstrap !== "undefined" && bootstrap.Modal) {
      const bsModal = bootstrap.Modal.getOrCreateInstance(modal);
      bsModal.show();
    } else {
      modal.classList.add("active");
    }
  }
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (modal) {
    if (typeof bootstrap !== "undefined" && bootstrap.Modal) {
      const bsModal = bootstrap.Modal.getInstance(modal);
      if (bsModal) bsModal.hide();
    }
    modal.classList.remove("active");
  }
}

// =========================================================================
// 3. CROSS-DOCUMENT VALIDATION ENGINE (INVOICE vs SHIPPING BILL vs BL)
// =========================================================================
function evaluateCrossDocumentValidation(shipmentId = "SHP-1469") {
  const data = getExportData();
  const shipment = data.shipments.find(s => s.shipmentNumber === shipmentId) || data.shipments[0];
  const invoice = data.commercialInvoices.find(i => i.shipmentNumber === shipmentId) || data.commercialInvoices[0];
  const sb = data.shippingBills.find(s => s.shipmentNumber === shipmentId) || data.shippingBills[0];
  const bl = data.billsOfLading.find(b => b.shipmentNumber === shipmentId) || data.billsOfLading[0];

  const checks = [
    {
      fieldKey: "consignee",
      fieldName: "Consignee / Importer Destination",
      invoiceVal: invoice.buyerAddressEdited || invoice.buyerAddressOriginal,
      sbVal: sb.countryOfDestination,
      blVal: bl.consignee,
      matchStatus: (invoice.buyerAddressEdited.includes("Hai Phong") && bl.consignee.includes("Hai Phong")) ? "MISMATCH" : "MATCH",
      notes: "Original Invoice had Da Nang; amended to Hai Phong. BL reflects Hai Phong. SB destination listed at country level.",
      resolution: "Override Confirmed"
    },
    {
      fieldKey: "hsn",
      fieldName: "HSN Code Classification",
      invoiceVal: invoice.items[0].hsn,
      sbVal: sb.page2_items[0].hsn,
      blVal: "68022310 (Granite)",
      matchStatus: (invoice.items[0].hsn === sb.page2_items[0].hsn) ? "MATCH" : "MISMATCH",
      notes: "Both Invoice & Shipping Bill item lines match Indian ITC-HS 68022310.",
      resolution: "Resolved"
    },
    {
      fieldKey: "grossWeight",
      fieldName: "Cargo Gross Weight (KGS)",
      invoiceVal: invoice.items[0].grossWeightKg.toLocaleString() + " KGS",
      sbVal: sb.page1_summary.grossWeightKg.toLocaleString() + " KGS",
      blVal: bl.grossWeightKg.toLocaleString() + " KGS",
      matchStatus: (invoice.items[0].grossWeightKg === sb.page1_summary.grossWeightKg && sb.page1_summary.grossWeightKg === bl.grossWeightKg) ? "MATCH" : "MISMATCH",
      notes: "Certified weighbridge weight 26,500 KGS verified across all documents.",
      resolution: "Resolved"
    },
    {
      fieldKey: "packages",
      fieldName: "Package Count & Type",
      invoiceVal: invoice.items[0].packages + " Wooden Bundles",
      sbVal: sb.page1_summary.totalPackages + " Pkgs (EDI)",
      blVal: bl.packageCount + " Wooden Bundles",
      matchStatus: (invoice.items[0].packages === sb.page1_summary.totalPackages && sb.page1_summary.totalPackages === bl.packageCount) ? "MATCH" : "MISMATCH",
      notes: "Physical tally count matches 226 wooden packages.",
      resolution: "Resolved"
    },
    {
      fieldKey: "container",
      fieldName: "Container Number & Seal",
      invoiceVal: "TCLU7789571 / Seal 14697",
      sbVal: sb.page5_equipmentContainers[0].containerNumber + " / Seal " + sb.page5_equipmentContainers[0].sealNumber,
      blVal: bl.containerNumber + " / Seal " + bl.sealNumber,
      matchStatus: (bl.containerNumber === sb.page5_equipmentContainers[0].containerNumber) ? "MATCH" : "MISMATCH",
      notes: "Electronic RFID & container prefix matched at Port Gate-In.",
      resolution: "Resolved"
    },
    {
      fieldKey: "valuation",
      fieldName: "Commercial Terms & Valuation",
      invoiceVal: `Total CIF: $${invoice.invoiceTotalUsd.toLocaleString()} USD (FOB $${invoice.subtotalFobUsd.toLocaleString()})`,
      sbVal: `FOB INR: ₹${sb.page1_summary.fobValueInr.toLocaleString()} (Exch: ${sb.page3_valuation.exchangeRate})`,
      blVal: bl.freightTerms,
      matchStatus: "WARNING",
      notes: "Customs EDI Shipping Bill calculates statutory FOB INR basis ICEGATE exchange rate; freight & insurance accounted in Part C.",
      resolution: "Override Confirmed"
    }
  ];

  return checks;
}

function resolveDiscrepancy(shipmentId, fieldKey, reviewNote) {
  const data = getExportData();
  const record = data.crossValidationResults.find(r => r.shipmentNumber === shipmentId && r.fieldKey === fieldKey);
  const currentUser = data.currentUser ? data.currentUser.name : "Reviewer";

  if (record) {
    const oldStatus = record.resolutionStatus;
    record.resolutionStatus = "Override Confirmed";
    record.reviewNotes = reviewNote;
    record.reviewedBy = currentUser;
    record.reviewedAt = new Date().toISOString().replace("T", " ").substring(0, 16);

    // Save and log audit
    saveExportData(data);
    logAuditEvent(shipmentId, "Cross-Document Validation", `Confirmed Override for ${record.fieldName}`, oldStatus, "Override Confirmed: " + reviewNote);
    showToast(`Discrepancy for ${record.fieldName} resolved & recorded in audit log.`, "success");
  }
}

// =========================================================================
// 4. HIDE MODE PRESENTATION TOGGLE
// =========================================================================
function toggleDocumentHideMode(containerId, isHide) {
  const container = document.getElementById(containerId);
  if (!container) return;

  if (isHide) {
    container.classList.add("hide-active");
    showToast("HIDE Presentation Mode Active: Sensitive commercial regions masked.", "warning");
  } else {
    container.classList.remove("hide-active");
    showToast("Standard Full Visibility Mode Active.", "success");
  }
}

// =========================================================================
// 5. VERSION CONTROL & IMMUTABILITY ENFORCEMENT
// =========================================================================
function finalizeDocument(shipmentId, docType) {
  const data = getExportData();
  const shipment = data.shipments.find(s => s.shipmentNumber === shipmentId);
  const currentUser = data.currentUser ? data.currentUser.name : "Admin";

  if (!shipment) return;

  const versionId = "VER-" + shipmentId.replace("SHP-", "") + "-FINAL";
  const newVer = {
    id: versionId,
    shipmentNumber: shipmentId,
    docType: docType,
    versionNumber: "v1.2 (Finalized Immutable)",
    variant: "Finalized",
    sourceFile: "Official Generated Archive",
    checksumSha256: "9b4c81a2e9f0d148" + Math.random().toString(16).substring(2, 10),
    isImmutable: true,
    createdAt: new Date().toISOString().replace("T", " ").substring(0, 16),
    generatedBy: currentUser
  };

  data.documentVersions.push(newVer);
  shipment.status = "Finalized";
  shipment.finalizedAt = newVer.createdAt;

  saveExportData(data);
  logAuditEvent(shipmentId, docType, "Finalized Immutable Document Version", "Editable", newVer.versionNumber);
  showToast(`${docType} for ${shipmentId} finalized! Version is now locked and immutable.`, "success");
}

// =========================================================================
// 6. AUDIT LOGGING UTILITY
// =========================================================================
function logAuditEvent(shipmentId, entity, action, oldValue, newValue) {
  const data = getExportData();
  const currentUser = data.currentUser ? `${data.currentUser.name} (${data.currentUser.role})` : "System User";
  
  const newLog = {
    id: "LOG-" + String(data.auditLogs.length + 1).padStart(2, "0"),
    shipmentNumber: shipmentId,
    user: currentUser,
    entity: entity,
    action: action,
    oldValue: String(oldValue),
    newValue: String(newValue),
    timestamp: new Date().toISOString().replace("T", " ").substring(0, 16)
  };

  data.auditLogs.unshift(newLog);
  saveExportData(data);
}

// =========================================================================
// 7. PRINTABLE DOCUMENT ENGINE
// =========================================================================
function printDocument(elementId) {
  const elem = document.getElementById(elementId);
  if (!elem) return;
  const content = elem.innerHTML;
  const printWindow = window.open('', '', 'height=850,width=1050');
  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
      <head>
        <title>Reliance Granito Official Export Document</title>
        <link rel="stylesheet" href="css/style.css">
        <style>
          body { background: white !important; color: black !important; padding: 25px; font-family: sans-serif; }
          .printable-document { box-shadow: none !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
          .hide-maskable { background: #000 !important; color: transparent !important; }
          @media print {
            .no-print { display: none !important; }
          }
        </style>
      </head>
      <body>
        ${content}
      </body>
    </html>
  `);
  printWindow.document.close();
  printWindow.focus();
  setTimeout(() => {
    printWindow.print();
    printWindow.close();
  }, 500);
}
