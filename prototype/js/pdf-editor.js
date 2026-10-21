/**
 * Reliance Granito EDMS - PDF Visual Correction & Masking Canvas Editor
 * Powered by Mozilla PDF.js & pdf-lib
 */

// Application State
const PDFEditorState = {
  pdfDoc: null,            // PDF.js document handle
  originalBytes: null,     // ArrayBuffer of raw PDF bytes
  fileName: "Document.pdf",
  numPages: 0,
  currentPage: 1,
  currentScale: 1.0,
  activeTool: "select",    // "select" | "whiteout" | "text" | "redact"
  
  // Normalized annotations map: { [pageNum]: Array<Annotation> }
  annotations: {},
  
  // Selected annotation and interaction state
  selectedId: null,
  isDrawing: null,
  isDragging: null,
  isResizing: null,
  resizeCorner: null,
  dragStartX: 0,
  dragStartY: 0,
  itemStartCoords: null,
  
  // History for Undo/Redo: Array of snapshots
  history: [],
  historyIndex: -1,
  
  // Preview mode toggle
  isPreviewMode: false,
  
  // Text formatting defaults
  textDefaults: {
    fontFamily: "Helvetica",
    fontSize: 10,
    isBold: false,
    color: "#000000"
  }
};

// DOM Elements cache
let el = {};

document.addEventListener("DOMContentLoaded", () => {
  cacheElements();
  initCanvasListeners();
  initKeyboardShortcuts();
  
  // Auto-load sample Shipping Bill on first open
  setTimeout(() => {
    loadSampleDoc("1469 SB ORIGINAL.PDF", "Shipping Bill #1469 (Original Customs EDI)");
  }, 300);
});

function cacheElements() {
  el = {
    docTitle: document.getElementById("docTitle"),
    docStatusBadge: document.getElementById("docStatusBadge"),
    editsCountBadge: document.getElementById("editsCountBadge"),
    btnDownloadPdf: document.getElementById("btnDownloadPdf"),
    btnPrevPage: document.getElementById("btnPrevPage"),
    btnNextPage: document.getElementById("btnNextPage"),
    pageIndicator: document.getElementById("pageIndicator"),
    zoomIndicator: document.getElementById("zoomIndicator"),
    btnUndo: document.getElementById("btnUndo"),
    btnRedo: document.getElementById("btnRedo"),
    pdfLoadingState: document.getElementById("pdfLoadingState"),
    loadingText: document.getElementById("loadingText"),
    pdfEmptyState: document.getElementById("pdfEmptyState"),
    canvasContainer: document.getElementById("canvasContainer"),
    pdfRenderCanvas: document.getElementById("pdfRenderCanvas"),
    annotationOverlayCanvas: document.getElementById("annotationOverlayCanvas"),
    pageEditsCount: document.getElementById("pageEditsCount"),
    annotationsListContainer: document.getElementById("annotationsListContainer"),
    annotationsList: document.getElementById("annotationsList"),
    noEditsNotice: document.getElementById("noEditsNotice"),
    btnClearPage: document.getElementById("btnClearPage"),
    btnPreviewToggle: document.getElementById("btnPreviewToggle"),
    textFontFamily: document.getElementById("textFontFamily"),
    textFontSize: document.getElementById("textFontSize"),
    textBoldBtn: document.getElementById("textBoldBtn"),
    textColorPicker: document.getElementById("textColorPicker")
  };
}

// =========================================================================
// 1. PDF LOADING & RENDERING ENGINE (PDF.js)
// =========================================================================

async function loadPdfFromArrayBuffer(arrayBuffer, fileName) {
  try {
    showLoading(true, "Parsing PDF Structure...");
    PDFEditorState.originalBytes = arrayBuffer.slice(0);
    PDFEditorState.fileName = fileName;
    PDFEditorState.annotations = {};
    PDFEditorState.history = [];
    PDFEditorState.historyIndex = -1;
    PDFEditorState.selectedId = null;

    if (typeof pdfjsLib === "undefined") {
      throw new Error("PDF.js library is not loaded. Please verify CDN connection.");
    }

    const loadingTask = pdfjsLib.getDocument({ data: arrayBuffer });
    PDFEditorState.pdfDoc = await loadingTask.promise;
    PDFEditorState.numPages = PDFEditorState.pdfDoc.numPages;
    PDFEditorState.currentPage = 1;

    // Update Header UI
    if (el.docTitle) el.docTitle.textContent = fileName;
    if (el.docStatusBadge) {
      el.docStatusBadge.className = "badge bg-success";
      el.docStatusBadge.textContent = "Loaded & Ready";
    }
    if (el.pdfEmptyState) el.pdfEmptyState.style.display = "none";
    if (el.canvasContainer) el.canvasContainer.style.setProperty("display", "inline-block", "important");

    saveStateSnapshot();
    await renderCurrentPage();
    updateUIControls();
    showToast(`Successfully loaded: ${fileName} (${PDFEditorState.numPages} pages)`, "success");
  } catch (err) {
    console.error("Error loading PDF:", err);
    showLoading(false);
    alert("Could not load PDF: " + err.message);
  }
}

async function loadSampleDoc(filename, label) {
  try {
    showLoading(true, `Fetching ${label || filename}...`);
    const response = await fetch(`load-sample.php?file=${encodeURIComponent(filename)}`);
    if (!response.ok) {
      throw new Error(`Failed to load file (${response.status} ${response.statusText})`);
    }
    const buffer = await response.arrayBuffer();
    await loadPdfFromArrayBuffer(buffer, filename);
  } catch (err) {
    console.error("Error in loadSampleDoc:", err);
    showLoading(false);
    showToast("Error loading sample PDF: " + err.message, "warning");
  }
}

