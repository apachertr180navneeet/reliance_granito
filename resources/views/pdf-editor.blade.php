@extends('layouts.app')

@section('body-class', 'pdf-editor-active')

@section('content')
<div class="editor-app">
  <!-- Top Navigation & Controls Header -->
  <header class="editor-header">
    <!-- Left: Document Identity & Switcher -->
    <div class="doc-info">
      <div class="doc-icon-badge">
        <i class="bi bi-file-earmark-pdf fs-5"></i>
      </div>
      <div class="doc-meta-block">
        <div class="d-flex align-items-center gap-2">
          <!-- Document Switcher Dropdown (MySQL Backed) -->
          <div class="dropdown">
            <button class="btn btn-sm btn-dark text-white dropdown-toggle doc-pill d-flex align-items-center gap-1 font-monospace" type="button" data-bs-toggle="dropdown" id="docNameLabel">
              {{ $selectedDoc->name ?? 'Select Export PDF' }}
            </button>
            <ul class="dropdown-menu shadow-lg" style="min-width: 320px;">
              <li><h6 class="dropdown-header text-uppercase">Sample Export Documents (MySQL)</h6></li>
              @foreach($documents as $doc)
              <li>
                <a class="dropdown-item d-flex align-items-center justify-content-between py-2 {{ ($selectedDoc && $selectedDoc->id === $doc->id) ? 'active' : '' }}" 
                   href="{{ route('pdf-editor.index', ['doc' => $doc->name]) }}">
                  <div>
                    <div class="fw-semibold text-truncate" style="max-width: 200px;">{{ $doc->title }}</div>
                    <div class="small opacity-75 font-monospace">{{ $doc->name }}</div>
                  </div>
                  <span class="badge bg-secondary-subtle text-secondary small">{{ number_format($doc->file_size / 1024, 0) }}KB</span>
                </a>
              </li>
              @endforeach
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item d-flex align-items-center gap-2 text-primary" href="javascript:void(0)" onclick="document.getElementById('fileInput').click()">
                  <i class="bi bi-upload"></i> Upload Custom PDF File...
                </a>
              </li>
            </ul>
          </div>

          <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle doc-save-badge" id="saveBadge">Clean</span>
        </div>
        <div class="doc-status-text" id="docStatusLabel">Loading authentic document from MySQL...</div>
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

      <!-- Consigner Stamping Menu (MySQL Backed) -->
      <div class="dropdown">
        <button class="btn btn-consigner-picker dropdown-toggle" data-bs-toggle="dropdown" id="btnConsignerDropdown" title="Stamp Registered Consigner Particulars from MySQL">
          <i class="bi bi-building-up text-primary"></i>
          <span>Consigner</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end consigner-menu-popover shadow-lg" id="consignerDropdownMenu">
          <li>
            <div class="dropdown-header text-uppercase d-flex align-items-center justify-content-between">
              <span>Stamp Consigner (MySQL)</span>
              <span class="badge consigner-hint-pill">Drag & Drop or Click</span>
            </div>
          </li>
          @forelse($consigners as $c)
          <li>
            <div class="dropdown-item py-2 px-3 d-flex align-items-center justify-content-between consigner-item-link"
                 draggable="true"
                 data-consigner-id="{{ $c->id }}"
                 data-consigner-name="{{ $c->name }}"
                 data-consigner-iec="{{ $c->iec }}"
                 data-consigner-region="{{ $c->region }}"
                 title="Click to stamp or drag & drop directly onto the PDF!">
              <div class="overflow-hidden pe-3 consigner-drag-target" onclick="applyConsignerStamp({{ $c->id }}); return false;">
                <div class="fw-bold text-dark consigner-name-truncate">
                  <i class="bi bi-grip-vertical text-muted me-1"></i>{{ $c->name }}
                </div>
                <div class="text-muted consigner-meta-text">IEC: {{ $c->iec }} &bull; {{ $c->region }} Plant</div>
              </div>
              <button type="button" class="btn btn-sm btn-primary-subtle text-primary border border-primary-subtle consigner-apply-pill px-2 py-1" onclick="applyConsignerStamp({{ $c->id }}); return false;">
                Apply
              </button>
            </div>
          </li>
          @empty
          <li><span class="dropdown-item small text-muted">No active consigners found.</span></li>
          @endforelse
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item d-flex align-items-center gap-2 text-primary fw-semibold py-2 px-3" href="{{ route('consigners.index') }}">
              <i class="bi bi-gear"></i> Manage Consigners Directory &rarr;
            </a>
          </li>
        </ul>
      </div>

      <!-- Contextual Formatting Bar -->
      <div class="format-bar" id="formatBar">
        <button type="button" class="btn btn-sm btn-light border py-0 px-2 fw-semibold" id="btnToggleBackground" onclick="toggleBackgroundMask()" title="Toggle Transparent vs Solid White Mask">
          <i class="bi bi-circle me-1 text-primary" id="iconBackgroundMask"></i><span id="labelBackgroundMask">Transparent</span>
        </button>
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

      <!-- Save & Export Clean PDF Button -->
      <button class="btn btn-header-export" id="btnDownload" onclick="downloadPdf()" disabled title="Save edits to MySQL and download vector PDF">
        <i class="bi bi-floppy2-fill"></i>
        <span>Save / Export PDF</span>
      </button>
    </div>
  </header>

  <!-- Main Viewport / Desk -->
  <main class="editor-viewport" id="viewport">
    
    <!-- Modern Elegant Dropzone Empty State -->
    <div class="empty-hero" id="emptyHero" style="display:none;">
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
@endsection

