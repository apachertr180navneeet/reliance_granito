<?php
// Reliance Granito Export Documentation Management System (EDMS)
// Clean & Intuitive Visual PDF Editor (Pure CSS Classes - Zero Inline Styles)
$pageTitle = "Reliance Granito | Visual PDF Editor & Masker";
$activeNav = "pdf-editor";
include_once __DIR__ . '/includes/header.php';
?>

<script>document.body.classList.add('pdf-editor-active');</script>

<div class="editor-app">
  <!-- Top Navigation & Controls Header -->
  <header class="editor-header">
    <!-- Left: Document Identity -->
    <div class="doc-info">
      <div class="doc-icon-badge">
        <i class="bi bi-file-earmark-pdf fs-5"></i>
      </div>
      <div class="doc-meta-block">
        <div class="d-flex align-items-center gap-2">
          <span class="doc-pill" id="docNameLabel">No File Loaded</span>
          <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle doc-save-badge" id="saveBadge">No File</span>
        </div>
        <div class="doc-status-text" id="docStatusLabel">Upload a PDF to start editing</div>
      </div>
    </div>

    <!-- Center: Segmented Tool Palette & Consigner Stamping Dropdown -->
    <div class="editor-header-center">
      <div class="tool-palette">
        <button type="button" class="tool-btn active" id="btnToolSelect" onclick="setTool('select')" title="Select / Move / Resize (V)">
          <i class="bi bi-cursor-fill"></i> <span>Select</span>
        </button>
        <button type="button" class="tool-btn" id="btnToolWhiteout" onclick="setTool('whiteout')" title="Draw Solid Whiteout Mask (W)">
          <i class="bi bi-square-fill text-warning"></i> <span>Whiteout</span>
        </button>
        <button type="button" class="tool-btn" id="btnToolText" onclick="setTool('text')" title="White Mask + Replacement Text (T)">
          <i class="bi bi-fonts text-primary"></i> <span>Replace Text</span>
        </button>
        <button type="button" class="tool-btn" id="btnToolRedact" onclick="setTool('redact')" title="Solid Black Redaction (R)">
          <i class="bi bi-square-fill text-dark"></i> <span>Redact</span>
        </button>
      </div>

      <!-- Consigner Stamping Menu -->
      <div class="dropdown">
        <button class="btn btn-consigner-picker dropdown-toggle" data-bs-toggle="dropdown" id="btnConsignerDropdown" title="Stamp Registered Consigner Particulars (IEC, GSTIN, Address, Bank)">
          <i class="bi bi-building-up text-primary"></i>
          <span>Consigner</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end consigner-menu-popover shadow-lg" id="consignerDropdownMenu">
          <li><h6 class="dropdown-header text-uppercase">Stamp Registered Consigner</h6></li>
          <li id="consignerListItems"><span class="dropdown-item small text-muted">Loading Consigners...</span></li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item d-flex align-items-center gap-2 text-primary fw-semibold py-2 px-3" href="consigners.php">
              <i class="bi bi-gear"></i> Manage Consigners Directory &rarr;
            </a>
          </li>
        </ul>
      </div>

      <!-- Contextual Formatting Bar -->
      <div class="format-bar" id="formatBar">
        <span class="badge bg-white text-success border border-success-subtle px-2 py-1 format-badge-solid">
          <i class="bi bi-shield-fill-check me-1"></i>Solid White Mask
        </span>
        <span class="fw-bold text-primary ms-1 format-label-size">SIZE:</span>
        <button class="btn btn-sm btn-light border py-0 px-2" onclick="stepFontSize(-1)" title="Decrease Size">-</button>
        <span id="fontSizeDisplay" class="fw-semibold text-dark px-1 format-font-val">10pt</span>
        <button class="btn btn-sm btn-light border py-0 px-2" onclick="stepFontSize(1)" title="Increase Size">+</button>
        <button class="btn btn-sm btn-light border py-0 px-2" id="btnBoldToggle" onclick="toggleBold()" title="Toggle Bold"><b>B</b></button>
        <button class="btn btn-sm btn-light border py-0 px-2" onclick="fitSelected()" title="Auto-fit white mask box to text"><i class="bi bi-arrows-angle-expand me-1"></i>Fit Box</button>
        <button class="btn btn-sm btn-success py-0 px-2 fw-semibold ms-1" onclick="deselectAll()" title="Done with this edit"><i class="bi bi-check2 me-1"></i>Done</button>
        <button class="btn btn-sm btn-outline-danger py-0 px-2 ms-1" onclick="deleteSelected()" title="Delete Selected"><i class="bi bi-trash"></i></button>
      </div>
    </div>

    <!-- Right Actions: Upload, Save & Export -->
    <div class="editor-header-right">
      <input type="file" id="fileInput" accept="application/pdf" class="file-input-hidden" onchange="handleFile(event)">
      
      <button class="btn btn-header-upload" onclick="document.getElementById('fileInput').click()" title="Upload a local PDF file">
        <i class="bi bi-upload text-primary"></i> <span>Upload PDF</span>
      </button>

      <!-- Prominent Save & Export Clean PDF Button -->
      <button class="btn btn-header-export" id="btnDownload" onclick="downloadPdf()" disabled title="Save edits and download updated PDF">
        <i class="bi bi-floppy2-fill"></i>
        <span>Save / Export PDF</span>
      </button>
    </div>
  </header>

  <!-- Main Viewport / Desk -->
  <main class="editor-viewport" id="viewport">
    
    <!-- Modern Elegant Dropzone Empty State -->
    <div class="empty-hero" id="emptyHero">
      <div class="empty-dropzone" onclick="document.getElementById('fileInput').click()" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" ondrop="handleDrop(event)">
        <div class="dropzone-icon-bubble">
          <i class="bi bi-cloud-arrow-up fs-1 text-primary"></i>
        </div>
        <h4 class="fw-bold text-white mb-2">Upload Export PDF Document</h4>
        <p class="text-white-50 small empty-desc">
          Drag & drop your export document here, or click to browse. Easily whiteout sensitive values, stamp verified consigner particulars, and edit text with 100% solid masking.
        </p>
        <button type="button" class="btn btn-primary btn-sm px-4 py-2 shadow-sm fw-semibold d-inline-flex align-items-center gap-2">
          <i class="bi bi-folder2-open"></i> Browse PDF File
        </button>
        <div class="text-white-50 small mt-3 dropzone-hint">
          Supports: Customs EDI Shipping Bills, Commercial Invoices, Bills of Lading, Packing Lists
        </div>
      </div>
    </div>

    <!-- Paper Sheet Container -->
    <div class="paper-sheet" id="paperSheet">
      <canvas id="pdfCanvas"></canvas>
      <canvas id="drawCanvas"></canvas>
      
      <!-- Direct In-Place Textarea Input (Opaque White) -->
      <textarea id="liveInput" placeholder="Type replacement text here..."></textarea>
      
      <!-- Floating Action Buttons right below the editing textarea -->
      <div id="liveInputActions">
        <button type="button" class="btn btn-primary btn-sm py-0 px-2 fw-semibold d-flex align-items-center gap-1 btn-live-action" onmousedown="event.preventDefault(); commitLiveText();">
          <i class="bi bi-check-lg"></i> Save Edit
        </button>
        <button type="button" class="btn btn-light btn-sm py-0 px-2 border d-flex align-items-center gap-1 btn-live-action" onmousedown="event.preventDefault(); cancelLiveText();">
          <i class="bi bi-x-lg"></i> Cancel
        </button>
      </div>
    </div>

    <!-- Floating Dock -->
    <div class="floating-dock" id="floatingDock">
      <button class="dock-btn" id="btnPrev" onclick="navPage(-1)" disabled title="Previous Page">
        <i class="bi bi-chevron-left"></i>
      </button>
      <span class="dock-text" id="dockPageText">Page 1 / 1</span>
      <button class="dock-btn" id="btnNext" onclick="navPage(1)" disabled title="Next Page">
        <i class="bi bi-chevron-right"></i>
      </button>

      <div class="dock-divider"></div>

      <button class="dock-btn" onclick="zoomChange(-0.15)" title="Zoom Out">
        <i class="bi bi-zoom-out"></i>
      </button>
      <span class="dock-text" id="dockZoomText">100%</span>
      <button class="dock-btn" onclick="zoomChange(0.15)" title="Zoom In">
        <i class="bi bi-zoom-in"></i>
      </button>

      <div class="dock-divider"></div>

      <button class="dock-btn" onclick="undo()" title="Undo (Ctrl+Z)"><i class="bi bi-arrow-counterclockwise"></i></button>
      <button class="dock-btn" onclick="redo()" title="Redo (Ctrl+Y)"><i class="bi bi-arrow-clockwise"></i></button>
    </div>

  </main>