function handleFileUpload(event) {
  const file = event.target.files && event.target.files[0];
  if (!file) return;
  if (file.type !== "application/pdf" && !file.name.toLowerCase().endsWith(".pdf")) {
    alert("Please select a valid PDF file.");
    return;
  }
  const reader = new FileReader();
  reader.onload = async (e) => {
    await loadPdfFromArrayBuffer(e.target.result, file.name);
  };
  reader.readAsArrayBuffer(file);
}

async function renderCurrentPage() {
  if (!PDFEditorState.pdfDoc) return;
  showLoading(true, `Rendering Page ${PDFEditorState.currentPage} of ${PDFEditorState.numPages}...`);

  try {
    const page = await PDFEditorState.pdfDoc.getPage(PDFEditorState.currentPage);
    
    // Viewport calculation basis scale and devicePixelRatio for ultra-sharp rendering
    const unscaledViewport = page.getViewport({ scale: 1.0 });
    const pixelRatio = window.devicePixelRatio || 1.5;
    const viewport = page.getViewport({ scale: PDFEditorState.currentScale * pixelRatio });

    const pdfCanvas = el.pdfRenderCanvas;
    const overlayCanvas = el.annotationOverlayCanvas;
    const container = el.canvasContainer;

    const displayWidth = unscaledViewport.width * PDFEditorState.currentScale;
    const displayHeight = unscaledViewport.height * PDFEditorState.currentScale;

    // Set physical canvas pixel sizes
    pdfCanvas.width = viewport.width;
    pdfCanvas.height = viewport.height;
    overlayCanvas.width = viewport.width;
    overlayCanvas.height = viewport.height;

    // Set CSS display dimensions
    pdfCanvas.style.width = `${displayWidth}px`;
    pdfCanvas.style.height = `${displayHeight}px`;
    overlayCanvas.style.width = `${displayWidth}px`;
    overlayCanvas.style.height = `${displayHeight}px`;
    container.style.width = `${displayWidth}px`;
    container.style.height = `${displayHeight}px`;

    const ctx = pdfCanvas.getContext("2d");
    const renderContext = {
      canvasContext: ctx,
      viewport: viewport
    };

    await page.render(renderContext).promise;
    
    // Draw annotations overlay
    redrawAnnotations();
    showLoading(false);
  } catch (err) {
    console.error("Render page error:", err);
    showLoading(false);
  }
}

function showLoading(show, text = "") {
  if (!el.pdfLoadingState) return;
  el.pdfLoadingState.style.display = show ? "block" : "none";
  if (el.loadingText && text) el.loadingText.textContent = text;
}

// =========================================================================
// 2. INTERACTIVE OVERLAY CANVAS & ANNOTATION LOGIC
// =========================================================================

function getCurrentPageAnnotations() {
  if (!PDFEditorState.annotations[PDFEditorState.currentPage]) {
    PDFEditorState.annotations[PDFEditorState.currentPage] = [];
  }
  return PDFEditorState.annotations[PDFEditorState.currentPage];
}

function initCanvasListeners() {
  const overlay = document.getElementById("annotationOverlayCanvas");
  if (!overlay) return;

  overlay.addEventListener("mousedown", handleCanvasMouseDown);
  window.addEventListener("mousemove", handleCanvasMouseMove);
  window.addEventListener("mouseup", handleCanvasMouseUp);
  overlay.addEventListener("dblclick", handleCanvasDoubleClick);
}

// Convert screen mouse coordinates into normalized (0.0 to 1.0) page coordinates
function getNormalizedCoords(e) {
  const rect = el.annotationOverlayCanvas.getBoundingClientRect();
  const rawX = e.clientX - rect.left;
  const rawY = e.clientY - rect.top;
  
  const normX = Math.max(0, Math.min(1, rawX / rect.width));
  const normY = Math.max(0, Math.min(1, rawY / rect.height));
  return { normX, normY, rawX, rawY, rectWidth: rect.width, rectHeight: rect.height };
}

function handleCanvasMouseDown(e) {
  if (!PDFEditorState.pdfDoc) return;
  const { normX, normY, rawX, rawY, rectWidth, rectHeight } = getNormalizedCoords(e);
  const items = getCurrentPageAnnotations();

  // 1. Check if clicking on resize handles of selected item
  if (PDFEditorState.selectedId) {
    const selected = items.find(i => i.id === PDFEditorState.selectedId);
    if (selected) {
      const handle = getHitResizeHandle(selected, rawX, rawY, rectWidth, rectHeight);
      if (handle) {
        PDFEditorState.isResizing = true;
        PDFEditorState.resizeCorner = handle;
        PDFEditorState.dragStartX = normX;
        PDFEditorState.dragStartY = normY;
        PDFEditorState.itemStartCoords = { ...selected };
        return;
      }
    }
  }

  // 2. Select tool behavior
  if (PDFEditorState.activeTool === "select") {
    // Hit test from topmost (reverse order)
    let hitItem = null;
    for (let i = items.length - 1; i >= 0; i--) {
      const it = items[i];
      if (normX >= it.x && normX <= it.x + it.width &&
          normY >= it.y && normY <= it.y + it.height) {
        hitItem = it;
        break;
      }
    }

    if (hitItem) {
      PDFEditorState.selectedId = hitItem.id;
      PDFEditorState.isDragging = true;
      PDFEditorState.dragStartX = normX;
      PDFEditorState.dragStartY = normY;
      PDFEditorState.itemStartCoords = { ...hitItem };
      syncTextControlsWithItem(hitItem);
    } else {
      PDFEditorState.selectedId = null;
    }
    redrawAnnotations();
    updateUIControls();
    return;
  }

  // 3. Drawing a new correction item (whiteout, text, redact)
  PDFEditorState.selectedId = null;
  PDFEditorState.isDrawing = true;
  PDFEditorState.dragStartX = normX;
  PDFEditorState.dragStartY = normY;

  const newItem = {
    id: "ann_" + Date.now() + "_" + Math.random().toString(36).substr(2, 4),
    type: PDFEditorState.activeTool,
    x: normX,
    y: normY,
    width: 0.001,
    height: 0.001,
    text: PDFEditorState.activeTool === "text" ? "Amended Value" : "",
    fontFamily: PDFEditorState.textDefaults.fontFamily,
    fontSize: PDFEditorState.textDefaults.fontSize,
    isBold: PDFEditorState.textDefaults.isBold,
    color: PDFEditorState.textDefaults.color,
    backgroundColor: PDFEditorState.activeTool === "redact" ? "#000000" : "#FFFFFF"
  };

  items.push(newItem);
  PDFEditorState.currentDrawingItem = newItem;
  redrawAnnotations();
}