@push('scripts')
<!-- Essential PDF Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
  if (typeof pdfjsLib !== 'undefined') {
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
  }
</script>
<script src="https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>

<script>
/**
 * Reliance Granito EDMS - Interactive Visual PDF Editor & Masker
 * Integrated with Laravel & MySQL Database Backend
 */

const MYSQL_CONSIGNERS = @json($consigners);
const INITIAL_DOC_NAME = "{{ $selectedDoc->name ?? '1469 SB ORIGINAL.PDF' }}";
const INITIAL_DOC_URL = "{{ $selectedDoc ? route('documents.stream', ['filename' => $selectedDoc->name]) : '' }}";

const App = {
  pdfDoc: null,
  rawBytes: null,
  fileName: INITIAL_DOC_NAME,
  numPages: 0,
  page: 1,
  scale: 1.0,
  tool: "select",

  annotations: {},
  selectedId: null,
  activeItem: null,
  editingId: null,

  isDrawing: false,
  isDragging: false,
  isResizing: false,
  resizeCorner: null,
  startX: 0,
  startY: 0,
  originBox: null,

  history: [],
  historyIdx: -1
};

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
  btnBoldToggle: document.getElementById("btnBoldToggle"),
  viewport: document.getElementById("viewport")
};

document.addEventListener("DOMContentLoaded", async () => {
  setupCanvasEvents();
  setupKeyboardEvents();
  initConsignersDragListeners();

  // Load initial document from MySQL catalog
  if (INITIAL_DOC_URL) {
    ui.docStatusLabel.textContent = "Streaming " + INITIAL_DOC_NAME + " from MySQL...";
    try {
      const response = await fetch(INITIAL_DOC_URL);
      if (response.ok) {
        const buffer = await response.arrayBuffer();
        await loadPdfBytes(buffer, INITIAL_DOC_NAME);
        await loadSavedEditsFromDatabase(INITIAL_DOC_NAME);
      } else {
        ui.emptyHero.style.display = "flex";
      }
    } catch (e) {
      console.warn("Could not load initial document:", e);
      ui.emptyHero.style.display = "flex";
    }
  } else {
    ui.emptyHero.style.display = "flex";
  }
});

function initConsignersDragListeners() {
  const menu = document.getElementById("consignerDropdownMenu");
  if (!menu) return;

  menu.querySelectorAll(".consigner-item-link").forEach(el => {
    el.addEventListener("dragstart", (e) => {
      const id = el.getAttribute("data-consigner-id");
      if (id) {
        e.dataTransfer.setData("text/plain", id);
        e.dataTransfer.setData("application/x-consigner-id", id);
        e.dataTransfer.effectAllowed = "copy";

        const dropdownBtn = document.getElementById("btnConsignerDropdown");
        if (dropdownBtn) {
          const bsDrop = bootstrap.Dropdown.getInstance(dropdownBtn);
          if (bsDrop) bsDrop.hide();
        }
      }
    });
  });

  // Check if opened from consigners master via sessionStorage
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
  } catch (e) {}
}

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
  ui.docStatusLabel.textContent = "Parsing PDF...";

  const task = pdfjsLib.getDocument({ data: arrayBuffer });
  App.pdfDoc = await task.promise;
  App.numPages = App.pdfDoc.numPages;
  App.page = 1;

  if (window.innerWidth < 768) {
    App.scale = Math.max(0.45, Math.min(1.0, (window.innerWidth - 32) / 600));
  } else {
    App.scale = 1.0;
  }

  ui.emptyHero.style.display = "none";
  ui.paperSheet.style.display = "block";
  ui.floatingDock.style.display = "flex";
  ui.btnDownload.disabled = false;
  ui.docStatusLabel.textContent = `${App.numPages} Page${App.numPages > 1 ? 's' : ''} &bull; Ready for Masking`;

  saveSnapshot();
  updateSaveBadge(false);
  await renderPage();
  updateDock();
}