</div>

<!-- Essential CDNs -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
  if (typeof pdfjsLib !== 'undefined') {
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
  }
</script>
<script src="https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>

<!-- Clean, Modular & Responsive Editor Logic -->
<script>
/**
 * Clean Visual PDF Editor
 * Logic: Streamlined Canvas Hit-testing, Drawing, 100% Solid White Masking & PDF-Lib Export
 */

const App = {
  pdfDoc: null,
  rawBytes: null,
  fileName: "document.pdf",
  numPages: 0,
  page: 1,
  scale: 1.0,
  tool: "select", // "select" | "whiteout" | "text" | "redact"

  // Per-page annotations: { [pageNum]: Array<Item> }
  annotations: {},
  selectedId: null,
  activeItem: null,
  editingId: null,

  // Drag / Resize / Draw interaction states
  isDrawing: false,
  isDragging: false,
  isResizing: false,
  resizeCorner: null,
  startX: 0,
  startY: 0,
  originBox: null,

  // History Stack
  history: [],
  historyIdx: -1
};

// Cached DOM Elements
const ui = {
  emptyHero: document.getElementById("emptyHero"),
  paperSheet: document.getElementById("paperSheet"),
  pdfCanvas: document.getElementById("pdfCanvas"),
  drawCanvas: document.getElementById("drawCanvas"),
  liveInput: document.getElementById("liveInput"),
  liveInputActions: document.getElementById("liveInputActions"),
  floatingDock: document.getElementById("floatingDock"),
  dockPageText: document.getElementById("dockPageText"),
  dockZoomText: document.getElementById("dockZoomText"),
  btnPrev: document.getElementById("btnPrev"),
  btnNext: document.getElementById("btnNext"),
  btnDownload: document.getElementById("btnDownload"),
  docNameLabel: document.getElementById("docNameLabel"),
  docStatusLabel: document.getElementById("docStatusLabel"),
  saveBadge: document.getElementById("saveBadge"),
  formatBar: document.getElementById("formatBar"),
  fontSizeDisplay: document.getElementById("fontSizeDisplay"),
  btnBoldToggle: document.getElementById("btnBoldToggle")
};

// =========================================================================
// 1. INITIALIZATION & FILE LOADING
// =========================================================================

document.addEventListener("DOMContentLoaded", () => {
  setupCanvasEvents();
  setupKeyboardEvents();
  initConsignersDropdown();
});

function handleDragOver(e) {
  e.preventDefault();
  e.stopPropagation();
  const dz = document.querySelector(".empty-dropzone");
  if (dz) dz.classList.add("dragover");
}

function handleDragLeave(e) {
  e.preventDefault();
  e.stopPropagation();
  const dz = document.querySelector(".empty-dropzone");
  if (dz) dz.classList.remove("dragover");
}