function handleCanvasMouseMove(e) {
  if (!PDFEditorState.pdfDoc) return;
  const { normX, normY, rawX, rawY, rectWidth, rectHeight } = getNormalizedCoords(e);
  const items = getCurrentPageAnnotations();

  // Resize cursor indication
  if (PDFEditorState.activeTool === "select" && PDFEditorState.selectedId && !PDFEditorState.isDrawing && !PDFEditorState.isDragging && !PDFEditorState.isResizing) {
    const selected = items.find(i => i.id === PDFEditorState.selectedId);
    if (selected) {
      const handle = getHitResizeHandle(selected, rawX, rawY, rectWidth, rectHeight);
      el.annotationOverlayCanvas.style.cursor = handle ? `${handle}-resize` : "move";
    }
  }

  // A. Handling Drawing Box
  if (PDFEditorState.isDrawing && PDFEditorState.currentDrawingItem) {
    const item = PDFEditorState.currentDrawingItem;
    const startX = PDFEditorState.dragStartX;
    const startY = PDFEditorState.dragStartY;

    item.x = Math.min(startX, normX);
    item.y = Math.min(startY, normY);
    item.width = Math.max(0.005, Math.abs(normX - startX));
    item.height = Math.max(0.005, Math.abs(normY - startY));
    redrawAnnotations();
    return;
  }

  // B. Handling Dragging Existing Box
  if (PDFEditorState.isDragging && PDFEditorState.selectedId) {
    const item = items.find(i => i.id === PDFEditorState.selectedId);
    if (!item || !PDFEditorState.itemStartCoords) return;

    const deltaX = normX - PDFEditorState.dragStartX;
    const deltaY = normY - PDFEditorState.dragStartY;

    item.x = Math.max(0, Math.min(1 - item.width, PDFEditorState.itemStartCoords.x + deltaX));
    item.y = Math.max(0, Math.min(1 - item.height, PDFEditorState.itemStartCoords.y + deltaY));
    redrawAnnotations();
    return;
  }

  // C. Handling Resizing Box
  if (PDFEditorState.isResizing && PDFEditorState.selectedId) {
    const item = items.find(i => i.id === PDFEditorState.selectedId);
    if (!item || !PDFEditorState.itemStartCoords) return;

    const corner = PDFEditorState.resizeCorner;
    const start = PDFEditorState.itemStartCoords;

    if (corner === "se") {
      item.width = Math.max(0.01, normX - start.x);
      item.height = Math.max(0.01, normY - start.y);
    } else if (corner === "sw") {
      const newWidth = Math.max(0.01, (start.x + start.width) - normX);
      item.x = (start.x + start.width) - newWidth;
      item.width = newWidth;
      item.height = Math.max(0.01, normY - start.y);
    } else if (corner === "ne") {
      item.width = Math.max(0.01, normX - start.x);
      const newHeight = Math.max(0.01, (start.y + start.height) - normY);
      item.y = (start.y + start.height) - newHeight;
      item.height = newHeight;
    } else if (corner === "nw") {
      const newWidth = Math.max(0.01, (start.x + start.width) - normX);
      const newHeight = Math.max(0.01, (start.y + start.height) - normY);
      item.x = (start.x + start.width) - newWidth;
      item.y = (start.y + start.height) - newHeight;
      item.width = newWidth;
      item.height = newHeight;
    }
    redrawAnnotations();
    return;
  }
}

function handleCanvasMouseUp(e) {
  if (PDFEditorState.isDrawing && PDFEditorState.currentDrawingItem) {
    const item = PDFEditorState.currentDrawingItem;
    PDFEditorState.isDrawing = false;
    PDFEditorState.currentDrawingItem = null;

    // Minimum size filter to prevent accidental dot clicks
    if (item.width < 0.015 && item.height < 0.015) {
      const items = getCurrentPageAnnotations();
      const idx = items.indexOf(item);
      if (idx !== -1) items.splice(idx, 1);
    } else {
      PDFEditorState.selectedId = item.id;
      // If it was a text replacement, prompt edit modal immediately for great UX
      if (item.type === "text") {
        openTextModalForItem(item);
      }
      saveStateSnapshot();
    }
    setEditorTool("select");
    redrawAnnotations();
    updateUIControls();
    return;
  }

  if (PDFEditorState.isDragging || PDFEditorState.isResizing) {
    PDFEditorState.isDragging = false;
    PDFEditorState.isResizing = false;
    PDFEditorState.resizeCorner = null;
    PDFEditorState.itemStartCoords = null;
    saveStateSnapshot();
    updateUIControls();
  }
}