async function renderPage() {
  if (!App.pdfDoc) return;
  const page = await App.pdfDoc.getPage(App.page);
  
  const pixelRatio = window.devicePixelRatio || 1.5;
  const viewport = page.getViewport({ scale: App.scale * pixelRatio });
  const unscaled = page.getViewport({ scale: 1.0 });

  const displayWidth = unscaled.width * App.scale;
  const displayHeight = unscaled.height * App.scale;

  ui.pdfCanvas.width = viewport.width;
  ui.pdfCanvas.height = viewport.height;
  ui.drawCanvas.width = viewport.width;
  ui.drawCanvas.height = viewport.height;

  ui.pdfCanvas.style.width = `${displayWidth}px`;
  ui.pdfCanvas.style.height = `${displayHeight}px`;
  ui.drawCanvas.style.width = `${displayWidth}px`;
  ui.drawCanvas.style.height = `${displayHeight}px`;
  ui.paperSheet.style.width = `${displayWidth}px`;
  ui.paperSheet.style.height = `${displayHeight}px`;

  const ctx = ui.pdfCanvas.getContext("2d");
  ctx.save();
  ctx.fillStyle = "#FFFFFF";
  ctx.fillRect(0, 0, viewport.width, viewport.height);
  ctx.restore();

  await page.render({
    canvasContext: ctx,
    viewport: viewport
  }).promise;

  redraw();
}

function redraw() {
  const ctx = ui.drawCanvas.getContext("2d");
  const w = ui.drawCanvas.width;
  const h = ui.drawCanvas.height;

  ctx.clearRect(0, 0, w, h);

  const items = getPageItems();

  items.forEach(item => {
    const rx = item.x * w;
    const ry = item.y * h;
    const rw = item.w * w;
    const rh = item.h * h;

    ctx.save();

    if (item.type === "whiteout") {
      ctx.fillStyle = "#FFFFFF";
      ctx.fillRect(rx, ry, rw, rh);
    } else if (item.type === "redact") {
      ctx.fillStyle = "#000000";
      ctx.fillRect(rx, ry, rw, rh);
    } else if (item.type === "text") {
      if (item.backgroundColor !== "transparent" && !item.isTransparent) {
        ctx.fillStyle = "#FFFFFF";
        ctx.fillRect(rx, ry, rw, rh);
      }

      ctx.beginPath();
      ctx.rect(rx, ry, rw, rh);
      ctx.clip();

      const fontSize = (item.fontSize || 10) * App.scale * (window.devicePixelRatio || 1.5);
      const isBold = item.isBold ? "bold " : "";
      ctx.font = `${isBold}${fontSize}px 'Helvetica Neue', Helvetica, Arial, sans-serif`;
      ctx.fillStyle = item.color || "#0F172A";
      ctx.textBaseline = "top";

      const lines = (item.text || "").split("\n");
      const lineHeight = fontSize * 1.25;
      const padX = 6 * App.scale * (window.devicePixelRatio || 1.5);
      const padY = 4 * App.scale * (window.devicePixelRatio || 1.5);

      lines.forEach((line, idx) => {
        ctx.fillText(line, rx + padX, ry + padY + (idx * lineHeight));
      });
    }

    ctx.restore();

    if (item.id === App.selectedId && App.tool === "select") {
      drawSelectionBox(ctx, rx, ry, rw, rh);
    }
  });
}

function drawSelectionBox(ctx, rx, ry, rw, rh) {
  ctx.save();
  ctx.strokeStyle = "#2563EB";
  ctx.lineWidth = 1.5 * (window.devicePixelRatio || 1.5);
  ctx.setLineDash([4 * (window.devicePixelRatio || 1.5), 3 * (window.devicePixelRatio || 1.5)]);
  ctx.strokeRect(rx - 1, ry - 1, rw + 2, rh + 2);
  ctx.setLineDash([]);

  const handleSize = 8 * (window.devicePixelRatio || 1.5);
  ctx.fillStyle = "#2563EB";
  ctx.fillRect(rx + rw - handleSize / 2, ry + rh - handleSize / 2, handleSize, handleSize);
  ctx.strokeStyle = "#FFFFFF";
  ctx.lineWidth = 1.5;
  ctx.strokeRect(rx + rw - handleSize / 2, ry + rh - handleSize / 2, handleSize, handleSize);
  ctx.restore();
}

