<?php
// Reliance Granito Export Documentation Management System (EDMS)
// Interactive Visual PDF Editor & Redactor - Executive Studio Edition
$pageTitle = "Reliance Granito | PDF Visual Correction & Masking Editor";
$activeNav = "pdf-editor";
include_once __DIR__ . '/includes/header.php';
?>

<!-- Activate Studio Mode on Body -->
<script>document.body.classList.add('pdf-studio-mode');</script>

<!-- Full-Bleed Studio Workspace App Container -->
<main class="pdf-studio-container" id="pdfStudioApp">

  <!-- =========================================================================
       TIER 1: DOCUMENT METADATA & PRIMARY ACTIONS TOPBAR (52px)
       ========================================================================= -->
  <header class="pdf-studio-topbar" id="pdfStudioTopBar">
    <!-- Left: Document Status & Info -->
    <div class="d-flex align-items-center gap-2">
      <div class="d-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-2" style="width:34px; height:34px;">
        <i class="bi bi-file-earmark-pdf-fill fs-5"></i>
      </div>
      
      <div class="d-flex align-items-center gap-2">
        <span class="fw-bold text-dark text-truncate" id="docTitle" style="max-width: 280px; font-size: 0.92rem;">No PDF Loaded</span>
        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" id="docStatusBadge" style="font-size:0.75rem;">Waiting for file</span>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" id="editsCountBadge" style="display:none; font-size:0.75rem;">0 Corrections</span>
      </div>

      <div class="vr mx-2 text-secondary opacity-25" style="height:22px;"></div>

      <!-- Quick Sample Document Switcher -->
      <div class="dropdown">
        <button class="btn btn-outline-secondary btn-sm dropdown-toggle py-1 px-2 d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" id="samplePickerBtn" style="font-size:0.8rem; border-color:#CBD5E1;">
          <i class="bi bi-folder2-open text-primary"></i>
          <span>Sample Documents</span>
        </button>
        <ul class="dropdown-menu shadow-sm" id="sampleDropdownMenu" style="font-size:0.83rem; min-width:280px;">
          <li><h6 class="dropdown-header">Export Consignment 1469 Files</h6></li>
          <li><a class="dropdown-item d-flex align-items-center gap-2" href="#" onclick="loadSampleDoc('1469 SB ORIGINAL.PDF', 'Shipping Bill #1469 (Original Customs EDI)'); return false;"><i class="bi bi-file-earmark-text text-danger"></i> 1469 SB ORIGINAL.pdf <span class="badge bg-light text-dark ms-auto">Customs EDI</span></a></li>
          <li><a class="dropdown-item d-flex align-items-center gap-2" href="#" onclick="loadSampleDoc('1469 INVOICE ORIGINAL.pdf', 'Commercial Invoice #1469 (Original)'); return false;"><i class="bi bi-receipt text-danger"></i> 1469 INVOICE ORIGINAL.pdf <span class="badge bg-light text-dark ms-auto">Commercial</span></a></li>
          <li><a class="dropdown-item d-flex align-items-center gap-2" href="#" onclick="loadSampleDoc('1469 BL.pdf', 'Bill of Lading #1469 (Original)'); return false;"><i class="bi bi-box-seam text-danger"></i> 1469 BL.pdf <span class="badge bg-light text-dark ms-auto">B/L Carrier</span></a></li>
          <li><hr class="dropdown-divider"></li>
          <li><h6 class="dropdown-header">Reference Masked Copies</h6></li>
          <li><a class="dropdown-item d-flex align-items-center gap-2" href="#" onclick="loadSampleDoc('1469 SB HIDE.pdf', 'Shipping Bill (Masked / HIDE Reference)'); return false;"><i class="bi bi-eye-slash text-warning"></i> 1469 SB HIDE.pdf</a></li>
          <li><a class="dropdown-item d-flex align-items-center gap-2" href="#" onclick="loadSampleDoc('BL HIDE.pdf', 'Bill of Lading (Masked Reference)'); return false;"><i class="bi bi-eye-slash text-warning"></i> BL HIDE.pdf</a></li>
        </ul>
      </div>
    </div>

    <!-- Center: Enterprise Statutory Notice -->
    <div class="d-none d-xl-flex align-items-center gap-2 text-muted" style="font-size:0.78rem;">
      <i class="bi bi-shield-check text-success fs-6"></i>
      <span>DGFT & Customs Compliant Masking Engine</span>
      <span class="text-secondary opacity-50">&bull;</span>
      <span>Morbi Facility & Jodhpur Unit</span>
    </div>

    <!-- Right: Upload, Download & Secondary Actions -->
    <div class="d-flex align-items-center gap-2">
      <!-- Hidden file input -->
      <input type="file" id="pdfFileInput" accept="application/pdf" style="display:none;" onchange="handleFileUpload(event)">
      
      <!-- Upload Button -->
      <button class="btn btn-outline-secondary btn-sm py-1 px-3 d-flex align-items-center gap-2" onclick="document.getElementById('pdfFileInput').click();" style="font-size:0.82rem; border-color:#CBD5E1;">
        <i class="bi bi-cloud-arrow-up text-primary"></i>
        <span>Upload PDF</span>
      </button>

      <!-- Download Clean Vector PDF Button -->
      <button class="btn btn-primary btn-sm py-1 px-3 d-flex align-items-center gap-2 fw-semibold shadow-sm" id="btnDownloadPdf" onclick="downloadUpdatedPdf()" disabled title="Make corrections to enable download" style="font-size:0.82rem;">
        <i class="bi bi-download"></i>
        <span>Export Clean PDF</span>
      </button>
    </div>
  </header>

  <!-- =========================================================================
       TIER 2: STUDIO TOOLS RIBBON (48px)
       ========================================================================= -->
  <div class="pdf-studio-toolribbon" id="pdfStudioToolRibbon">
    <!-- Left: Primary Annotation & Modification Tools -->
    <div class="d-flex align-items-center gap-1">
      <button type="button" class="studio-tool-btn active" id="toolSelect" onclick="setEditorTool('select')" title="Select, Move & Resize Corrections (V)">
        <i class="bi bi-cursor-fill text-primary"></i> Select
      </button>
      
      <button type="button" class="studio-tool-btn" id="toolWhiteout" onclick="setEditorTool('whiteout')" title="Draw Whiteout / Clean Mask Box (W)">
        <i class="bi bi-square-fill text-warning"></i> Whiteout
      </button>

      <button type="button" class="studio-tool-btn" id="toolText" onclick="setEditorTool('text')" title="Whiteout + Replacement Text on PDF (T)">
        <i class="bi bi-fonts text-primary"></i> Replace Text
      </button>

      <button type="button" class="studio-tool-btn" id="toolRedact" onclick="setEditorTool('redact')" title="Draw Solid Blackout Redaction Bar (R)">
        <i class="bi bi-square-fill text-dark"></i> Redact
      </button>

      <div class="vr mx-1 text-secondary opacity-25" style="height:20px;"></div>

      <!-- Quick Stamp Dropdown -->
      <div class="dropdown">
        <button type="button" class="studio-tool-btn dropdown-toggle" data-bs-toggle="dropdown" id="stampDropdownBtn" title="Apply Official Export Stamp">
          <i class="bi bi-patch-check-fill text-danger"></i> Stamp
        </button>
        <ul class="dropdown-menu shadow-sm" style="font-size:0.82rem;">
          <li><a class="dropdown-item fw-bold text-danger" href="#" onclick="applyStamp('AMENDED'); return false;">[ AMENDED ]</a></li>
          <li><a class="dropdown-item fw-bold text-success" href="#" onclick="applyStamp('VERIFIED'); return false;">[ VERIFIED & MATCHED ]</a></li>
          <li><a class="dropdown-item fw-bold text-primary" href="#" onclick="applyStamp('CUSTOMS CLEARED'); return false;">[ CUSTOMS CLEARED ]</a></li>
          <li><a class="dropdown-item fw-bold text-warning" href="#" onclick="applyStamp('CONFIDENTIAL'); return false;">[ CONFIDENTIAL ]</a></li>
        </ul>
      </div>

      <!-- Consigner Particulars Dropdown -->
      <div class="dropdown">
        <button type="button" class="studio-tool-btn dropdown-toggle" data-bs-toggle="dropdown" id="consignerDropdownBtn" onclick="initConsignersDropdown()" title="Stamp Registered Consigner Particulars (IEC, GSTIN, Address)">
          <i class="bi bi-building-up text-primary"></i> Consigner
        </button>
        <ul class="dropdown-menu shadow-sm" id="consignerDropdownMenu" style="font-size:0.83rem; min-width:280px;">
          <li><h6 class="dropdown-header">Stamp Registered Consigner</h6></li>
          <li id="consignerListItems"><span class="dropdown-item small text-muted">Loading Consigners...</span></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item d-flex align-items-center gap-2 text-primary fw-semibold" href="consigners.php"><i class="bi bi-gear"></i> Manage Consigners (CRUD) &rarr;</a></li>
        </ul>
      </div>
    </div>

    <!-- Center: Dynamic Inline Text Formatting Controls (Visible when Text tool is active) -->
    <div class="studio-text-subbar" id="textFormattingControls" style="display:none !important;">
      <span class="badge bg-primary text-white px-2 py-1" style="font-size:0.68rem; letter-spacing: 0.04em;">
        TEXT
      </span>

      <select class="form-select form-select-sm py-0 border-0 bg-white" id="textFontFamily" onchange="updateSelectedTextProperties()" title="Font Family" style="width:130px; font-size:0.78rem; height:26px;">
        <option value="Helvetica">Helvetica</option>
        <option value="Courier">Courier (EDI)</option>
        <option value="Times">Times</option>
      </select>

      <select class="form-select form-select-sm py-0 border-0 bg-white" id="textFontSize" onchange="updateSelectedTextProperties()" title="Font Size" style="width:85px; font-size:0.78rem; height:26px;">
        <option value="8">8 pt</option>
        <option value="9">9 pt</option>
        <option value="10" selected>10 pt</option>
        <option value="11">11 pt</option>
        <option value="12">12 pt</option>
        <option value="14">14 pt</option>
        <option value="16">16 pt</option>
        <option value="20">20 pt</option>
      </select>

      <button type="button" class="btn btn-sm btn-white bg-white border py-0 px-2" id="textBoldBtn" onclick="toggleBoldText()" title="Toggle Bold" style="height:26px;">
        <i class="bi bi-type-bold"></i>
      </button>

      <div class="d-flex align-items-center gap-1">
        <input type="color" class="form-control form-control-color form-control-sm p-0 rounded border-0" id="textColorPicker" value="#000000" title="Text Color" onchange="updateSelectedTextProperties()" style="width:24px; height:24px; cursor:pointer;">
      </div>

      <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2 fw-semibold" style="font-size:0.75rem; height:26px;" onclick="autoFitSelectedBox()" title="Fit Box to Text">
        <i class="bi bi-arrows-angle-expand me-1"></i>Fit
      </button>
    </div>

    <!-- Right: Desk Theme, Undo/Redo & Preview -->
    <div class="d-flex align-items-center gap-2 ms-auto">
      <!-- Desk Theme Selector -->
      <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-1 py-1" type="button" data-bs-toggle="dropdown" title="Change Studio Desk Backdrop" style="font-size:0.8rem; border-color:#CBD5E1;">
          <i class="bi bi-palette text-primary"></i>
          <span>Desk</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size:0.82rem;">
          <li><h6 class="dropdown-header">Canvas Desk Backdrop</h6></li>
          <li><a class="dropdown-item active" href="#" id="themeStudioBtn" onclick="setCanvasDeskTheme('studio'); return false;"><i class="bi bi-circle-fill text-dark me-2"></i>Dark Slate Studio (Default)</a></li>
          <li><a class="dropdown-item" href="#" id="themeLightBtn" onclick="setCanvasDeskTheme('light'); return false;"><i class="bi bi-circle-fill text-secondary me-2"></i>Light Drafting Desk</a></li>
          <li><a class="dropdown-item" href="#" id="themeBlueprintBtn" onclick="setCanvasDeskTheme('blueprint'); return false;"><i class="bi bi-circle-fill text-primary me-2"></i>Customs Blueprint</a></li>
        </ul>
      </div>

      <!-- Preview Final PDF Toggle -->
      <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 py-1" id="btnPreviewToggle" onclick="toggleHidePreview()" title="Preview Final Masked PDF" style="font-size:0.8rem; border-color:#CBD5E1;">
        <i class="bi bi-eye"></i>
        <span>Preview</span>
      </button>

      <!-- Undo / Redo -->
      <div class="btn-group btn-group-sm">
        <button class="btn btn-outline-secondary py-1" id="btnUndo" onclick="undoEdit()" disabled title="Undo (Ctrl+Z)" style="border-color:#CBD5E1;"><i class="bi bi-arrow-counterclockwise"></i></button>
        <button class="btn btn-outline-secondary py-1" id="btnRedo" onclick="redoEdit()" disabled title="Redo (Ctrl+Y)" style="border-color:#CBD5E1;"><i class="bi bi-arrow-clockwise"></i></button>
      </div>
    </div>
  </div>

  <!-- =========================================================================
       TIER 3: MAIN SPLIT STUDIO WORKSPACE (CANVAS + DOCKED INSPECTOR)
       ========================================================================= -->
  <div class="pdf-studio-split-body" id="pdfStudioSplitWorkspace">
    
    <!-- Left / Center: Canvas Desk Viewport Shell -->
    <div class="pdf-studio-canvas-pane" id="pdfStudioCanvasPane">
      
      <!-- Scrollable Studio Desk Environment -->
      <div class="pdf-viewport-wrapper text-center position-relative" id="pdfViewportWrapper" style="flex:1; height:100%; overflow:auto;">
        
        <!-- Loading State Overlay -->
        <div id="pdfLoadingState" class="position-absolute top-50 start-50 translate-middle text-center p-4" style="display:none; z-index: 200; background:rgba(255,255,255,0.95); border-radius:12px; box-shadow:0 12px 35px rgba(0,0,0,0.3);">
          <div class="spinner-border text-primary mb-2" role="status"></div>
          <div class="fw-bold text-dark" id="loadingText">Rendering PDF Page...</div>
          <div class="small text-muted">Please wait while high-resolution vectors load</div>
        </div>

        <!-- Empty Welcome State -->
        <div id="pdfEmptyState" class="p-5 text-center my-auto" style="z-index: 10;">
          <div class="d-inline-flex p-4 rounded-circle bg-white shadow-lg text-primary mb-3">
            <i class="bi bi-file-earmark-pdf fs-1"></i>
          </div>
          <h4 class="fw-bold text-white mb-2">No Document Opened</h4>
          <p class="text-white-50 mx-auto mb-4" style="max-width: 480px; font-size: 0.9rem;">
            Choose a sample export document from the top bar (Shipping Bill, Commercial Invoice, or Bill of Lading) or upload a local PDF to start masking and correcting values.
          </p>
          <div class="d-flex justify-content-center gap-2">
            <button class="btn btn-primary shadow-sm" onclick="loadSampleDoc('1469 SB ORIGINAL.PDF', 'Shipping Bill #1469')">
              <i class="bi bi-lightning-charge me-1"></i> Quick Load Shipping Bill 1469
            </button>
            <button class="btn btn-light shadow-sm" onclick="document.getElementById('pdfFileInput').click();">
              <i class="bi bi-upload me-1"></i> Upload File
            </button>
          </div>
        </div>

        <!-- Stacked PDF Canvas & Interactive Annotation Canvas (Genuine Paper Sheet) -->
        <div id="canvasContainer" class="d-inline-block position-relative" style="display:none !important; user-select:none; margin: 30px auto 120px auto !important;">
          <!-- Base PDF.js Canvas -->
          <canvas id="pdfRenderCanvas" style="display:block;"></canvas>
          <!-- Interactive Drawing & Transform Overlay Canvas -->
          <canvas id="annotationOverlayCanvas" class="position-absolute top-0 start-0" style="touch-action:none;"></canvas>

          <!-- Direct Inline Text Editor (type directly onto document in-place) -->
          <textarea id="inlineTextEditor" class="position-absolute shadow border border-primary rounded-1" style="display:none; z-index:50; resize:both; outline:2px solid #2563EB; padding:4px 6px; background:#FFFFFF; overflow:hidden; font-family:Helvetica; line-height:1.25;"></textarea>

          <!-- Floating Quick Action Bar for selected item -->
          <div id="floatingItemToolbar" class="position-absolute bg-white shadow-lg border rounded-pill px-2 py-1 d-flex align-items-center gap-1" style="display:none; z-index:60; transform: translateY(-130%); font-size:0.75rem;">
            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill fw-semibold" id="floatBtnEdit" onclick="startInlineEditing(PDFEditorState.selectedId)" title="Edit Text Directly"><i class="bi bi-pencil-fill me-1"></i>Edit Text</button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 rounded-pill" onclick="quickStepFontSize(-1)" title="Decrease Font Size"><i class="bi bi-dash fs-6"></i></button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 rounded-pill" onclick="quickStepFontSize(1)" title="Increase Font Size"><i class="bi bi-plus fs-6"></i></button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 rounded-pill" onclick="autoFitSelectedBox()" title="Fit Box to Text"><i class="bi bi-arrows-angle-expand me-1"></i>Fit</button>
            <div class="vr my-1"></div>
            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 rounded-pill" onclick="deleteAnnotationById(PDFEditorState.selectedId)" title="Delete This Correction"><i class="bi bi-trash-fill"></i></button>
          </div>
        </div>

      </div>

      <!-- Permanently Floating Studio Dock (Pinned at Bottom Center) -->
      <div class="studio-floating-dock" id="studioFloatingDock">
        <!-- Page Pager -->
        <button class="studio-dock-btn" id="btnPrevPage" onclick="changePage(-1)" disabled title="Previous Page">
          <i class="bi bi-chevron-left"></i>
        </button>
        <span class="studio-dock-label" id="pageIndicator">Page 1 / 1</span>
        <button class="studio-dock-btn" id="btnNextPage" onclick="changePage(1)" disabled title="Next Page">
          <i class="bi bi-chevron-right"></i>
        </button>

        <div class="studio-dock-divider"></div>

        <!-- Zoom Controls -->
        <button class="studio-dock-btn" onclick="adjustZoom(-0.15)" title="Zoom Out">
          <i class="bi bi-zoom-out"></i>
        </button>
        <span class="studio-dock-label" id="zoomIndicator">100%</span>
        <button class="studio-dock-btn" onclick="adjustZoom(0.15)" title="Zoom In">
          <i class="bi bi-zoom-in"></i>
        </button>
        <button class="studio-dock-btn" onclick="resetZoomFit()" title="Fit to Width">
          <i class="bi bi-arrows-expand"></i>
        </button>
      </div>

    </div>

    <!-- Right: Full-Height Docked Inspector Panel (360px) -->
    <aside class="pdf-studio-inspector-pane" id="pdfStudioInspectorPane">
      
      <!-- Inspector Tab Header -->
      <div class="inspector-tab-nav" role="tablist">
        <button class="inspector-tab-btn active" id="tabCorrectionsBtn" onclick="switchInspectorTab('corrections')">
          <i class="bi bi-layers-half me-1"></i> Edits
          <span class="badge bg-primary rounded-pill ms-1" id="pageEditsCount">0</span>
        </button>
        <button class="inspector-tab-btn" id="tabPresetsBtn" onclick="switchInspectorTab('presets')">
          <i class="bi bi-magic me-1"></i> Presets
        </button>
        <button class="inspector-tab-btn" id="tabConsignersBtn" onclick="switchInspectorTab('consigners')">
          <i class="bi bi-building-up me-1"></i> Consigners
        </button>
      </div>

      <!-- Tab Content Body (Smooth Internal Scroll) -->
      <div class="pdf-studio-inspector-content">
        
        <!-- Tab 1: Corrections List -->
        <div id="tabPaneCorrections">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="small text-muted fw-bold" style="font-size:0.75rem;">CURRENT PAGE CORRECTIONS</span>
            <button class="btn btn-link btn-sm text-danger p-0" id="btnClearPage" onclick="clearCurrentPageEdits()" disabled style="font-size:0.75rem; text-decoration:none;">
              <i class="bi bi-trash"></i> Clear All
            </button>
          </div>
          
          <div id="annotationsListContainer">
            <div class="text-center text-muted p-4 small" id="noEditsNotice">
              <i class="bi bi-pencil-slash d-block fs-3 mb-2 text-secondary opacity-50"></i>
              No corrections on this page yet.<br>Click & drag with <strong>Whiteout</strong> or <strong>Replace Text</strong> tool.
            </div>
            <div class="list-group list-group-flush" id="annotationsList"></div>
          </div>
        </div>

        <!-- Tab 2: Standard Presets -->
        <div class="d-none" id="tabPanePresets">
          <p class="small text-muted mb-3" style="font-size:0.8rem;">1-Click standard export document redactions & amendments:</p>
          <div class="d-grid gap-2">
            <button class="btn btn-outline-secondary btn-sm text-start d-flex align-items-center justify-content-between p-2" onclick="applyPreset('mask_rates')">
              <div>
                <div class="fw-semibold text-dark"><i class="bi bi-currency-dollar text-danger me-1"></i> Mask Item Rates</div>
                <div class="small text-muted" style="font-size:0.72rem;">Whiteout FOB unit rates for customs</div>
              </div>
              <i class="bi bi-plus-circle text-primary fs-6"></i>
            </button>
            <button class="btn btn-outline-secondary btn-sm text-start d-flex align-items-center justify-content-between p-2" onclick="applyPreset('amend_haiphong')">
              <div>
                <div class="fw-semibold text-dark"><i class="bi bi-geo-alt text-success me-1"></i> Amend Destination</div>
                <div class="small text-muted" style="font-size:0.72rem;">Replace buyer address with Hai Phong Port</div>
              </div>
              <i class="bi bi-plus-circle text-primary fs-6"></i>
            </button>
            <button class="btn btn-outline-secondary btn-sm text-start d-flex align-items-center justify-content-between p-2" onclick="applyPreset('mask_freight')">
              <div>
                <div class="fw-semibold text-dark"><i class="bi bi-shield-lock text-warning me-1"></i> Redact Freight Charges</div>
                <div class="small text-muted" style="font-size:0.72rem;">Blackout confidential shipping freight</div>
              </div>
              <i class="bi bi-plus-circle text-primary fs-6"></i>
            </button>
            <button class="btn btn-outline-secondary btn-sm text-start d-flex align-items-center justify-content-between p-2" onclick="applyPreset('add_original_stamp')">
              <div>
                <div class="fw-semibold text-dark"><i class="bi bi-patch-check text-primary me-1"></i> Official Copy Stamp</div>
                <div class="small text-muted" style="font-size:0.72rem;">Stamp verified export documentation copy</div>
              </div>
              <i class="bi bi-plus-circle text-primary fs-6"></i>
            </button>
          </div>
        </div>

        <!-- Tab 3: Registered Consigners Direct Stamper -->
        <div class="d-none" id="tabPaneConsigners">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="small text-muted fw-bold" style="font-size:0.75rem;">REGISTERED CONSIGNERS</span>
            <a href="consigners.php" class="small text-primary fw-semibold text-decoration-none" style="font-size:0.75rem;">Manage &rarr;</a>
          </div>
          <p class="small text-muted mb-2" style="font-size:0.75rem;">Click any consigner to stamp verified export particulars directly on the PDF:</p>
          <div class="d-grid gap-2" id="sidebarConsignersList">
            <!-- Populated dynamically via JS -->
          </div>
        </div>

      </div>

      <!-- Inspector Footer: Quick Keyboard Reference -->
      <footer class="pdf-studio-inspector-footer">
        <div class="d-flex align-items-center justify-content-between text-muted" style="font-size:0.72rem;">
          <span><kbd class="bg-white border text-dark px-1">V</kbd> Select</span>
          <span><kbd class="bg-white border text-dark px-1">W</kbd> Whiteout</span>
          <span><kbd class="bg-white border text-dark px-1">T</kbd> Text</span>
          <span><kbd class="bg-white border text-dark px-1">Ctrl+Z</kbd> Undo</span>
        </div>
      </footer>

    </aside>

  </div>