function handleCanvasDoubleClick(e) {
  const { normX, normY } = getNormalizedCoords(e);
  const items = getCurrentPageAnnotations();
  for (let i = items.length - 1; i >= 0; i--) {
    const it = items[i];
    if (normX >= it.x && normX <= it.x + it.width &&
        normY >= it.y && normY <= it.y + it.height) {
      PDFEditorState.selectedId = it.id;
      redrawAnnotations();
      openTextModalForItem(it);
      break;
    }
  }
}

function getHitResizeHandle(item, rawX, rawY, rectW, rectH) {
  const handleSize = 10;
  const x = item.x * rectW;
  const y = item.y * rectH;
  const w = item.width * rectW;
  const h = item.height * rectH;

  // Handles at nw, ne, se, sw
  if (Math.abs(rawX - x) <= handleSize && Math.abs(rawY - y) <= handleSize) return "nw";
  if (Math.abs(rawX - (x + w)) <= handleSize && Math.abs(rawY - y) <= handleSize) return "ne";
  if (Math.abs(rawX - (x + w)) <= handleSize && Math.abs(rawY - (y + h)) <= handleSize) return "se";
  if (Math.abs(rawX - x) <= handleSize && Math.abs(rawY - (y + h)) <= handleSize) return "sw";
  return null;
}

// =========================================================================
// 3. RENDERING OVERLAY ANNOTATIONS (CANVAS 2D)
// =========================================================================

function redrawAnnotations() {
  const canvas = el.annotationOverlayCanvas;
  if (!canvas) return;
  const ctx = canvas.getContext("2d");
  const width = canvas.width;
  const height = canvas.height;
  ctx.clearRect(0, 0, width, height);

  const items = getCurrentPageAnnotations();
  const isPreview = PDFEditorState.isPreviewMode;

  items.forEach(it => {
    const x = it.x * width;
    const y = it.y * height;
    const w = it.width * width;
    const h = it.height * height;
    const isSelected = (!isPreview && it.id === PDFEditorState.selectedId);

    // 1. Draw Background Mask
    if (it.type === "redact") {
      ctx.fillStyle = "#000000";
      ctx.fillRect(x, y, w, h);
    } else {
      // Whiteout or Text box
      ctx.fillStyle = it.backgroundColor || "#FFFFFF";
      ctx.fillRect(x, y, w, h);
    }

    // 2. Draw Text if present
    if (it.text && it.text.trim().length > 0) {
      ctx.save();
      ctx.fillStyle = it.color || "#000000";
      
      // Calculate scaled font size based on canvas width
      const fontScale = width / 800; // Base reference scale
      const fontSizePx = Math.max(9, (it.fontSize || 10) * fontScale);
      const fontWeight = it.isBold ? "bold" : "normal";
      ctx.font = `${fontWeight} ${fontSizePx}px ${it.fontFamily || "Helvetica"}, sans-serif`;
      ctx.textBaseline = "top";

      // Split lines for multiline text
      const lines = it.text.split("\n");
      const lineHeight = fontSizePx * 1.25;
      lines.forEach((line, lineIdx) => {
        ctx.fillText(line, x + (4 * fontScale), y + (3 * fontScale) + (lineIdx * lineHeight));
      });
      ctx.restore();
    }

    // 3. Draw Stamp Banner if type === "stamp"
    if (it.type === "stamp") {
      ctx.save();
      ctx.strokeStyle = it.color || "#DC2626";
      ctx.lineWidth = 3;
      ctx.strokeRect(x, y, w, h);
      ctx.restore();
    }

    // 4. Draw Selection Outline & Resize Handles
    if (isSelected) {
      ctx.save();
      ctx.strokeStyle = "#2563EB";
      ctx.lineWidth = 2;
      ctx.setLineDash([4, 4]);
      ctx.strokeRect(x, y, w, h);
      ctx.setLineDash([]);

      // Draw 4 corner handles
      const handleRadius = 5;
      ctx.fillStyle = "#2563EB";
      [
        { hx: x, hy: y },
        { hx: x + w, hy: y },
        { hx: x + w, hy: y + h },
        { hx: x, hy: y + h }
      ].forEach(hPoint => {
        ctx.beginPath();
        ctx.arc(hPoint.hx, hPoint.hy, handleRadius, 0, Math.PI * 2);
        ctx.fill();
        ctx.strokeStyle = "#FFFFFF";
        ctx.lineWidth = 1.5;
        ctx.stroke();
      });
      ctx.restore();
    } else if (!isPreview && it.type === "whiteout") {
      // Subtle dashed border so user knows where whiteout is placed in edit mode
      ctx.save();
      ctx.strokeStyle = "#CBD5E1";
      ctx.lineWidth = 1;
      ctx.setLineDash([3, 3]);
      ctx.strokeRect(x, y, w, h);
      ctx.restore();
    }
  });

  renderSidebarEditsList();
}