function getPageItems() {
  if (!App.annotations[App.page]) {
    App.annotations[App.page] = [];
  }
  return App.annotations[App.page];
}

function setTool(toolName) {
  App.tool = toolName;
  document.querySelectorAll(".tool-btn").forEach(btn => btn.classList.remove("active"));
  
  const idMap = {
    select: "btnToolSelect",
    whiteout: "btnToolWhiteout",
    text: "btnToolText",
    redact: "btnToolRedact"
  };
  const activeBtn = document.getElementById(idMap[toolName]);
  if (activeBtn) activeBtn.classList.add("active");

  if (toolName !== "select") {
    deselectAll();
  }

  const canvas = ui.drawCanvas;
  if (toolName === "select") {
    canvas.style.cursor = "default";
  } else {
    canvas.style.cursor = "crosshair";
  }
}

function autoFitItemToText(item, generous = false) {
  if (!item || item.type !== "text") return;
  const ctx = ui.drawCanvas.getContext("2d");
  const w = ui.drawCanvas.width;
  const h = ui.drawCanvas.height;

  const fontSize = (item.fontSize || 10) * App.scale * (window.devicePixelRatio || 1.5);
  const isBold = item.isBold ? "bold " : "";
  ctx.font = `${isBold}${fontSize}px 'Helvetica Neue', Helvetica, Arial, sans-serif`;

  const lines = (item.text || "").split("\n");
  let maxLineWidth = 0;
  lines.forEach(line => {
    const metrics = ctx.measureText(line);
    if (metrics.width > maxLineWidth) maxLineWidth = metrics.width;
  });

  const lineHeight = fontSize * 1.25;
  const padX = (generous ? 12 : 8) * App.scale * (window.devicePixelRatio || 1.5);
  const padY = (generous ? 8 : 4) * App.scale * (window.devicePixelRatio || 1.5);

  const calcWidth = Math.max(30, maxLineWidth + padX * 2);
  const calcHeight = Math.max(18, (lines.length * lineHeight) + padY * 2);

  item.w = calcWidth / w;
  item.h = calcHeight / h;
}

function fitSelected() {
  const items = getPageItems();
  const selected = items.find(it => it.id === App.selectedId);
  if (selected && selected.type === "text") {
    autoFitItemToText(selected, true);
    saveSnapshot();
    updateSaveBadge(true);
    redraw();
    showToast("Whiteout box tightly fitted to text bounds", "info");
  }
}

function toggleBackgroundMask() {
  const items = getPageItems();
  const selected = items.find(it => it.id === App.selectedId);
  if (!selected || selected.type !== "text") return;

  if (selected.isTransparent) {
    selected.isTransparent = false;
    selected.backgroundColor = "#FFFFFF";
    document.getElementById("labelBackgroundMask").textContent = "White Mask";
    document.getElementById("iconBackgroundMask").className = "bi bi-check-circle-fill me-1 text-success";
    showToast("Background switched to 100% Solid White Mask", "info");
  } else {
    selected.isTransparent = true;
    selected.backgroundColor = "transparent";
    document.getElementById("labelBackgroundMask").textContent = "Transparent";
    document.getElementById("iconBackgroundMask").className = "bi bi-circle me-1 text-primary";
    showToast("Background switched to Transparent", "info");
  }

  saveSnapshot();
  updateSaveBadge(true);
  redraw();
}

function syncFormatBar(item) {
  if (!item || item.type !== "text") {
    ui.formatBar.classList.remove("active");
    return;
  }
  ui.formatBar.classList.add("active");
  ui.fontSizeDisplay.textContent = `${item.fontSize || 10}pt`;
  if (item.isBold) {
    ui.btnBoldToggle.classList.add("btn-primary");
    ui.btnBoldToggle.classList.remove("btn-light");
  } else {
    ui.btnBoldToggle.classList.remove("btn-primary");
    ui.btnBoldToggle.classList.add("btn-light");
  }

  const labelBg = document.getElementById("labelBackgroundMask");
  const iconBg = document.getElementById("iconBackgroundMask");
  if (item.isTransparent) {
    labelBg.textContent = "Transparent";
    iconBg.className = "bi bi-circle me-1 text-primary";
  } else {
    labelBg.textContent = "White Mask";
    iconBg.className = "bi bi-check-circle-fill me-1 text-success";
  }
}