function handleDrop(e) {
  e.preventDefault();
  e.stopPropagation();
  const dz = document.querySelector(".empty-dropzone");
  if (dz) dz.classList.remove("dragover");
  const files = e.dataTransfer && e.dataTransfer.files;
  if (files && files.length > 0) {
    const file = files[0];
    if (file.type === "application/pdf" || file.name.toLowerCase().endsWith(".pdf")) {
      const reader = new FileReader();
      reader.onload = async () => {
        await loadPdfBytes(reader.result, file.name);
      };
      reader.readAsArrayBuffer(file);
    } else {
      alert("Please drop a valid PDF document.");
    }
  }
}

function handleFile(e) {
  const file = e.target.files && e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = async () => {
    await loadPdfBytes(reader.result, file.name);
  };
  reader.readAsArrayBuffer(file);
}

async function loadPdfBytes(arrayBuffer, name) {
  App.rawBytes = arrayBuffer.slice(0);
  App.fileName = name;
  App.annotations = {};
  App.history = [];
  App.historyIdx = -1;
  App.selectedId = null;

  ui.docNameLabel.textContent = name;
  ui.docStatusLabel.textContent = "Parsing...";

  const task = pdfjsLib.getDocument({ data: arrayBuffer });
  App.pdfDoc = await task.promise;
  App.numPages = App.pdfDoc.numPages;
  App.page = 1;

  // Mobile responsive initial scale fit
  if (window.innerWidth < 768) {
    App.scale = Math.max(0.45, Math.min(1.0, (window.innerWidth - 32) / 600));
  } else {
    App.scale = 1.0;
  }

  ui.emptyHero.style.display = "none";
  ui.paperSheet.style.display = "block";
  ui.floatingDock.style.display = "flex";
  ui.btnDownload.disabled = false;
  ui.docStatusLabel.textContent = `${App.numPages} Page${App.numPages > 1 ? 's' : ''}`;

  saveSnapshot();
  updateSaveBadge(false);
  await renderPage();
  updateDock();
}

// =========================================================================
// 2. PAGE RENDERING (PDF.JS + OVERLAY)
// =========================================================================

async function renderPage() {
  if (!App.pdfDoc) return;
  const page = await App.pdfDoc.getPage(App.page);
  
  // Calculate sharp high-DPI resolution
  const pixelRatio = window.devicePixelRatio || 1.5;
  const viewport = page.getViewport({ scale: App.scale * pixelRatio });
  const unscaled = page.getViewport({ scale: 1.0 });

  const displayWidth = unscaled.width * App.scale;
  const displayHeight = unscaled.height * App.scale;

  // Set Canvas internal buffer sizes
  ui.pdfCanvas.width = viewport.width;
  ui.pdfCanvas.height = viewport.height;
  ui.drawCanvas.width = viewport.width;
  ui.drawCanvas.height = viewport.height;

  // Set display dimensions dynamically to match scale
  ui.pdfCanvas.style.width = `${displayWidth}px`;
  ui.pdfCanvas.style.height = `${displayHeight}px`;
  ui.drawCanvas.style.width = `${displayWidth}px`;
  ui.drawCanvas.style.height = `${displayHeight}px`;
  ui.paperSheet.style.width = `${displayWidth}px`;
  ui.paperSheet.style.height = `${displayHeight}px`;

  const ctx = ui.pdfCanvas.getContext("2d");
  await page.render({ canvasContext: ctx, viewport: viewport }).promise;

  redraw();
}

function getPageItems() {
  if (!App.annotations[App.page]) {
    App.annotations[App.page] = [];
  }
  return App.annotations[App.page];
}

// =========================================================================
// 3. CANVAS INTERACTION (DRAW, MOVE, RESIZE, IN-PLACE EDIT)
// =========================================================================

function setupCanvasEvents() {
  ui.drawCanvas.addEventListener("mousedown", onMouseDown);
  window.addEventListener("mousemove", onMouseMove);
  window.addEventListener("mouseup", onMouseUp);
  ui.drawCanvas.addEventListener("dblclick", onDoubleClick);

  // Mobile touchscreen support (touchstart, touchmove, touchend)
  function touchWrapper(handler) {
    return function(e) {
      if (e.touches && e.touches.length > 0) {
        const t = e.touches[0];
        const synthetic = {
          clientX: t.clientX,
          clientY: t.clientY,
          target: e.target,
          preventDefault: () => e.preventDefault(),
          stopPropagation: () => e.stopPropagation()
        };
        handler(synthetic);
      } else if (e.type === "touchend") {
        handler(e);
      }
    };
  }

  ui.drawCanvas.addEventListener("touchstart", touchWrapper(onMouseDown), { passive: false });
  window.addEventListener("touchmove", touchWrapper(onMouseMove), { passive: false });
  window.addEventListener("touchend", onMouseUp, { passive: false });

  // Live in-place text input
  ui.liveInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      commitLiveText();
    } else if (e.key === "Escape") {
      cancelLiveText();
    }
  });

  ui.liveInput.addEventListener("blur", () => {
    setTimeout(() => {
      const active = document.activeElement;
      if (ui.liveInputActions && ui.liveInputActions.contains(active)) return;
      commitLiveText();
    }, 150);
  });
}

function getNormalized(e) {
  const rect = ui.drawCanvas.getBoundingClientRect();
  const rawX = e.clientX - rect.left;
  const rawY = e.clientY - rect.top;
  const x = Math.max(0, Math.min(1, rawX / rect.width));
  const y = Math.max(0, Math.min(1, rawY / rect.height));
  return { x, y, rawX, rawY, rectW: rect.width, rectH: rect.height };
}