function renderSidebarEditsList() {
  const items = getCurrentPageAnnotations();
  if (el.pageEditsCount) el.pageEditsCount.textContent = items.length;
  if (el.btnClearPage) el.btnClearPage.disabled = (items.length === 0);

  if (items.length === 0) {
    if (el.noEditsNotice) el.noEditsNotice.style.display = "block";
    if (el.annotationsList) el.annotationsList.innerHTML = "";
    return;
  }

  if (el.noEditsNotice) el.noEditsNotice.style.display = "none";
  if (!el.annotationsList) return;

  el.annotationsList.innerHTML = items.map((item, index) => {
    const isSelected = item.id === PDFEditorState.selectedId;
    let icon = "bi-square-fill text-warning";
    let typeName = "Whiteout Mask";
    if (item.type === "text") {
      icon = "bi-fonts text-primary";
      typeName = "Replacement Text";
    } else if (item.type === "redact") {
      icon = "bi-square-fill text-dark";
      typeName = "Blackout Redaction";
    } else if (item.type === "stamp") {
      icon = "bi-patch-check text-danger";
      typeName = "Stamp Annotation";
    }

    const previewSnippet = item.text ? `"${item.text.substring(0, 22)}${item.text.length > 22 ? '...' : ''}"` : `${typeName}`;

    return `
      <div class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-2 mb-1 rounded border ${isSelected ? 'border-primary bg-primary-subtle' : ''}" style="cursor:pointer;" onclick="selectAnnotationById('${item.id}')">
        <div class="d-flex align-items-center gap-2 overflow-hidden">
          <i class="bi ${icon} fs-6 flex-shrink-0"></i>
          <div class="text-truncate">
            <div class="fw-semibold small text-dark">${previewSnippet}</div>
            <div class="text-muted" style="font-size:0.72rem;">${item.type.toUpperCase()} &bull; Page ${PDFEditorState.currentPage}</div>
          </div>
        </div>
        <div class="d-flex align-items-center gap-1 flex-shrink-0">
          <button class="btn btn-sm btn-link text-primary p-0 px-1" title="Edit Properties" onclick="event.stopPropagation(); openTextModalForItem(getCurrentPageAnnotations().find(i => i.id === '${item.id}'))">
            <i class="bi bi-pencil"></i>
          </button>
          <button class="btn btn-sm btn-link text-danger p-0 px-1" title="Delete" onclick="event.stopPropagation(); deleteAnnotationById('${item.id}')">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </div>
    `;
  }).join("");
}

function selectAnnotationById(id) {
  PDFEditorState.selectedId = id;
  const item = getCurrentPageAnnotations().find(i => i.id === id);
  if (item) syncTextControlsWithItem(item);
  redrawAnnotations();
  updateUIControls();
}

function deleteAnnotationById(id) {
  const items = getCurrentPageAnnotations();
  const idx = items.findIndex(i => i.id === id);
  if (idx !== -1) {
    items.splice(idx, 1);
    if (PDFEditorState.selectedId === id) PDFEditorState.selectedId = null;
    saveStateSnapshot();
    redrawAnnotations();
    updateUIControls();
    showToast("Correction removed.", "info");
  }
}

function clearCurrentPageEdits() {
  if (confirm("Are you sure you want to remove all corrections on this page?")) {
    PDFEditorState.annotations[PDFEditorState.currentPage] = [];
    PDFEditorState.selectedId = null;
    saveStateSnapshot();
    redrawAnnotations();
    updateUIControls();
    showToast(`Cleared all corrections on Page ${PDFEditorState.currentPage}`, "info");
  }
}

// =========================================================================
// 4. TOOLBAR & FORMATTING ACTIONS
// =========================================================================

function setEditorTool(tool) {
  PDFEditorState.activeTool = tool;
  ["toolSelect", "toolWhiteout", "toolText", "toolRedact"].forEach(id => {
    const btn = document.getElementById(id);
    if (btn) btn.classList.remove("active");
  });

  const activeBtn = document.getElementById("tool" + tool.charAt(0).toUpperCase() + tool.slice(1));
  if (activeBtn) activeBtn.classList.add("active");

  const canvas = el.annotationOverlayCanvas;
  if (canvas) {
    if (tool === "select") canvas.style.cursor = "default";
    else canvas.style.cursor = "crosshair";
  }
}

function syncTextControlsWithItem(item) {
  if (el.textFontFamily) el.textFontFamily.value = item.fontFamily || "Helvetica";
  if (el.textFontSize) el.textFontSize.value = String(item.fontSize || 10);
  if (el.textColorPicker) el.textColorPicker.value = item.color || "#000000";
  if (el.textBoldBtn) {
    if (item.isBold) el.textBoldBtn.classList.add("active", "btn-primary");
    else el.textBoldBtn.classList.remove("active", "btn-primary");
  }
}

function updateSelectedTextProperties() {
  const items = getCurrentPageAnnotations();
  const item = items.find(i => i.id === PDFEditorState.selectedId);
  
  const family = el.textFontFamily ? el.textFontFamily.value : "Helvetica";
  const size = el.textFontSize ? parseInt(el.textFontSize.value, 10) : 10;
  const color = el.textColorPicker ? el.textColorPicker.value : "#000000";

  PDFEditorState.textDefaults.fontFamily = family;
  PDFEditorState.textDefaults.fontSize = size;
  PDFEditorState.textDefaults.color = color;

  if (item) {
    item.fontFamily = family;
    item.fontSize = size;
    item.color = color;
    saveStateSnapshot();
    redrawAnnotations();
  }
}