function stepFontSize(delta) {
  const items = getPageItems();
  const selected = items.find(it => it.id === App.selectedId);
  if (selected && selected.type === "text") {
    const cur = selected.fontSize || 10;
    selected.fontSize = Math.max(6, Math.min(48, cur + delta));
    syncFormatBar(selected);
    autoFitItemToText(selected, false);
    saveSnapshot();
    updateSaveBadge(true);
    redraw();
  }
}

function toggleBold() {
  const items = getPageItems();
  const selected = items.find(it => it.id === App.selectedId);
  if (selected && selected.type === "text") {
    selected.isBold = !selected.isBold;
    syncFormatBar(selected);
    autoFitItemToText(selected, false);
    saveSnapshot();
    updateSaveBadge(true);
    redraw();
  }
}

function deleteSelected() {
  if (!App.selectedId) return;
  const items = getPageItems();
  const idx = items.findIndex(it => it.id === App.selectedId);
  if (idx !== -1) {
    items.splice(idx, 1);
    deselectAll();
    saveSnapshot();
    updateSaveBadge(true);
    redraw();
  }
}

function deselectAll() {
  App.selectedId = null;
  ui.formatBar.classList.remove("active");
  closeLiveInput();
  redraw();
}

function setupCanvasEvents() {
  const c = ui.drawCanvas;

  c.addEventListener("mousedown", onMouseDown);
  window.addEventListener("mousemove", onMouseMove);
  window.addEventListener("mouseup", onMouseUp);
  c.addEventListener("dblclick", onDoubleClick);

  // HTML5 Drag and drop consigner onto canvas
  c.addEventListener("dragover", (e) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = "copy";
  });

  c.addEventListener("drop", (e) => {
    e.preventDefault();
    const consignerId = e.dataTransfer.getData("application/x-consigner-id") || e.dataTransfer.getData("text/plain");
    if (!consignerId) return;

    const rect = c.getBoundingClientRect();
    const normX = (e.clientX - rect.left) / rect.width;
    const normY = (e.clientY - rect.top) / rect.height;

    applyConsignerStamp(consignerId, normX, normY);
  });
}

function getNormalizedCoords(e) {
  const rect = ui.drawCanvas.getBoundingClientRect();
  return {
    x: Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width)),
    y: Math.max(0, Math.min(1, (e.clientY - rect.top) / rect.height))
  };
}

function hitTest(normX, normY) {
  const items = getPageItems();
  for (let i = items.length - 1; i >= 0; i--) {
    const it = items[i];
    if (normX >= it.x && normX <= it.x + it.w && normY >= it.y && normY <= it.y + it.h) {
      return it;
    }
  }
  return null;
}

function hitTestHandle(item, normX, normY) {
  const w = ui.drawCanvas.width;
  const h = ui.drawCanvas.height;
  const handleSizeNorm = (12 * (window.devicePixelRatio || 1.5)) / Math.min(w, h);
  const handleX = item.x + item.w;
  const handleY = item.y + item.h;

  if (Math.abs(normX - handleX) <= handleSizeNorm && Math.abs(normY - handleY) <= handleSizeNorm) {
    return "se";
  }
  return null;
}

function onMouseDown(e) {
  if (!App.pdfDoc) return;
  const { x, y } = getNormalizedCoords(e);

  if (App.tool === "select") {
    const items = getPageItems();
    const curSelected = items.find(it => it.id === App.selectedId);

    if (curSelected) {
      const handle = hitTestHandle(curSelected, x, y);
      if (handle) {
        App.isResizing = true;
        App.resizeCorner = handle;
        App.startX = x;
        App.startY = y;
        App.originBox = { x: curSelected.x, y: curSelected.y, w: curSelected.w, h: curSelected.h };
        return;
      }
    }

    const hit = hitTest(x, y);
    if (hit) {
      App.selectedId = hit.id;
      App.isDragging = true;
      App.startX = x;
      App.startY = y;
      App.originBox = { x: hit.x, y: hit.y, w: hit.w, h: hit.h };
      syncFormatBar(hit);
      redraw();
    } else {
      deselectAll();
    }
  } else if (App.tool === "whiteout" || App.tool === "text" || App.tool === "redact") {
    App.isDrawing = true;
    App.startX = x;
    App.startY = y;

    const newItem = {
      id: "ann_" + Date.now(),
      type: App.tool,
      x: x,
      y: y,
      w: 0.001,
      h: 0.001,
      text: App.tool === "text" ? "Type replacement here" : "",
      fontSize: 10,
      isBold: false,
      color: "#0F172A",
      backgroundColor: "#FFFFFF",
      isTransparent: false
    };

    getPageItems().push(newItem);
    App.selectedId = newItem.id;
    App.activeItem = newItem;
  }
}