</main>

<!-- Modal: Text Edit Modal for double-clicked text annotation -->
<div class="modal fade" id="editTextModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header py-2 border-bottom">
        <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>Edit Correction Text</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label small fw-semibold">Replacement Text / Values</label>
          <textarea class="form-control" id="modalTextInput" rows="3" placeholder="Enter corrected text..."></textarea>
          <div class="d-flex align-items-center justify-content-between mt-1">
            <span class="small text-muted" style="font-size:0.75rem;"><i class="bi bi-shield-check text-success me-1"></i>Text wraps & strictly stays inside box</span>
            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size:0.75rem;" onclick="autoFitBoxToText()" title="Automatically resize the box to fit this text">
              <i class="bi bi-arrows-angle-expand me-1 text-primary"></i>Auto-fit Box
            </button>
          </div>
        </div>
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label small fw-semibold">Font Size</label>
            <select class="form-select form-select-sm" id="modalTextFontSize">
              <option value="8">8 pt</option>
              <option value="9">9 pt</option>
              <option value="10">10 pt</option>
              <option value="11">11 pt</option>
              <option value="12">12 pt</option>
              <option value="14">14 pt</option>
              <option value="16">16 pt</option>
              <option value="20">20 pt</option>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label small fw-semibold">Font Style</label>
            <select class="form-select form-select-sm" id="modalTextFontWeight">
              <option value="normal">Normal</option>
              <option value="bold">Bold</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer py-2 border-top">
        <button type="button" class="btn btn-sm btn-outline-danger me-auto" onclick="deleteCurrentModalItem()">Delete</button>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="autoFitBoxToText()">
          <i class="bi bi-arrows-angle-expand me-1"></i>Fit Box to Text
        </button>
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="saveModalTextChanges()">Apply Changes</button>
      </div>
    </div>
  </div>
</div>

<!-- Load PDF.js & pdf-lib from reliable CDNs -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
  if (typeof pdfjsLib !== 'undefined') {
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
  }
</script>
<script src="https://unpkg.com/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>

<!-- Custom PDF Visual Editor Script -->
<script src="js/pdf-editor.js?v=<?= time() ?>"></script>

<?php
include_once __DIR__ . '/includes/footer.php';
?>