function onMouseDown(e) {
  if (!App.pdfDoc) return;
  if (App.editingId) commitLiveText();

  const { x, y, rawX, rawY, rectW, rectH } = getNormalized(e);
  const items = getPageItems();

  // Check resize handle on selected item
  if (App.selectedId) {
    const sel = items.find(i => i.id === App.selectedId);
    if (sel) {
      const handle = hitCornerHandle(sel, rawX, rawY, rectW, rectH);
      if (handle) {
        App.isResizing = true;
        App.resizeCorner = handle;
        App.startX = x;
        App.startY = y;
        App.originBox = { ...sel };
        return;
      }
    }
  }

  // Hit test existing item
  let hit = null;
  for (let i = items.length - 1; i >= 0; i--) {
    const it = items[i];
    if (x >= it.x && x <= it.x + it.w && y >= it.y && y <= it.y + it.h) {
      hit = it;
      break;
    }
  }

  if (hit) {
    App.selectedId = hit.id;
    App.isDragging = true;
    App.startX = x;
    App.startY = y;
    App.originBox = { ...hit };
    syncFormatBar(hit);
    redraw();
    return;
  }

  // Click on empty space
  if (App.tool === "select") {
    App.selectedId = null;
    syncFormatBar(null);
    redraw();
    return;
  }

  // Drawing new item (whiteout, text, redact)
  App.selectedId = null;
  App.isDrawing = true;
  App.startX = x;
  App.startY = y;

  const newItem = {
    id: "item_" + Date.now(),
    type: App.tool,
    x: x,
    y: y,
    w: 0.001,
    h: 0.001,
    text: "",
    fontSize: 10,
    isBold: false,
    color: "#0F172A",
    backgroundColor: App.tool === "redact" ? "#000000" : "#FFFFFF"
  };

  items.push(newItem);
  App.activeItem = newItem;
  syncFormatBar(null);
  redraw();
}

function onMouseMove(e) {
  if (!App.pdfDoc) return;
  const { x, y, rawX, rawY, rectW, rectH } = getNormalized(e);
  const items = getPageItems();

  // Dynamic cursor feedback
  if (!App.isDrawing && !App.isDragging && !App.isResizing) {
    let cur = (App.tool === "select") ? "default" : "crosshair";
    if (App.selectedId) {
      const sel = items.find(i => i.id === App.selectedId);
      if (sel && hitCornerHandle(sel, rawX, rawY, rectW, rectH)) {
        cur = "nwse-resize";
      }
    }
    ui.drawCanvas.style.cursor = cur;
  }

  // Draw box
  if (App.isDrawing && App.activeItem) {
    App.activeItem.x = Math.min(App.startX, x);
    App.activeItem.y = Math.min(App.startY, y);
    App.activeItem.w = Math.max(0.005, Math.abs(x - App.startX));
    App.activeItem.h = Math.max(0.005, Math.abs(y - App.startY));
    redraw();
    return;
  }

  // Move box
  if (App.isDragging && App.selectedId) {
    const item = items.find(i => i.id === App.selectedId);
    if (!item || !App.originBox) return;
    const dx = x - App.startX;
    const dy = y - App.startY;
    item.x = Math.max(0, Math.min(1 - item.w, App.originBox.x + dx));
    item.y = Math.max(0, Math.min(1 - item.h, App.originBox.y + dy));
    redraw();
    return;
  }

  // Resize box
  if (App.isResizing && App.selectedId) {
    const item = items.find(i => i.id === App.selectedId);
    if (!item || !App.originBox) return;
    if (App.resizeCorner === "se") {
      item.w = Math.max(0.01, x - App.originBox.x);
      item.h = Math.max(0.01, y - App.originBox.y);
    }
    redraw();
    return;
  }
}

function onMouseUp() {
  if (App.isDrawing && App.activeItem) {
    const item = App.activeItem;
    App.isDrawing = false;
    App.activeItem = null;

    // Filter tiny accidental clicks or single click creation
    if (item.w < 0.02 || item.h < 0.02) {
      if (item.type === "text") {
        item.w = 0.22;
        item.h = 0.045;
        App.selectedId = item.id;
        saveSnapshot();
        updateSaveBadge(true);
        redraw();
        startLiveInput(item);
        return;
      }
      const items = getPageItems();
      const idx = items.indexOf(item);
      if (idx !== -1) items.splice(idx, 1);
    } else {
      App.selectedId = item.id;
      saveSnapshot();
      updateSaveBadge(true);
      if (item.type === "text") {
        redraw();
        startLiveInput(item);
        return;
      }
    }
    redraw();
    return;
  }

  if (App.isDragging || App.isResizing) {
    App.isDragging = false;
    App.isResizing = false;
    App.resizeCorner = null;
    App.originBox = null;
    saveSnapshot();
    updateSaveBadge(true);
  }
}

function onDoubleClick(e) {
  const { x, y } = getNormalized(e);
  const items = getPageItems();
  for (let i = items.length - 1; i >= 0; i--) {
    const it = items[i];
    if (x >= it.x && x <= it.x + it.w && y >= it.y && y <= it.y + it.h) {
      App.selectedId = it.id;
      it.type = "text"; // allow adding text to whiteout box instantly
      startLiveInput(it);
      break;
    }
  }
}

function hitCornerHandle(item, rawX, rawY, rectW, rectH) {
  const cornerX = (item.x + item.w) * rectW;
  const cornerY = (item.y + item.h) * rectH;
  if (Math.hypot(rawX - cornerX, rawY - cornerY) <= 12) {
    return "se";
  }
  return null;
}

