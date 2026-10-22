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

function escapeHtml(str) {
  if (!str) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}
window.escapeHtml = escapeHtml;

document.addEventListener("DOMContentLoaded", () => {
  cacheElements();
  initCanvasListeners();
  initKeyboardShortcuts();
  initConsignersDropdown();
  checkPendingConsignerStamp();
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
    textColorPicker: document.getElementById("textColorPicker"),
    inlineTextEditor: document.getElementById("inlineTextEditor"),
    floatingItemToolbar: document.getElementById("floatingItemToolbar"),
    floatBtnEdit: document.getElementById("floatBtnEdit")
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

  // Setup inline text editor events
  const inline = document.getElementById("inlineTextEditor");
  if (inline) {
    inline.addEventListener("keydown", (e) => {
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        commitInlineTextEditing();
      } else if (e.key === "Escape") {
        e.preventDefault();
        cancelInlineTextEditing();
      }
    });
    inline.addEventListener("blur", () => {
      // Small timeout to allow button clicks on floating toolbar
      setTimeout(commitInlineTextEditing, 120);
    });
  }
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
  
  // If editing text inline, commit before doing anything else
  if (PDFEditorState.editingInlineId) {
    commitInlineTextEditing();
  }

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

  // 2. UNIVERSAL HIT TEST: Clicking ANY existing item selects and moves it (in ANY tool mode!)
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
    redrawAnnotations();
    updateUIControls();
    updateFloatingToolbarPosition();
    return;
  }

  // 3. Clicked on EMPTY SPACE:
  if (PDFEditorState.activeTool === "select") {
    PDFEditorState.selectedId = null;
    hideFloatingToolbar();
    redrawAnnotations();
    updateUIControls();
    return;
  }

  // 4. Drawing a new item (whiteout, text, redact)
  PDFEditorState.selectedId = null;
  hideFloatingToolbar();
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
    text: PDFEditorState.activeTool === "text" ? "" : "",
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

  // Dynamic Cursor Feedback (shows move, resize, or crosshair)
  if (!PDFEditorState.isDrawing && !PDFEditorState.isDragging && !PDFEditorState.isResizing) {
    let cursor = (PDFEditorState.activeTool === "select") ? "default" : "crosshair";
    
    // Check if hovering over handles of selected item
    if (PDFEditorState.selectedId) {
      const selected = items.find(i => i.id === PDFEditorState.selectedId);
      if (selected) {
        const handle = getHitResizeHandle(selected, rawX, rawY, rectWidth, rectHeight);
        if (handle) cursor = `${handle}-resize`;
      }
    }

    // Check if hovering over any existing item body
    if (cursor === "default" || cursor === "crosshair") {
      for (let i = items.length - 1; i >= 0; i--) {
        const it = items[i];
        if (normX >= it.x && normX <= it.x + it.width &&
            normY >= it.y && normY <= it.y + it.height) {
          cursor = (it.type === "text") ? "text" : "move";
          break;
        }
      }
    }

    if (el.annotationOverlayCanvas) el.annotationOverlayCanvas.style.cursor = cursor;
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

  // B. Handling Dragging Existing Box (Smooth movement)
  if (PDFEditorState.isDragging && PDFEditorState.selectedId) {
    const item = items.find(i => i.id === PDFEditorState.selectedId);
    if (!item || !PDFEditorState.itemStartCoords) return;

    const deltaX = normX - PDFEditorState.dragStartX;
    const deltaY = normY - PDFEditorState.dragStartY;

    item.x = Math.max(0, Math.min(1 - item.width, PDFEditorState.itemStartCoords.x + deltaX));
    item.y = Math.max(0, Math.min(1 - item.height, PDFEditorState.itemStartCoords.y + deltaY));
    redrawAnnotations();
    updateFloatingToolbarPosition();
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
    updateFloatingToolbarPosition();
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
      // If it was a single click on text tool, create a standard comfortable text box
      if (item.type === "text") {
        item.width = 0.22;
        item.height = 0.045;
        PDFEditorState.selectedId = item.id;
        saveStateSnapshot();
        redrawAnnotations();
        updateUIControls();
        startInlineEditing(item.id);
        return;
      }
      const items = getCurrentPageAnnotations();
      const idx = items.indexOf(item);
      if (idx !== -1) items.splice(idx, 1);
    } else {
      // Ensure text boxes have a comfortable size
      if (item.type === "text") {
        item.width = Math.max(item.width, 0.16);
        item.height = Math.max(item.height, 0.04);
      }
      PDFEditorState.selectedId = item.id;
      saveStateSnapshot();

      // IF TEXT: start inline editing directly on the document without a blocking modal!
      if (item.type === "text") {
        redrawAnnotations();
        updateUIControls();
        startInlineEditing(item.id);
        return;
      }
    }

    redrawAnnotations();
    updateUIControls();
    updateFloatingToolbarPosition();
    return;
  }

  if (PDFEditorState.isDragging || PDFEditorState.isResizing) {
    PDFEditorState.isDragging = false;
    PDFEditorState.isResizing = false;
    PDFEditorState.resizeCorner = null;
    PDFEditorState.itemStartCoords = null;
    saveStateSnapshot();
    updateUIControls();
    updateFloatingToolbarPosition();
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
      if (it.type === "text") {
        startInlineEditing(it.id);
      } else {
        openTextModalForItem(it);
      }
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
// DIRECT INLINE TEXT EDITING (Natural, in-place typing on document)
// =========================================================================

function startInlineEditing(id) {
  if (!id) return;
  const items = getCurrentPageAnnotations();
  const item = items.find(i => i.id === id);
  if (!item) return;

  PDFEditorState.editingInlineId = id;
  hideFloatingToolbar();

  const overlay = el.annotationOverlayCanvas || document.getElementById("annotationOverlayCanvas");
  const inline = el.inlineTextEditor || document.getElementById("inlineTextEditor");
  if (!overlay || !inline) return;

  const rect = overlay.getBoundingClientRect();
  const fontScale = rect.width / 800;
  const fontSizePx = Math.max(10, (item.fontSize || 10) * fontScale);

  const left = item.x * rect.width;
  const top = item.y * rect.height;
  const width = Math.max(120, item.width * rect.width);
  const height = Math.max(38, item.height * rect.height);

  inline.style.left = `${left}px`;
  inline.style.top = `${top}px`;
  inline.style.width = `${width}px`;
  inline.style.height = `${height}px`;
  inline.style.fontSize = `${fontSizePx}px`;
  inline.style.fontFamily = item.fontFamily || "Helvetica";
  inline.style.fontWeight = item.isBold ? "bold" : "normal";
  inline.style.color = item.color || "#000000";
  inline.style.backgroundColor = item.backgroundColor || "#FFFFFF";
  inline.value = item.text || "";
  inline.placeholder = "Type correction here...";
  inline.style.display = "block";

  inline.focus();
  inline.select();
  redrawAnnotations();
}

function commitInlineTextEditing() {
  if (!PDFEditorState.editingInlineId) return;
  const inline = el.inlineTextEditor || document.getElementById("inlineTextEditor");
  const items = getCurrentPageAnnotations();
  const item = items.find(i => i.id === PDFEditorState.editingInlineId);

  if (item && inline) {
    const newText = inline.value.trim();
    if (newText) {
      item.text = newText;
      // Auto-fit box if text became longer
      autoFitItemToText(item);
    }
  }

  if (inline) inline.style.display = "none";
  PDFEditorState.editingInlineId = null;
  saveStateSnapshot();
  redrawAnnotations();
  updateUIControls();
  updateFloatingToolbarPosition();
}

function cancelInlineTextEditing() {
  const inline = el.inlineTextEditor || document.getElementById("inlineTextEditor");
  if (inline) inline.style.display = "none";
  PDFEditorState.editingInlineId = null;
  redrawAnnotations();
  updateFloatingToolbarPosition();
}

// Floating Action Bar positioning right above selected annotation
function updateFloatingToolbarPosition() {
  const toolbar = el.floatingItemToolbar || document.getElementById("floatingItemToolbar");
  if (!toolbar) return;

  if (!PDFEditorState.selectedId || PDFEditorState.isPreviewMode || PDFEditorState.editingInlineId) {
    toolbar.style.display = "none";
    return;
  }

  const items = getCurrentPageAnnotations();
  const item = items.find(i => i.id === PDFEditorState.selectedId);
  const overlay = el.annotationOverlayCanvas || document.getElementById("annotationOverlayCanvas");
  if (!item || !overlay) {
    toolbar.style.display = "none";
    return;
  }

  const rect = overlay.getBoundingClientRect();
  const left = Math.max(8, Math.min(rect.width - 240, item.x * rect.width));
  const top = Math.max(34, item.y * rect.height);

  toolbar.style.left = `${left}px`;
  toolbar.style.top = `${top}px`;
  toolbar.style.display = "flex";

  const editBtn = el.floatBtnEdit || document.getElementById("floatBtnEdit");
  if (editBtn) {
    editBtn.style.display = (item.type === "text") ? "inline-block" : "none";
  }
}

function hideFloatingToolbar() {
  const toolbar = el.floatingItemToolbar || document.getElementById("floatingItemToolbar");
  if (toolbar) toolbar.style.display = "none";
}

function quickStepFontSize(delta) {
  if (!PDFEditorState.selectedId) return;
  const items = getCurrentPageAnnotations();
  const item = items.find(i => i.id === PDFEditorState.selectedId);
  if (!item) return;

  item.fontSize = Math.max(7, Math.min(32, (item.fontSize || 10) + delta));
  autoFitItemToText(item);
  saveStateSnapshot();
  redrawAnnotations();
  syncTextControlsWithItem(item);
  updateFloatingToolbarPosition();
}

function autoFitSelectedBox() {
  if (!PDFEditorState.selectedId) return;
  const items = getCurrentPageAnnotations();
  const item = items.find(i => i.id === PDFEditorState.selectedId);
  if (!item) return;
  autoFitItemToText(item);
  saveStateSnapshot();
  redrawAnnotations();
  updateFloatingToolbarPosition();
  showToast("Box sized to fit text perfectly.", "success");
}

function autoFitItemToText(item) {
  if (!item || !item.text) return;
  const overlay = el.annotationOverlayCanvas || document.getElementById("annotationOverlayCanvas");
  if (!overlay) return;
  const ctx = overlay.getContext("2d");
  const width = overlay.width;
  const height = overlay.height;

  const fontScale = width / 800;
  const fontSizePx = Math.max(8, (item.fontSize || 10) * fontScale);
  ctx.font = `${item.isBold ? "bold" : "normal"} ${fontSizePx}px ${item.fontFamily || "Helvetica"}, sans-serif`;

  const lines = item.text.split("\n");
  let maxLineWidth = 0;
  lines.forEach(l => {
    const w = ctx.measureText(l).width;
    if (w > maxLineWidth) maxLineWidth = w;
  });

  const padX = 10 * fontScale;
  const padY = 8 * fontScale;
  const neededWidthPx = maxLineWidth + (padX * 2);
  const neededHeightPx = (lines.length * fontSizePx * 1.35) + (padY * 2);

  item.width = Math.min(1 - item.x, Math.max(0.06, neededWidthPx / width));
  item.height = Math.min(1 - item.y, Math.max(0.025, neededHeightPx / height));
}

// Helper: Word-wrapping for Canvas 2D with word/character breaks to guarantee text stays inside maxWidth
function getWrappedCanvasLines(ctx, text, maxWidth) {
  const paragraphs = String(text || "").split("\n");
  const lines = [];

  paragraphs.forEach(para => {
    if (para.trim().length === 0) {
      lines.push("");
      return;
    }
    const words = para.split(" ");
    let currentLine = "";

    words.forEach(word => {
      const testLine = currentLine ? `${currentLine} ${word}` : word;
      const testWidth = ctx.measureText(testLine).width;

      if (testWidth <= maxWidth) {
        currentLine = testLine;
      } else {
        if (currentLine) {
          lines.push(currentLine);
          currentLine = word;
        } else {
          // If a single word is longer than maxWidth, break character by character
          let chunk = "";
          for (const char of word) {
            if (ctx.measureText(chunk + char).width <= maxWidth) {
              chunk += char;
            } else {
              if (chunk) lines.push(chunk);
              chunk = char;
            }
          }
          currentLine = chunk;
        }
      }
    });

    if (currentLine) {
      lines.push(currentLine);
    }
  });

  return lines;
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

    // 2. Draw Text if present (STRICTLY CONSTRAINED INSIDE BOX)
    if (it.text && it.text.trim().length > 0) {
      ctx.save();
      // Clip to box boundaries so NO text can ever bleed outside
      ctx.beginPath();
      ctx.rect(x, y, w, h);
      ctx.clip();

      ctx.fillStyle = it.color || "#000000";
      
      const fontScale = width / 800; // Base reference scale
      let fontSizePx = Math.max(7, (it.fontSize || 10) * fontScale);
      const fontWeight = it.isBold ? "bold" : "normal";
      ctx.font = `${fontWeight} ${fontSizePx}px ${it.fontFamily || "Helvetica"}, sans-serif`;
      ctx.textBaseline = "top";

      // Padding inside the box
      const padX = Math.max(2, 4 * fontScale);
      const padY = Math.max(2, 3 * fontScale);
      const maxWidth = Math.max(10, w - (padX * 2));
      const maxHeight = Math.max(10, h - (padY * 2));

      // Calculate wrapped lines
      let lines = getWrappedCanvasLines(ctx, it.text, maxWidth);
      let lineHeight = fontSizePx * 1.25;

      // Auto-scale font size if text exceeds box height
      let neededHeight = lines.length * lineHeight;
      if (neededHeight > maxHeight && fontSizePx > 7) {
        const scaleFactor = Math.max(0.65, maxHeight / neededHeight);
        fontSizePx = Math.max(6, Math.floor(fontSizePx * scaleFactor));
        ctx.font = `${fontWeight} ${fontSizePx}px ${it.fontFamily || "Helvetica"}, sans-serif`;
        lineHeight = fontSizePx * 1.25;
        lines = getWrappedCanvasLines(ctx, it.text, maxWidth);
      }

      // Render lines (clipped to box boundaries by ctx.clip())
      lines.forEach((line, lineIdx) => {
        const lineY = y + padY + (lineIdx * lineHeight);
        if (lineY <= y + h) {
          ctx.fillText(line, x + padX, lineY);
        }
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
      // Clear visual dashed border so user easily sees where whiteout is placed in edit mode
      ctx.save();
      ctx.strokeStyle = "#60A5FA";
      ctx.lineWidth = 1.5;
      ctx.setLineDash([4, 4]);
      ctx.strokeRect(x, y, w, h);
      ctx.restore();
    }
  });

  updateFloatingToolbarPosition();
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
  updateTextFormattingVisibility();
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

  updateTextFormattingVisibility();
}

function updateTextFormattingVisibility() {
  const fmt = document.getElementById("textFormattingControls");
  if (!fmt) return;
  const isTextTool = PDFEditorState.activeTool === "text";
  const items = getCurrentPageAnnotations();
  const selectedItem = items.find(i => i.id === PDFEditorState.selectedId);
  const isTextSelected = selectedItem && (selectedItem.type === "text");

  if (isTextTool || isTextSelected) {
    fmt.style.setProperty("display", "flex", "important");
  } else {
    fmt.style.setProperty("display", "none", "important");
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

function autoFitBoxToText() {
  if (!activeModalItem) return;
  const modalInput = document.getElementById("modalTextInput");
  const modalSize = document.getElementById("modalTextFontSize");
  const modalWeight = document.getElementById("modalTextFontWeight");

  const text = (modalInput && modalInput.value) ? modalInput.value : (activeModalItem.text || "Text");
  const fontSize = modalSize ? parseInt(modalSize.value, 10) : (activeModalItem.fontSize || 10);
  const isBold = modalWeight ? (modalWeight.value === "bold") : activeModalItem.isBold;

  // Use overlay canvas context to measure text
  const overlay = el.annotationOverlayCanvas || document.getElementById("annotationOverlayCanvas");
  if (!overlay) return;
  const ctx = overlay.getContext("2d");
  const width = overlay.width;
  const height = overlay.height;

  const fontScale = width / 800;
  const fontSizePx = Math.max(7, fontSize * fontScale);
  ctx.font = `${isBold ? "bold" : "normal"} ${fontSizePx}px ${activeModalItem.fontFamily || "Helvetica"}, sans-serif`;

  const lines = text.split("\n");
  let maxLineWidth = 0;
  lines.forEach(l => {
    const w = ctx.measureText(l).width;
    if (w > maxLineWidth) maxLineWidth = w;
  });

  const padX = 10 * fontScale;
  const padY = 8 * fontScale;
  const neededWidthPx = maxLineWidth + (padX * 2);
  const neededHeightPx = (lines.length * fontSizePx * 1.35) + (padY * 2);

  activeModalItem.width = Math.min(1 - activeModalItem.x, Math.max(0.06, neededWidthPx / width));
  activeModalItem.height = Math.min(1 - activeModalItem.y, Math.max(0.025, neededHeightPx / height));
  activeModalItem.text = text;
  activeModalItem.fontSize = fontSize;
  activeModalItem.isBold = isBold;

  redrawAnnotations();
  updateUIControls();
  showToast("Box resized to fit text cleanly.", "success");
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

// Helper: Word-wrapping for pdf-lib vector PDF export
function getWrappedPdfLines(text, font, fontSize, maxWidth) {
  // Sanitize text for standard PDF WinAnsi font compatibility
  const sanitized = String(text || "")
    .replace(/[\u2018\u2019]/g, "'")
    .replace(/[\u201C\u201D]/g, '"')
    .replace(/[\u2013\u2014]/g, "-")
    .replace(/[\u2022]/g, "*")
    .replace(/[\u00A0]/g, " ")
    .replace(/[^\x00-\x7F]/g, "");

  const paragraphs = sanitized.split("\n");
  const lines = [];

  paragraphs.forEach(para => {
    if (para.trim().length === 0) {
      lines.push("");
      return;
    }
    const words = para.split(" ");
    let currentLine = "";

    words.forEach(word => {
      const testLine = currentLine ? `${currentLine} ${word}` : word;
      let testWidth = 0;
      try {
        testWidth = font.widthOfTextAtSize(testLine, fontSize);
      } catch (e) {
        testWidth = testLine.length * (fontSize * 0.55);
      }

      if (testWidth <= maxWidth) {
        currentLine = testLine;
      } else {
        if (currentLine) {
          lines.push(currentLine);
          currentLine = word;
        } else {
          // Word longer than maxWidth: break character by character
          let chunk = "";
          for (const char of word) {
            let chunkWidth = 0;
            try {
              chunkWidth = font.widthOfTextAtSize(chunk + char, fontSize);
            } catch (e) {
              chunkWidth = (chunk.length + 1) * (fontSize * 0.55);
            }
            if (chunkWidth <= maxWidth) {
              chunk += char;
            } else {
              if (chunk) lines.push(chunk);
              chunk = char;
            }
          }
          currentLine = chunk;
        }
      }
    });

    if (currentLine) {
      lines.push(currentLine);
    }
  });

  return lines;
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
    
    // Load fresh copy of PDF bytes
    const pdfDoc = await PDFLib.PDFDocument.load(PDFEditorState.originalBytes.slice(0));
    
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

        // B. Draw Text if present (STRICTLY CONSTRAINED INSIDE BOX)
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
          let fontSize = Math.max(6, ann.fontSize || 10);
          const padX = 3;
          const padY = 3;
          const maxWidth = Math.max(8, pdfW - (padX * 2));
          const maxHeight = Math.max(8, pdfH - (padY * 2));

          // Word-wrap lines for PDF font
          let lines = getWrappedPdfLines(ann.text, chosenFont, fontSize, maxWidth);
          let lineStep = fontSize * 1.25;

          // Auto-scale font down if text height exceeds box height
          let neededHeight = lines.length * lineStep;
          if (neededHeight > maxHeight && fontSize > 6) {
            const scaleFactor = Math.max(0.65, maxHeight / neededHeight);
            fontSize = Math.max(5.5, Math.floor(fontSize * scaleFactor * 10) / 10);
            lineStep = fontSize * 1.25;
            lines = getWrappedPdfLines(ann.text, chosenFont, fontSize, maxWidth);
          }

          // Starting from top of the box downwards
          let currentY = pdfY + pdfH - fontSize - padY;

          for (const line of lines) {
            if (line.trim().length > 0 && currentY >= pdfY - 2) {
              try {
                page.drawText(line, {
                  x: pdfX + padX,
                  y: currentY,
                  size: fontSize,
                  font: chosenFont,
                  color: textColor
                });
              } catch (drawErr) {
                console.warn("Could not draw line in PDF:", line, drawErr);
              }
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
    
    // Revoke object URL safely after 60s so Chrome/Edge downloads never fail
    setTimeout(() => {
      if (link.parentNode) link.parentNode.removeChild(link);
      URL.revokeObjectURL(downloadUrl);
    }, 60000);

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

// =========================================================================
// 10. CONSIGNERS (CONSIGNOR) INTEGRATION FOR PDF STAMPING & AMENDMENTS
// =========================================================================

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
      region: "Morbi"
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
      region: "Morbi"
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
      region: "Morbi"
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
      region: "Jodhpur"
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
      <a class="dropdown-item py-2 d-flex align-items-center justify-content-between" href="#" onclick="applyConsignerStamp('${escapeHtml(c.id)}'); return false;">
        <div class="overflow-hidden pe-2">
          <div class="fw-bold text-dark text-truncate" style="max-width:200px;">${escapeHtml(c.name)}</div>
          <div class="text-muted small" style="font-size:0.7rem;">IEC: ${escapeHtml(c.iec)} • ${escapeHtml(c.region || 'Morbi')}</div>
        </div>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:0.65rem;">Apply</span>
      </a>
    </li>
  `).join("");

  menu.innerHTML = `
    <li><h6 class="dropdown-header">Stamp Verified Consigner</h6></li>
    ${itemsHtml}
    <li><hr class="dropdown-divider"></li>
    <li>
      <a class="dropdown-item d-flex align-items-center gap-2 text-primary fw-semibold" href="consigners.php">
        <i class="bi bi-gear"></i> Manage Consigners (CRUD) &rarr;
      </a>
    </li>
  `;

  populateSidebarConsigners();
}

function populateSidebarConsigners() {
  const listEl = document.getElementById("sidebarConsignersList");
  if (!listEl) return;
  const consigners = getRegisteredConsigners();
  const activeConsigners = consigners.filter(c => c.status !== "Inactive");

  listEl.innerHTML = activeConsigners.map(c => `
    <div class="card border p-2 shadow-xs mb-1 bg-white hover-shadow" style="border-radius:8px; cursor:pointer;" onclick="applyConsignerStamp('${escapeHtml(c.id)}')">
      <div class="d-flex align-items-center justify-content-between mb-1">
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:0.68rem;">${escapeHtml(c.region || 'Morbi')}</span>
        <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size:0.7rem;"><i class="bi bi-patch-plus me-1"></i>Stamp</button>
      </div>
      <div class="fw-bold small text-dark text-truncate" title="${escapeHtml(c.name)}">${escapeHtml(c.name)}</div>
      <div class="text-muted small" style="font-size:0.7rem;">IEC: ${escapeHtml(c.iec)} &bull; GSTIN: ${escapeHtml(c.gstin)}</div>
    </div>
  `).join("");
}

function switchInspectorTab(tabName) {
  const btnCorrections = document.getElementById("tabCorrectionsBtn");
  const btnPresets = document.getElementById("tabPresetsBtn");
  const btnConsigners = document.getElementById("tabConsignersBtn");

  const paneCorrections = document.getElementById("tabPaneCorrections");
  const panePresets = document.getElementById("tabPanePresets");
  const paneConsigners = document.getElementById("tabPaneConsigners");

  [btnCorrections, btnPresets, btnConsigners].forEach(b => b && b.classList.remove("active"));
  [paneCorrections, panePresets, paneConsigners].forEach(p => p && p.classList.add("d-none"));

  if (tabName === "corrections") {
    if (btnCorrections) btnCorrections.classList.add("active");
    if (paneCorrections) paneCorrections.classList.remove("d-none");
  } else if (tabName === "presets") {
    if (btnPresets) btnPresets.classList.add("active");
    if (panePresets) panePresets.classList.remove("d-none");
  } else if (tabName === "consigners") {
    if (btnConsigners) btnConsigners.classList.add("active");
    if (paneConsigners) paneConsigners.classList.remove("d-none");
    populateSidebarConsigners();
  }
}

function setCanvasDeskTheme(theme) {
  const wrapper = document.getElementById("pdfViewportWrapper");
  if (!wrapper) return;

  wrapper.classList.remove("theme-light", "theme-blueprint");

  const btnStudio = document.getElementById("themeStudioBtn");
  const btnLight = document.getElementById("themeLightBtn");
  const btnBlueprint = document.getElementById("themeBlueprintBtn");
  [btnStudio, btnLight, btnBlueprint].forEach(b => b && b.classList.remove("active"));

  if (theme === "light") {
    wrapper.classList.add("theme-light");
    if (btnLight) btnLight.classList.add("active");
    showToast("Switched to Light Drafting Desk", "info");
  } else if (theme === "blueprint") {
    wrapper.classList.add("theme-blueprint");
    if (btnBlueprint) btnBlueprint.classList.add("active");
    showToast("Switched to Customs Blueprint Desk", "info");
  } else {
    if (btnStudio) btnStudio.classList.add("active");
    showToast("Switched to Slate Studio Desk", "info");
  }
}

function applyConsignerStamp(consignerId) {
  const consigners = getRegisteredConsigners();
  const c = consigners.find(item => item.id === consignerId);
  if (!c) {
    showToast("Consigner record not found", "warning");
    return;
  }

  const stampLines = [
    c.name.toUpperCase(),
    c.factoryAddress || c.registeredAddress,
    `IEC: ${c.iec} (BRANCH: ${c.iecBranch || '0'}) | GSTIN: ${c.gstin} | AD CODE: ${c.adCode || '6390013'}`,
    `BANK: ${c.forexBank || 'ICICI BANK LIMITED, JODHPUR / MORBI BRANCH'}`
  ];

  const text = stampLines.join("\n");

  if (!PDFEditorState.annotations[PDFEditorState.currentPage]) {
    PDFEditorState.annotations[PDFEditorState.currentPage] = [];
  }

  // Create clean Consigner overlay box at the standard Exporter location (top left of customs document)
  const newAnnotation = {
    id: "ann_consigner_" + Date.now(),
    type: "text",
    x: 0.05,
    y: 0.08,
    width: 0.88,
    height: 0.09,
    text: text,
    fontFamily: "Courier",
    fontSize: 8.5,
    isBold: true,
    color: "#0F172A",
    backgroundColor: "#FFFFFF"
  };

  PDFEditorState.annotations[PDFEditorState.currentPage].push(newAnnotation);
  saveStateSnapshot();
  redrawAnnotations();
  updateUIControls();

  showToast(`Stamped Consigner "${c.name}" on Page ${PDFEditorState.currentPage}`, "success");
}

function checkPendingConsignerStamp() {
  try {
    const raw = sessionStorage.getItem("reliance_stamp_consigner");
    if (!raw) return;

    const consigner = JSON.parse(raw);
    sessionStorage.removeItem("reliance_stamp_consigner");

    setTimeout(() => {
      applyConsignerStamp(consigner.id);
      showToast(`Automatically loaded and stamped ${consigner.name}!`, "success");
    }, 800);
  } catch (e) {
    console.warn("Could not check pending consigner stamp:", e);
  }
}