function toggleBoldText() {
  const isBold = !PDFEditorState.textDefaults.isBold;
  PDFEditorState.textDefaults.isBold = isBold;
  
  if (el.textBoldBtn) {
    if (isBold) el.textBoldBtn.classList.add("active", "btn-primary");
    else el.textBoldBtn.classList.remove("active", "btn-primary");
  }

  const items = getCurrentPageAnnotations();
  const item = items.find(i => i.id === PDFEditorState.selectedId);
  if (item) {
    item.isBold = isBold;
    saveStateSnapshot();
    redrawAnnotations();
  }
}

function toggleHidePreview() {
  PDFEditorState.isPreviewMode = !PDFEditorState.isPreviewMode;
  if (el.btnPreviewToggle) {
    if (PDFEditorState.isPreviewMode) {
      el.btnPreviewToggle.classList.add("btn-warning", "text-dark");
      el.btnPreviewToggle.innerHTML = '<i class="bi bi-eye-slash"></i> Exit Preview';
      showToast("Clean Document Preview Active.", "info");
    } else {
      el.btnPreviewToggle.classList.remove("btn-warning", "text-dark");
      el.btnPreviewToggle.innerHTML = '<i class="bi bi-eye"></i> Preview';
    }
  }
  redrawAnnotations();
}

// =========================================================================
// 5. MODAL EDITING FOR TEXT ANNOTATIONS
// =========================================================================

let activeModalItem = null;

function openTextModalForItem(item) {
  if (!item) return;
  activeModalItem = item;
  
  const modalInput = document.getElementById("modalTextInput");
  const modalSize = document.getElementById("modalTextFontSize");
  const modalWeight = document.getElementById("modalTextFontWeight");

  if (modalInput) modalInput.value = item.text || "";
  if (modalSize) modalSize.value = String(item.fontSize || 10);
  if (modalWeight) modalWeight.value = item.isBold ? "bold" : "normal";

  const modalEl = document.getElementById("editTextModal");
  if (modalEl && typeof bootstrap !== "undefined") {
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
  }
}

function saveModalTextChanges() {
  if (!activeModalItem) return;
  const modalInput = document.getElementById("modalTextInput");
  const modalSize = document.getElementById("modalTextFontSize");
  const modalWeight = document.getElementById("modalTextFontWeight");

  activeModalItem.text = modalInput ? modalInput.value : "";
  activeModalItem.fontSize = modalSize ? parseInt(modalSize.value, 10) : 10;
  activeModalItem.isBold = (modalWeight && modalWeight.value === "bold");

  const modalEl = document.getElementById("editTextModal");
  if (modalEl && typeof bootstrap !== "undefined") {
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
  }

  saveStateSnapshot();
  redrawAnnotations();
  updateUIControls();
  showToast("Correction text updated.", "success");
}

function deleteCurrentModalItem() {
  if (activeModalItem) {
    deleteAnnotationById(activeModalItem.id);
    const modalEl = document.getElementById("editTextModal");
    if (modalEl && typeof bootstrap !== "undefined") {
      const modal = bootstrap.Modal.getInstance(modalEl);
      if (modal) modal.hide();
    }
  }
}

// =========================================================================
// 6. QUICK PRESETS & STAMPS
// =========================================================================

function applyStamp(type) {
  const stampConfig = {
    "AMENDED": { text: "AMENDED & REVISED", color: "#DC2626" },
    "VERIFIED": { text: "VERIFIED & MATCHED", color: "#059669" },
    "CUSTOMS CLEARED": { text: "CUSTOMS CLEARED - ICEGATE", color: "#2563EB" },
    "CONFIDENTIAL": { text: "STRICTLY CONFIDENTIAL", color: "#D97706" }
  };

  const cfg = stampConfig[type] || { text: type, color: "#DC2626" };
  const items = getCurrentPageAnnotations();

  const stampItem = {
    id: "stamp_" + Date.now(),
    type: "stamp",
    x: 0.65,
    y: 0.04,
    width: 0.30,
    height: 0.05,
    text: `[ ${cfg.text} ]`,
    fontFamily: "Helvetica",
    fontSize: 12,
    isBold: true,
    color: cfg.color,
    backgroundColor: "#FFFFFF"
  };

  items.push(stampItem);
  PDFEditorState.selectedId = stampItem.id;
  saveStateSnapshot();
  redrawAnnotations();
  updateUIControls();
  showToast(`Applied stamp: ${cfg.text}`, "success");
}

function applyPreset(presetKey) {
  const items = getCurrentPageAnnotations();

  if (presetKey === "mask_rates") {
    // Mask typical FOB / Unit Price column in Shipping Bill / Invoice
    items.push({
      id: "ann_preset_" + Date.now() + "_rate",
      type: "whiteout",
      x: 0.72,
      y: 0.32,
      width: 0.24,
      height: 0.18,
      text: "",
      backgroundColor: "#FFFFFF"
    });
    showToast("Applied Preset: FOB / Unit Rates Masked", "warning");
  } else if (presetKey === "amend_haiphong") {
    // Replace consignee destination with Hai Phong
    items.push({
      id: "ann_preset_" + Date.now() + "_haiphong",
      type: "text",
      x: 0.45,
      y: 0.22,
      width: 0.48,
      height: 0.07,
      text: "CJ Stone Co. Ltd (Dong Hai Ward, Hai Phong City, Vietnam)\nPort of Discharge: Haiphong Port (VNHPH)",
      fontFamily: "Courier",
      fontSize: 9,
      isBold: true,
      color: "#0F172A",
      backgroundColor: "#FFFFFF"
    });
    showToast("Applied Preset: Destination amended to Hai Phong Port", "success");
  } else if (presetKey === "mask_freight") {
    // Redact freight rates
    items.push({
      id: "ann_preset_" + Date.now() + "_frt",
      type: "redact",
      x: 0.55,
      y: 0.65,
      width: 0.38,
      height: 0.06,
      text: "",
      backgroundColor: "#000000"
    });
    showToast("Applied Preset: Freight Charges Redacted", "warning");
  } else if (presetKey === "add_original_stamp") {
    applyStamp("VERIFIED");
    return;
  }

  saveStateSnapshot();
  redrawAnnotations();
  updateUIControls();
}