function onMouseMove(e) {
  if (!App.pdfDoc) return;
  const { x, y } = getNormalizedCoords(e);

  if (App.isDrawing && App.activeItem) {
    const it = App.activeItem;
    it.x = Math.min(App.startX, x);
    it.y = Math.min(App.startY, y);
    it.w = Math.max(0.01, Math.abs(x - App.startX));
    it.h = Math.max(0.01, Math.abs(y - App.startY));
    redraw();
  } else if (App.isDragging && App.selectedId) {
    const items = getPageItems();
    const it = items.find(i => i.id === App.selectedId);
    if (it && App.originBox) {
      const dx = x - App.startX;
      const dy = y - App.startY;
      it.x = Math.max(0, Math.min(1 - it.w, App.originBox.x + dx));
      it.y = Math.max(0, Math.min(1 - it.h, App.originBox.y + dy));
      redraw();
    }
  } else if (App.isResizing && App.selectedId) {
    const items = getPageItems();
    const it = items.find(i => i.id === App.selectedId);
    if (it && App.originBox) {
      const dx = x - App.startX;
      const dy = y - App.startY;
      it.w = Math.max(0.01, App.originBox.w + dx);
      it.h = Math.max(0.01, App.originBox.h + dy);
      redraw();
    }
  }
}

function onMouseUp() {
  if (App.isDrawing) {
    App.isDrawing = false;
    if (App.activeItem) {
      if (App.activeItem.type === "text") {
        setTool("select");
        syncFormatBar(App.activeItem);
        openLiveInput(App.activeItem);
      } else {
        setTool("select");
      }
      App.activeItem = null;
      saveSnapshot();
      updateSaveBadge(true);
      redraw();
    }
  }

  if (App.isDragging || App.isResizing) {
    App.isDragging = false;
    App.isResizing = false;
    App.originBox = null;
    saveSnapshot();
    updateSaveBadge(true);
    redraw();
  }
}

function onDoubleClick(e) {
  if (!App.pdfDoc) return;
  const { x, y } = getNormalizedCoords(e);
  const hit = hitTest(x, y);
  if (hit && hit.type === "text") {
    openLiveInput(hit);
  }
}

function openLiveInput(item) {
  App.editingId = item.id;
  const unscaled = ui.drawCanvas.getBoundingClientRect();
  const rx = item.x * unscaled.width;
  const ry = item.y * unscaled.height;
  const rw = Math.max(120, item.w * unscaled.width);
  const rh = Math.max(34, item.h * unscaled.height);

  const inp = ui.liveInput;
  inp.value = item.text || "";
  inp.style.display = "block";
  inp.style.left = `${rx}px`;
  inp.style.top = `${ry}px`;
  inp.style.width = `${rw}px`;
  inp.style.height = `${rh}px`;

  const fontSize = (item.fontSize || 10) * App.scale;
  inp.style.fontSize = `${fontSize}px`;
  inp.style.fontWeight = item.isBold ? "700" : "400";
  inp.style.color = item.color || "#0F172A";

  const actions = ui.liveInputActions;
  actions.style.display = "flex";
  actions.style.left = `${rx}px`;
  actions.style.top = `${ry + rh + 4}px`;

  inp.focus();
  inp.select();

  inp.onkeydown = (e) => {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      commitLiveText();
    } else if (e.key === "Escape") {
      cancelLiveText();
    }
  };
}

function commitLiveText() {
  if (!App.editingId) return;
  const items = getPageItems();
  const it = items.find(i => i.id === App.editingId);
  if (it) {
    it.text = ui.liveInput.value.trim();
    autoFitItemToText(it, true);
    saveSnapshot();
    updateSaveBadge(true);
  }
  closeLiveInput();
  redraw();
}

function cancelLiveText() {
  closeLiveInput();
  redraw();
}

function closeLiveInput() {
  App.editingId = null;
  ui.liveInput.style.display = "none";
  ui.liveInputActions.style.display = "none";
}

function setupKeyboardEvents() {
  window.addEventListener("keydown", (e) => {
    if (e.target.tagName === "INPUT" || e.target.tagName === "TEXTAREA") return;

    if (e.ctrlKey || e.metaKey) {
      if (e.key === "z" || e.key === "Z") {
        e.preventDefault();
        if (e.shiftKey) redo(); else undo();
      } else if (e.key === "y" || e.key === "Y") {
        e.preventDefault();
        redo();
      } else if (e.key === "s" || e.key === "S") {
        e.preventDefault();
        downloadPdf();
      }
    } else if (e.key === "Delete" || e.key === "Backspace") {
      if (App.selectedId) {
        e.preventDefault();
        deleteSelected();
      }
    } else if (e.key === "v" || e.key === "V") {
      setTool("select");
    } else if (e.key === "w" || e.key === "W") {
      setTool("whiteout");
    } else if (e.key === "t" || e.key === "T") {
      setTool("text");
    } else if (e.key === "r" || e.key === "R") {
      setTool("redact");
    }
  });
}