// Auto-fit box so solid white background GUARANTEES 100% coverage over text
function autoFitItemToText(item) {
  if (!item || !item.text) return;
  const ctx = ui.drawCanvas.getContext("2d");
  const W = ui.drawCanvas.width;
  const H = ui.drawCanvas.height;
  const fontScale = W / 800;
  const fontSizePx = Math.max(8, (item.fontSize || 10) * fontScale);
  ctx.font = `${item.isBold ? 'bold' : 'normal'} ${fontSizePx}px Helvetica, Arial, sans-serif`;

  const lines = item.text.split("\n");
  let maxW = 0;
  lines.forEach(l => {
    const measure = ctx.measureText(l).width;
    if (measure > maxW) maxW = measure;
  });

  const padX = 12 * fontScale;
  const padY = 8 * fontScale;
  const neededW = (maxW + padX * 2) / W;
  const neededH = ((lines.length * fontSizePx * 1.35) + padY * 2) / H;

  // Solid white mask must be at least large enough to cover all text + padding
  item.w = Math.min(1 - item.x, Math.max(item.w || 0, neededW));
  item.h = Math.min(1 - item.y, Math.max(item.h || 0, neededH));
}

// Direct In-Place Text Editing (With Opaque White Mask & Floating Save Button)
function startLiveInput(item) {
  App.editingId = item.id;
  const rect = ui.drawCanvas.getBoundingClientRect();
  const left = item.x * rect.width;
  const top = item.y * rect.height;
  const width = Math.max(120, item.w * rect.width);
  const height = Math.max(36, item.h * rect.height);

  const fontScale = rect.width / 800;
  const fontSizePx = Math.max(10, (item.fontSize || 10) * fontScale);

  ui.liveInput.style.left = `${left}px`;
  ui.liveInput.style.top = `${top}px`;
  ui.liveInput.style.width = `${width}px`;
  ui.liveInput.style.height = `${height}px`;
  ui.liveInput.style.fontSize = `${fontSizePx}px`;
  ui.liveInput.style.fontWeight = item.isBold ? "bold" : "normal";
  ui.liveInput.style.color = item.color || "#0F172A";
  ui.liveInput.style.backgroundColor = "#FFFFFF";
  ui.liveInput.value = item.text || "";
  ui.liveInput.style.display = "block";

  // Position Floating Save / Cancel Bar
  if (ui.liveInputActions) {
    ui.liveInputActions.style.left = `${left}px`;
    ui.liveInputActions.style.top = `${top + height + 6}px`;
    ui.liveInputActions.style.display = "flex";
  }

  ui.liveInput.focus();
  ui.liveInput.select();
  syncFormatBar(item);
  redraw();
}

function commitLiveText() {
  if (!App.editingId) return;
  const items = getPageItems();
  const item = items.find(i => i.id === App.editingId);
  if (item) {
    item.text = ui.liveInput.value.trim();

    // Synchronize box dimensions from live textarea if user resized it
    const rect = ui.drawCanvas.getBoundingClientRect();
    const inputW = ui.liveInput.offsetWidth / rect.width;
    const inputH = ui.liveInput.offsetHeight / rect.height;
    item.w = Math.max(item.w, inputW);
    item.h = Math.max(item.h, inputH);

    // Auto-fit so white mask is GUARANTEED to cover 100% of the text!
    autoFitItemToText(item);
  }
  ui.liveInput.style.display = "none";
  if (ui.liveInputActions) ui.liveInputActions.style.display = "none";
  App.editingId = null;
  saveSnapshot();
  updateSaveBadge(true);
  redraw();
}

function cancelLiveText() {
  ui.liveInput.style.display = "none";
  if (ui.liveInputActions) ui.liveInputActions.style.display = "none";
  App.editingId = null;
  redraw();
}

function deselectAll() {
  if (App.editingId) commitLiveText();
  App.selectedId = null;
  syncFormatBar(null);
  redraw();
}