// =========================================================================
// 7. PAGE NAVIGATION, ZOOM & HISTORY (UNDO / REDO)
// =========================================================================

async function changePage(offset) {
  const newPage = PDFEditorState.currentPage + offset;
  if (newPage >= 1 && newPage <= PDFEditorState.numPages) {
    PDFEditorState.currentPage = newPage;
    PDFEditorState.selectedId = null;
    await renderCurrentPage();
    updateUIControls();
  }
}

async function adjustZoom(delta) {
  const newScale = Math.max(0.5, Math.min(2.5, PDFEditorState.currentScale + delta));
  if (Math.abs(newScale - PDFEditorState.currentScale) > 0.05) {
    PDFEditorState.currentScale = Math.round(newScale * 10) / 10;
    await renderCurrentPage();
    updateUIControls();
  }
}

async function resetZoomFit() {
  const wrapper = el.pdfViewportWrapper || document.getElementById("pdfViewportWrapper");
  if (!wrapper || !PDFEditorState.pdfDoc) return;
  const page = await PDFEditorState.pdfDoc.getPage(PDFEditorState.currentPage);
  const unscaled = page.getViewport({ scale: 1.0 });
  const availableWidth = wrapper.clientWidth - 40;
  const fitScale = Math.max(0.6, Math.min(2.0, availableWidth / unscaled.width));
  PDFEditorState.currentScale = Math.round(fitScale * 10) / 10;
  await renderCurrentPage();
  updateUIControls();
}

function saveStateSnapshot() {
  // Prune future states if branched
  PDFEditorState.history = PDFEditorState.history.slice(0, PDFEditorState.historyIndex + 1);
  // Deep clone annotations
  const snapshot = JSON.parse(JSON.stringify(PDFEditorState.annotations));
  PDFEditorState.history.push(snapshot);
  PDFEditorState.historyIndex = PDFEditorState.history.length - 1;
  updateUIControls();
}

function undoEdit() {
  if (PDFEditorState.historyIndex > 0) {
    PDFEditorState.historyIndex--;
    const snapshot = PDFEditorState.history[PDFEditorState.historyIndex];
    PDFEditorState.annotations = JSON.parse(JSON.stringify(snapshot));
    PDFEditorState.selectedId = null;
    redrawAnnotations();
    updateUIControls();
    showToast("Undo applied", "info");
  }
}

function redoEdit() {
  if (PDFEditorState.historyIndex < PDFEditorState.history.length - 1) {
    PDFEditorState.historyIndex++;
    const snapshot = PDFEditorState.history[PDFEditorState.historyIndex];
    PDFEditorState.annotations = JSON.parse(JSON.stringify(snapshot));
    PDFEditorState.selectedId = null;
    redrawAnnotations();
    updateUIControls();
    showToast("Redo applied", "info");
  }
}

function initKeyboardShortcuts() {
  window.addEventListener("keydown", (e) => {
    // Delete key
    if ((e.key === "Delete" || e.key === "Backspace") && PDFEditorState.selectedId) {
      const activeEl = document.activeElement;
      if (activeEl && (activeEl.tagName === "INPUT" || activeEl.tagName === "TEXTAREA")) return;
      deleteAnnotationById(PDFEditorState.selectedId);
      e.preventDefault();
    }
    // Undo: Ctrl+Z
    if (e.ctrlKey && e.key.toLowerCase() === "z" && !e.shiftKey) {
      undoEdit();
      e.preventDefault();
    }
    // Redo: Ctrl+Y or Ctrl+Shift+Z
    if ((e.ctrlKey && e.key.toLowerCase() === "y") || (e.ctrlKey && e.shiftKey && e.key.toLowerCase() === "z")) {
      redoEdit();
      e.preventDefault();
    }
  });
}

function updateUIControls() {
  if (el.pageIndicator) {
    el.pageIndicator.textContent = `Page ${PDFEditorState.currentPage} / ${PDFEditorState.numPages || 1}`;
  }
  if (el.zoomIndicator) {
    el.zoomIndicator.textContent = `${Math.round(PDFEditorState.currentScale * 100)}%`;
  }
  if (el.btnPrevPage) el.btnPrevPage.disabled = (PDFEditorState.currentPage <= 1);
  if (el.btnNextPage) el.btnNextPage.disabled = (PDFEditorState.currentPage >= PDFEditorState.numPages);
  
  if (el.btnUndo) el.btnUndo.disabled = (PDFEditorState.historyIndex <= 0);
  if (el.btnRedo) el.btnRedo.disabled = (PDFEditorState.historyIndex >= PDFEditorState.history.length - 1);

  // Total edits count across all pages
  let totalEdits = 0;
  Object.values(PDFEditorState.annotations).forEach(list => {
    totalEdits += list.length;
  });

  if (el.editsCountBadge) {
    if (totalEdits > 0) {
      el.editsCountBadge.style.display = "inline-block";
      el.editsCountBadge.textContent = `${totalEdits} Correction${totalEdits > 1 ? 's' : ''}`;
    } else {
      el.editsCountBadge.style.display = "none";
    }
  }

  if (el.btnDownloadPdf) {
    el.btnDownloadPdf.disabled = !PDFEditorState.originalBytes;
  }
}