function saveSnapshot() {
  App.history = App.history.slice(0, App.historyIdx + 1);
  const copy = JSON.parse(JSON.stringify(App.annotations));
  App.history.push(copy);
  if (App.history.length > 30) App.history.shift();
  App.historyIdx = App.history.length - 1;
}

function undo() {
  if (App.historyIdx > 0) {
    App.historyIdx--;
    App.annotations = JSON.parse(JSON.stringify(App.history[App.historyIdx]));
    deselectAll();
    redraw();
    updateSaveBadge(true);
  }
}

function redo() {
  if (App.historyIdx < App.history.length - 1) {
    App.historyIdx++;
    App.annotations = JSON.parse(JSON.stringify(App.history[App.historyIdx]));
    deselectAll();
    redraw();
    updateSaveBadge(true);
  }
}

function updateSaveBadge(hasUnsaved) {
  const badge = ui.saveBadge;
  if (!App.pdfDoc) {
    badge.className = "badge bg-secondary-subtle text-secondary border border-secondary-subtle doc-save-badge";
    badge.textContent = "No File";
    return;
  }
  if (hasUnsaved) {
    badge.className = "badge bg-warning-subtle text-warning border border-warning-subtle doc-save-badge";
    badge.textContent = "● Unsaved Edits";
  } else {
    badge.className = "badge bg-success-subtle text-success border border-success-subtle doc-save-badge";
    badge.textContent = "✓ Clean";
  }
}

function updateDock() {
  ui.dockPageText.textContent = `Page ${App.page} / ${App.numPages}`;
  ui.dockZoomText.textContent = `${Math.round(App.scale * 100)}%`;
  ui.btnPrev.disabled = (App.page <= 1);
  ui.btnNext.disabled = (App.page >= App.numPages);
}

async function navPage(dir) {
  const target = App.page + dir;
  if (target >= 1 && target <= App.numPages) {
    App.page = target;
    deselectAll();
    await renderPage();
    updateDock();
  }
}

async function zoomChange(delta) {
  const cur = App.scale;
  App.scale = Math.max(0.4, Math.min(2.5, cur + delta));
  deselectAll();
  await renderPage();
  updateDock();
}

function applyConsignerStamp(consignerId, targetX = null, targetY = null) {
  if (!App.pdfDoc) {
    alert("Please upload or select an export PDF document first before stamping.");
    return;
  }

  const c = MYSQL_CONSIGNERS.find(item => item.id == consignerId);
  if (!c) return;

  const dropdownBtn = document.getElementById("btnConsignerDropdown");
  if (dropdownBtn) {
    const bsDrop = bootstrap.Dropdown.getInstance(dropdownBtn);
    if (bsDrop) bsDrop.hide();
  }

  const text = c.name.toUpperCase().trim();
  const items = getPageItems();

  if (App.selectedId) {
    const selected = items.find(it => it.id === App.selectedId);
    if (selected && selected.type === "text") {
      selected.text = text;
      selected.backgroundColor = "transparent";
      selected.isTransparent = true;
      autoFitItemToText(selected, true);
      syncFormatBar(selected);
      saveSnapshot();
      updateSaveBadge(true);
      redraw();
      ui.docStatusLabel.textContent = `Applied ${c.name} (Transparent)`;
      return;
    }
  }

  let posX = targetX !== null ? Math.max(0.01, Math.min(0.65, targetX)) : 0.05;
  let posY = targetY !== null ? Math.max(0.01, Math.min(0.85, targetY)) : 0.08;

  const newAnnotation = {
    id: "ann_consigner_" + Date.now(),
    type: "text",
    x: posX,
    y: posY,
    w: 0.01,
    h: 0.01,
    text: text,
    fontSize: 10,
    isBold: true,
    color: "#0F172A",
    backgroundColor: "transparent",
    isTransparent: true
  };

  autoFitItemToText(newAnnotation, true);
  items.push(newAnnotation);
  App.selectedId = newAnnotation.id;
  setTool("select");
  syncFormatBar(newAnnotation);
  saveSnapshot();
  updateSaveBadge(true);
  redraw();
  ui.docStatusLabel.textContent = `Stamped ${c.name} &bull; Drag to position`;
}