// Update Header Save Status Badge
function updateSaveBadge(isDirty = true) {
  if (!ui.saveBadge) return;
  let count = 0;
  Object.values(App.annotations).forEach(arr => count += arr.length);
  if (count === 0) {
    ui.saveBadge.className = "badge bg-secondary-subtle text-secondary border border-secondary-subtle doc-save-badge";
    ui.saveBadge.innerHTML = "No Edits";
  } else if (isDirty) {
    ui.saveBadge.className = "badge bg-warning-subtle text-warning-emphasis border border-warning-subtle doc-save-badge";
    ui.saveBadge.innerHTML = `<i class="bi bi-circle-fill me-1 live-action-icon-dot"></i>${count} Unsaved Edit${count > 1 ? 's' : ''}`;
  } else {
    ui.saveBadge.className = "badge bg-success-subtle text-success border border-success-subtle doc-save-badge";
    ui.saveBadge.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i>All Changes Saved`;
  }
}

// =========================================================================
// 4. CANVAS OVERLAY RENDERING (100% SOLID OPAQUE WHITEOUT MASK)
// =========================================================================

function redraw() {
  const ctx = ui.drawCanvas.getContext("2d");
  const W = ui.drawCanvas.width;
  const H = ui.drawCanvas.height;
  ctx.clearRect(0, 0, W, H);

  const items = getPageItems();

  items.forEach(it => {
    const x = it.x * W;
    const y = it.y * H;
    const w = it.w * W;
    const h = it.h * H;
    const isSel = it.id === App.selectedId;

    // 1. Draw 100% OPAQUE SOLID MASK (NO TRANSPARENCY)
    if (it.type === "redact") {
      ctx.fillStyle = "#000000";
      ctx.fillRect(x, y, w, h);
    } else {
      // Solid Whiteout Mask: Completely wipes out the underlying original text!
      ctx.fillStyle = "#FFFFFF";
      ctx.fillRect(x, y, w, h);
    }

    // 2. Draw Replacement Text (Clipped strictly inside the solid white mask)
    if (it.text && it.id !== App.editingId) {
      ctx.save();
      ctx.beginPath();
      ctx.rect(x, y, w, h);
      ctx.clip(); // Guarantees text NEVER bleeds outside the white mask

      const fontScale = W / 800;
      const fontSizePx = Math.max(8, (it.fontSize || 10) * fontScale);
      ctx.font = `${it.isBold ? 'bold' : 'normal'} ${fontSizePx}px Helvetica, Arial, sans-serif`;
      ctx.fillStyle = it.color || "#0F172A";
      ctx.textBaseline = "top";

      const padX = 6 * fontScale;
      const padY = 4 * fontScale;
      const lines = it.text.split("\n");
      const lineH = fontSizePx * 1.25;

      lines.forEach((line, idx) => {
        ctx.fillText(line, x + padX, y + padY + (idx * lineH));
      });

      ctx.restore();
    }

    // 3. Selection outline
    if (isSel) {
      ctx.save();
      ctx.strokeStyle = "#2563EB";
      ctx.lineWidth = 2;
      ctx.strokeRect(x, y, w, h);

      // Bottom-Right resize handle
      ctx.fillStyle = "#2563EB";
      ctx.beginPath();
      ctx.arc(x + w, y + h, 5, 0, Math.PI * 2);
      ctx.fill();
      ctx.strokeStyle = "#FFFFFF";
      ctx.lineWidth = 1.5;
      ctx.stroke();
      ctx.restore();
    } else if (it.type === "whiteout" && !it.text) {
      // Subtle dashed border for empty whiteout so user can see its placement on paper
      ctx.save();
      ctx.strokeStyle = "#94A3B8";
      ctx.setLineDash([4, 4]);
      ctx.lineWidth = 1;
      ctx.strokeRect(x, y, w, h);
      ctx.restore();
    }
  });
}

// =========================================================================
// 5. TOOLBAR, FORMATTING & SHORTCUTS
// =========================================================================

function setTool(toolName) {
  App.tool = toolName;
  ["btnToolSelect", "btnToolWhiteout", "btnToolText", "btnToolRedact"].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.classList.remove("active");
  });

  const activeBtn = {
    select: "btnToolSelect",
    whiteout: "btnToolWhiteout",
    text: "btnToolText",
    redact: "btnToolRedact"
  }[toolName];

  if (activeBtn) document.getElementById(activeBtn).classList.add("active");
  ui.drawCanvas.style.cursor = toolName === "select" ? "default" : "crosshair";
}

function syncFormatBar(item) {
  if (!item) {
    ui.formatBar.classList.remove("show");
    return;
  }
  ui.formatBar.classList.add("show");
  ui.fontSizeDisplay.textContent = `${item.fontSize || 10}pt`;
  if (item.isBold) {
    ui.btnBoldToggle.classList.add("btn-primary");
    ui.btnBoldToggle.classList.remove("btn-light");
  } else {
    ui.btnBoldToggle.classList.remove("btn-primary");
    ui.btnBoldToggle.classList.add("btn-light");
  }
}

function stepFontSize(delta) {
  if (!App.selectedId) return;
  const items = getPageItems();
  const item = items.find(i => i.id === App.selectedId);
  if (!item) return;

  item.fontSize = Math.max(6, Math.min(32, (item.fontSize || 10) + delta));
  autoFitItemToText(item);
  syncFormatBar(item);
  saveSnapshot();
  updateSaveBadge(true);
  redraw();
}

function toggleBold() {
  if (!App.selectedId) return;
  const items = getPageItems();
  const item = items.find(i => i.id === App.selectedId);
  if (!item) return;

  item.isBold = !item.isBold;
  autoFitItemToText(item);
  syncFormatBar(item);
  saveSnapshot();
  updateSaveBadge(true);
  redraw();
}

function fitSelected() {
  if (!App.selectedId) return;
  const items = getPageItems();
  const item = items.find(i => i.id === App.selectedId);
  if (!item) return;

  autoFitItemToText(item);
  saveSnapshot();
  updateSaveBadge(true);
  redraw();
}

function deleteSelected() {
  if (!App.selectedId) return;
  const items = getPageItems();
  const idx = items.findIndex(i => i.id === App.selectedId);
  if (idx !== -1) {
    items.splice(idx, 1);
    App.selectedId = null;
    syncFormatBar(null);
    saveSnapshot();
    updateSaveBadge(true);
    redraw();
  }
}

function setupKeyboardEvents() {
  window.addEventListener("keydown", (e) => {
    const inInput = document.activeElement && (document.activeElement.tagName === "INPUT" || document.activeElement.tagName === "TEXTAREA");
    
    // Delete key
    if ((e.key === "Delete" || e.key === "Backspace") && App.selectedId && !inInput) {
      deleteSelected();
      e.preventDefault();
    }
    // Tool shortcuts
    if (!inInput && !e.ctrlKey && !e.metaKey) {
      if (e.key.toLowerCase() === "v") setTool("select");
      else if (e.key.toLowerCase() === "w") setTool("whiteout");
      else if (e.key.toLowerCase() === "t") setTool("text");
      else if (e.key.toLowerCase() === "r") setTool("redact");
    }
    // Undo / Redo
    if (e.ctrlKey && e.key.toLowerCase() === "z" && !e.shiftKey) {
      undo();
      e.preventDefault();
    }
    if ((e.ctrlKey && e.key.toLowerCase() === "y") || (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === "z")) {
      redo();
      e.preventDefault();
    }
  });
}

// =========================================================================
// 6. MULTI-PAGE & ZOOM CONTROLS
// =========================================================================

async function navPage(offset) {
  const target = App.page + offset;
  if (target >= 1 && target <= App.numPages) {
    App.page = target;
    App.selectedId = null;
    syncFormatBar(null);
    await renderPage();
    updateDock();
  }
}

async function zoomChange(delta) {
  const newScale = Math.max(0.5, Math.min(2.5, App.scale + delta));
  App.scale = Math.round(newScale * 10) / 10;
  await renderPage();
  updateDock();
}

function updateDock() {
  ui.dockPageText.textContent = `Page ${App.page} / ${App.numPages}`;
  ui.dockZoomText.textContent = `${Math.round(App.scale * 100)}%`;
  ui.btnPrev.disabled = App.page <= 1;
  ui.btnNext.disabled = App.page >= App.numPages;
}

// History Snapshots
function saveSnapshot() {
  App.history = App.history.slice(0, App.historyIdx + 1);
  App.history.push(JSON.parse(JSON.stringify(App.annotations)));
  App.historyIdx = App.history.length - 1;
}

function undo() {
  if (App.historyIdx > 0) {
    App.historyIdx--;
    App.annotations = JSON.parse(JSON.stringify(App.history[App.historyIdx]));
    App.selectedId = null;
    syncFormatBar(null);
    updateSaveBadge(true);
    redraw();
  }
}

function redo() {
  if (App.historyIdx < App.history.length - 1) {
    App.historyIdx++;
    App.annotations = JSON.parse(JSON.stringify(App.history[App.historyIdx]));
    App.selectedId = null;
    syncFormatBar(null);
    updateSaveBadge(true);
    redraw();
  }
}

// =========================================================================
// 7. PDF-LIB NATIVE VECTOR EXPORT (SOLID OPAQUE WHITE MASKING)
// =========================================================================

async function downloadPdf() {
  if (!App.rawBytes) return;

  try {
    ui.docStatusLabel.textContent = "Compiling clean PDF...";
    const pdfDoc = await PDFLib.PDFDocument.load(App.rawBytes.slice(0));
    
    // Standard crisp fonts
    const fontHelvetica = await pdfDoc.embedFont(PDFLib.StandardFonts.Helvetica);
    const fontHelveticaBold = await pdfDoc.embedFont(PDFLib.StandardFonts.HelveticaBold);
    const pages = pdfDoc.getPages();

    // Iterate through all document pages
    for (let pNum = 1; pNum <= pages.length; pNum++) {
      const items = App.annotations[pNum] || [];
      if (items.length === 0) continue;

      const page = pages[pNum - 1];
      const { width: pdfW, height: pdfH } = page.getSize();

      items.forEach(it => {
        const chosenFont = it.isBold ? fontHelveticaBold : fontHelvetica;
        const fontSize = Math.max(6, it.fontSize || 10);
        let boxW = it.w * pdfW;
        let boxH = it.h * pdfH;

        // Auto-fit check: ensure the white mask completely covers all text + padding
        if (it.text) {
          const lines = it.text.split("\n");
          let maxTextW = 0;
          lines.forEach(line => {
            try {
              const tw = chosenFont.widthOfTextAtSize(line, fontSize);
              if (tw > maxTextW) maxTextW = tw;
            } catch (e) {
              const tw = line.length * (fontSize * 0.55);
              if (tw > maxTextW) maxTextW = tw;
            }
          });
          const neededW = maxTextW + 12; // 6pt padding on each side
          const neededH = (lines.length * fontSize * 1.3) + 8;
          boxW = Math.max(boxW, neededW);
          boxH = Math.max(boxH, neededH);
        }

        const x = it.x * pdfW;
        // Invert Y coordinate for PDF coordinate space
        const y = pdfH - (it.y * pdfH) - boxH;

        // 1. Draw 100% SOLID OPAQUE MASK RECTANGLE (NO TRANSPARENCY!)
        page.drawRectangle({
          x: x,
          y: y,
          width: boxW,
          height: boxH,
          color: it.type === "redact" ? PDFLib.rgb(0, 0, 0) : PDFLib.rgb(1, 1, 1),
          borderWidth: 0,
          opacity: 1.0 // Strictly opaque
        });

        // 2. Draw Text on top of the solid white rectangle
        if (it.text) {
          const lines = it.text.split("\n");
          const lineStep = fontSize * 1.25;
          let textY = y + boxH - fontSize - 4; // Top of the white box downwards

          lines.forEach(line => {
            if (line.trim().length > 0) {
              try {
                page.drawText(line, {
                  x: x + 6,
                  y: textY,
                  size: fontSize,
                  font: chosenFont,
                  color: PDFLib.rgb(0.06, 0.09, 0.16)
                });
              } catch (err) {
                console.warn("Could not draw text:", err);
              }
            }
            textY -= lineStep;
          });
        }
      });
    }

    const modified = await pdfDoc.save();
    const blob = new Blob([modified], { type: "application/pdf" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = App.fileName.replace(/\.pdf$/i, "") + "_CLEAN.pdf";
    document.body.appendChild(a);
    a.click();

    setTimeout(() => {
      a.remove();
      URL.revokeObjectURL(url);
      ui.docStatusLabel.textContent = "Downloaded successfully";
      updateSaveBadge(false);
    }, 1000);

  } catch (err) {
    console.error("Download error:", err);
    alert("Could not export PDF: " + err.message);
    ui.docStatusLabel.textContent = "Export failed";
  }
}

// =========================================================================
// 8. CONSIGNERS INTEGRATION FOR PDF STAMPING & AMENDMENTS
// =========================================================================

function escapeHtml(str) {
  if (!str) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function getRegisteredConsigners() {
  const DEFAULT_CONSIGNERS = [
    {
      id: "EXP-001",
      name: "Reliance Granito Private Limited",
      iec: "ABEFR3842G",
      iecBranch: "0",
      gstin: "08ABEFR3842G1Z5",
      adCode: "6390013",
      forexBank: "ICICI Bank Limited, Jodhpur Branch (A/C: ICIC0006827)",
      registeredAddress: "Sector 23, Chopasni Housing Board, Jodhpur, Rajasthan - 342008, India",
      factoryAddress: "National Highway 8-A, Old Ghuntu Road, Morbi - 363642, Gujarat, India",
      region: "Morbi",
      status: "Active"
    },
    {
      id: "EXP-002",
      name: "Reliance Granito (Morbi Plant II)",
      iec: "ABEFR3842G",
      iecBranch: "1",
      gstin: "24ABEFR3842G1Z2",
      adCode: "6390013",
      forexBank: "State Bank of India, Morbi Main Branch (A/C: 30894102941)",
      registeredAddress: "Sector 23, Chopasni Housing Board, Jodhpur, Rajasthan - 342008, India",
      factoryAddress: "Survey No. 142/P, Lakhdhirpur Road, Morbi - 363642, Gujarat, India",
      region: "Morbi",
      status: "Active"
    },
    {
      id: "EXP-003",
      name: "Reliance Ceramic Industries LLP",
      iec: "AABFR9182K",
      iecBranch: "0",
      gstin: "24AABFR9182K1Z9",
      adCode: "6391104",
      forexBank: "HDFC Bank Limited, Morbi Branch",
      registeredAddress: "GIDC Industrial Estate, Phase II, Morbi - 363641, Gujarat, India",
      factoryAddress: "GIDC Industrial Estate, Phase II, Morbi - 363641, Gujarat, India",
      region: "Morbi",
      status: "Active"
    },
    {
      id: "EXP-004",
      name: "Marwar Granite & Quartz Export LLP",
      iec: "AARFM7721H",
      iecBranch: "0",
      gstin: "08AARFM7721H1Z1",
      adCode: "6389920",
      forexBank: "Axis Bank Limited, Mandore Road, Jodhpur",
      registeredAddress: "Plot 45-B, Mandore Industrial Area, Jodhpur - 342007, Rajasthan",
      factoryAddress: "RIICO Industrial Area, Phase II, Boranada, Jodhpur - 342012, Rajasthan",
      region: "Jodhpur",
      status: "Active"
    }
  ];

  try {
    const raw = localStorage.getItem("reliance_edms_consigners");
    if (raw) {
      const parsed = JSON.parse(raw);
      if (Array.isArray(parsed) && parsed.length > 0) return parsed;
    }
  } catch (e) {
    console.warn("Could not read consigners from localStorage:", e);
  }
  return DEFAULT_CONSIGNERS;
}

function initConsignersDropdown() {
  const menu = document.getElementById("consignerDropdownMenu");
  if (!menu) return;

  const consigners = getRegisteredConsigners();
  const activeConsigners = consigners.filter(c => c.status !== "Inactive");

  const itemsHtml = activeConsigners.map(c => `
    <li>
      <a class="dropdown-item py-2 px-3 d-flex align-items-center justify-content-between consigner-item-link" href="#" onclick="applyConsignerStamp('${escapeHtml(c.id)}'); return false;">
        <div class="overflow-hidden pe-3">
          <div class="fw-bold text-dark consigner-name-truncate">${escapeHtml(c.name)}</div>
          <div class="text-muted consigner-meta-text">IEC: ${escapeHtml(c.iec)} • ${escapeHtml(c.region || 'Morbi')}</div>
        </div>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle consigner-apply-pill">Apply</span>
      </a>
    </li>
  `).join("");

  menu.innerHTML = `
    <li><h6 class="dropdown-header text-uppercase">Stamp Registered Consigner</h6></li>
    ${itemsHtml}
    <li><hr class="dropdown-divider"></li>
    <li>
      <a class="dropdown-item d-flex align-items-center gap-2 text-primary fw-semibold py-2 px-3" href="consigners.php">
        <i class="bi bi-gear"></i> Manage Consigners Directory &rarr;
      </a>
    </li>
  `;

  // Also check if opened from consigners.php via sessionStorage
  try {
    const raw = sessionStorage.getItem("reliance_stamp_consigner");
    if (raw) {
      const parsed = JSON.parse(raw);
      sessionStorage.removeItem("reliance_stamp_consigner");
      if (parsed && parsed.id) {
        setTimeout(() => {
          applyConsignerStamp(parsed.id);
        }, 800);
      }
    }
  } catch (e) {
    console.warn("Session consigner check:", e);
  }
}

function applyConsignerStamp(consignerId) {
  if (!App.pdfDoc) {
    alert("Please upload an export PDF document first before stamping.");
    return;
  }

  const stampLines = [
    c.name.toUpperCase(),
    c.factoryAddress || c.registeredAddress,
    `IEC: ${c.iec} (BRANCH: ${c.iecBranch || '0'}) | GSTIN: ${c.gstin} | AD CODE: ${c.adCode || '6390013'}`,
    `BANK: ${c.forexBank || 'ICICI BANK LIMITED, JODHPUR / MORBI BRANCH'}`
  ];

  const text = stampLines.join("\n");
  const items = getPageItems();

  // If user has an existing text item selected, replace its content with the consigner details!
  if (App.selectedId) {
    const selected = items.find(it => it.id === App.selectedId);
    if (selected && selected.type === "text") {
      selected.text = text;
      selected.backgroundColor = "#FFFFFF";
      autoFitItemToText(selected);
      syncFormatBar(selected);
      saveSnapshot();
      updateSaveBadge(true);
      redraw();
      ui.docStatusLabel.textContent = `Applied ${c.name} to selected box`;
      return;
    }
  }

  // Create clean Consigner overlay box with solid opaque white mask at exporter position
  const newAnnotation = {
    id: "ann_consigner_" + Date.now(),
    type: "text",
    x: 0.05,
    y: 0.08,
    w: 0.88,
    h: 0.09,
    text: text,
    fontSize: 8.5,
    isBold: true,
    color: "#0F172A",
    backgroundColor: "#FFFFFF"
  };

  autoFitItemToText(newAnnotation);
  items.push(newAnnotation);
  App.selectedId = newAnnotation.id;
  syncFormatBar(newAnnotation);
  saveSnapshot();
  updateSaveBadge(true);
  redraw();
  ui.docStatusLabel.textContent = `Stamped ${c.name}`;
}
</script>

<?php
include_once __DIR__ . '/includes/footer.php';
?>