// =========================================================================
// 8. PDF-LIB EXPORT ENGINE: BURNING CORRECTIONS INTO NATIVE VECTOR PDF
// =========================================================================

async function downloadUpdatedPdf() {
  if (!PDFEditorState.originalBytes) {
    alert("Please load a PDF document first.");
    return;
  }

  if (typeof PDFLib === "undefined") {
    alert("PDF generation engine (pdf-lib) is not available. Please check internet connection.");
    return;
  }

  try {
    showLoading(true, "Compiling native PDF with vector corrections...");
    
    // Load original PDF bytes
    const pdfDoc = await PDFLib.PDFDocument.load(PDFEditorState.originalBytes);
    
    // Standard embed fonts
    const fontHelvetica = await pdfDoc.embedFont(PDFLib.StandardFonts.Helvetica);
    const fontHelveticaBold = await pdfDoc.embedFont(PDFLib.StandardFonts.HelveticaBold);
    const fontCourier = await pdfDoc.embedFont(PDFLib.StandardFonts.Courier);
    const fontCourierBold = await pdfDoc.embedFont(PDFLib.StandardFonts.CourierBold);
    const fontTimes = await pdfDoc.embedFont(PDFLib.StandardFonts.TimesRoman);
    const fontTimesBold = await pdfDoc.embedFont(PDFLib.StandardFonts.TimesRomanBold);

    const pages = pdfDoc.getPages();

    // Iterate through all pages and burn-in annotations
    for (let pageNum = 1; pageNum <= pages.length; pageNum++) {
      const pageAnnList = PDFEditorState.annotations[pageNum] || [];
      if (pageAnnList.length === 0) continue;

      const page = pages[pageNum - 1];
      const { width: pdfWidth, height: pdfHeight } = page.getSize();

      for (const ann of pageAnnList) {
        // Map normalized coordinates (0 to 1) to native PDF point coordinates (pt)
        const pdfX = ann.x * pdfWidth;
        const pdfW = ann.width * pdfWidth;
        const pdfH = ann.height * pdfHeight;
        // Invert Y coordinate since PDF (0,0) is at bottom-left
        const pdfY = pdfHeight - (ann.y * pdfHeight) - pdfH;

        // A. Draw Background Rectangle (Whiteout or Redact)
        if (ann.type === "redact") {
          page.drawRectangle({
            x: pdfX,
            y: pdfY,
            width: pdfW,
            height: pdfH,
            color: PDFLib.rgb(0, 0, 0),
            borderWidth: 0
          });
        } else {
          // Whiteout, Text replacement, or Stamp
          page.drawRectangle({
            x: pdfX,
            y: pdfY,
            width: pdfW,
            height: pdfH,
            color: PDFLib.rgb(1, 1, 1),
            borderWidth: ann.type === "stamp" ? 2 : 0,
            borderColor: ann.type === "stamp" ? hexToPdfRgb(ann.color || "#DC2626") : PDFLib.rgb(1, 1, 1)
          });
        }

        // B. Draw Text if present
        if (ann.text && ann.text.trim().length > 0) {
          let chosenFont = fontHelvetica;
          if (ann.fontFamily === "Courier") {
            chosenFont = ann.isBold ? fontCourierBold : fontCourier;
          } else if (ann.fontFamily === "Times") {
            chosenFont = ann.isBold ? fontTimesBold : fontTimes;
          } else {
            chosenFont = ann.isBold ? fontHelveticaBold : fontHelvetica;
          }

          const textColor = hexToPdfRgb(ann.color || "#000000");
          const fontSize = Math.max(7, ann.fontSize || 10);
          const lines = ann.text.split("\n");
          const lineStep = fontSize * 1.25;

          // Starting from top of the box downwards
          let currentY = pdfY + pdfH - fontSize - 2;

          for (const line of lines) {
            if (line.trim().length > 0 && currentY >= pdfY) {
              page.drawText(line, {
                x: pdfX + 3,
                y: currentY,
                size: fontSize,
                font: chosenFont,
                color: textColor
              });
            }
            currentY -= lineStep;
          }
        }
      }
    }

    // Save modified PDF bytes
    const modifiedBytes = await pdfDoc.save();
    showLoading(false);

    // Create Download Link
    const blob = new Blob([modifiedBytes], { type: "application/pdf" });
    const downloadUrl = URL.createObjectURL(blob);
    const link = document.createElement("a");
    const outputName = PDFEditorState.fileName.replace(/\.pdf$/i, "") + "_UPDATED.pdf";
    link.href = downloadUrl;
    link.download = outputName;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(downloadUrl);

    showToast(`Updated PDF downloaded successfully: ${outputName}`, "success");
  } catch (err) {
    console.error("PDF generation failed:", err);
    showLoading(false);
    alert("Could not generate updated PDF: " + err.message);
  }
}

// Convert HEX color string ("#1E293B") to PDFLib RGB object (0.0 to 1.0)
function hexToPdfRgb(hex) {
  let cleanHex = hex.replace("#", "");
  if (cleanHex.length === 3) {
    cleanHex = cleanHex.split("").map(c => c + c).join("");
  }
  const r = parseInt(cleanHex.substring(0, 2), 16) / 255;
  const g = parseInt(cleanHex.substring(2, 4), 16) / 255;
  const b = parseInt(cleanHex.substring(4, 6), 16) / 255;
  return PDFLib.rgb(r || 0, g || 0, b || 0);
}