// Save edits to MySQL database
async function saveEditsToDatabase() {
  try {
    const payload = {
      document_name: App.fileName,
      annotations: App.annotations,
      edits_count: Object.values(App.annotations).flat().length
    };

    const res = await fetch("{{ route('pdf-editor.save') }}", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": window.csrfToken,
        "Accept": "application/json"
      },
      body: JSON.stringify(payload)
    });

    if (res.ok) {
      console.log("Annotations saved to MySQL database successfully.");
    }
  } catch (err) {
    console.warn("Could not save annotations to database:", err);
  }
}

// Load previously saved edits from MySQL database
async function loadSavedEditsFromDatabase(documentName) {
  try {
    const res = await fetch(`/pdf-editor/edits/${encodeURIComponent(documentName)}`, {
      headers: { "Accept": "application/json" }
    });
    if (res.ok) {
      const data = await res.json();
      if (data && data.annotations && Object.keys(data.annotations).length > 0) {
        App.annotations = data.annotations;
        saveSnapshot();
        redraw();
        showToast("Restored saved annotations from MySQL database", "info");
      }
    }
  } catch (err) {
    console.warn("Could not load saved annotations:", err);
  }
}

// Download & Export Clean PDF via PDF-Lib + Save to MySQL
async function downloadPdf() {
  if (!App.rawBytes || !App.pdfDoc) return;
  deselectAll();

  ui.docStatusLabel.textContent = "Compiling vector PDF & saving to MySQL...";

  try {
    // 1. Save state in MySQL database
    await saveEditsToDatabase();

    // 2. Vector compile with pdf-lib
    const pdfDoc = await PDFLib.PDFDocument.load(App.rawBytes);
    const helvetica = await pdfDoc.embedFont(PDFLib.StandardFonts.Helvetica);
    const helveticaBold = await pdfDoc.embedFont(PDFLib.StandardFonts.HelveticaBold);

    const pages = pdfDoc.getPages();

    for (let pIdx = 0; pIdx < pages.length; pIdx++) {
      const pageNum = pIdx + 1;
      const pdfPage = pages[pIdx];
      const items = App.annotations[pageNum] || [];
      const { width: pWidth, height: pHeight } = pdfPage.getSize();

      for (const it of items) {
        const itemW = it.w * pWidth;
        const itemH = it.h * pHeight;
        const itemX = it.x * pWidth;
        const itemY = pHeight - (it.y * pHeight) - itemH;

        if (it.type === "whiteout") {
          pdfPage.drawRectangle({
            x: itemX,
            y: itemY,
            width: itemW,
            height: itemH,
            color: PDFLib.rgb(1, 1, 1),
            opacity: 1.0
          });
        } else if (it.type === "redact") {
          pdfPage.drawRectangle({
            x: itemX,
            y: itemY,
            width: itemW,
            height: itemH,
            color: PDFLib.rgb(0, 0, 0),
            opacity: 1.0
          });
        } else if (it.type === "text") {
          if (it.backgroundColor !== "transparent" && !it.isTransparent) {
            pdfPage.drawRectangle({
              x: itemX,
              y: itemY,
              width: itemW,
              height: itemH,
              color: PDFLib.rgb(1, 1, 1),
              opacity: 1.0
            });
          }

          const font = it.isBold ? helveticaBold : helvetica;
          const fontSize = Math.max(6, it.fontSize || 10);
          const lines = (it.text || "").split("\n");
          const lineHeight = fontSize * 1.25;
          const padX = 6;
          const padY = 4;

          lines.forEach((line, lIdx) => {
            const lineY = itemY + itemH - padY - fontSize - (lIdx * lineHeight);
            pdfPage.drawText(line, {
              x: itemX + padX,
              y: Math.max(0, lineY),
              size: fontSize,
              font: font,
              color: PDFLib.rgb(15 / 255, 23 / 255, 42 / 255)
            });
          });
        }
      }
    }

    const modifiedPdfBytes = await pdfDoc.save();
    const blob = new Blob([modifiedPdfBytes], { type: "application/pdf" });
    const cleanName = App.fileName.replace(/\.pdf$/i, "") + "_CLEAN.pdf";

    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = cleanName;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);

    updateSaveBadge(false);
    ui.docStatusLabel.textContent = `✓ Exported ${cleanName} & Saved to MySQL!`;
    showToast(`PDF exported successfully & saved to MySQL database!`, "success");
  } catch (err) {
    console.error("PDF Export Error:", err);
    alert("Error compiling PDF: " + err.message);
    ui.docStatusLabel.textContent = "Error exporting PDF";
  }
}
</script>
@endpush
