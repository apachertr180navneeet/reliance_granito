# Reliance Granito - Export Documentation Management System (EDMS)

An enterprise export documentation and compliance system for **Reliance Granito Private Limited** (Morbi Ceramic Manufacturing Facility & Jodhpur Export Office).

The system manages the end-to-end export documentation lifecycle, including Customs EDI Shipping Bills, Commercial Invoices, Bills of Lading (B/L), and provides an interactive **Visual PDF Editor & Masker** for compliance, redaction, and particulars amendment.

---

## 🚀 Key Features

### 1. Interactive Visual PDF Editor & Masker (`prototype/pdf-editor.php`)
- **Clean Minimalist Interface**: Distraction-free workspace with dark slate desk backdrop, document status indicator, and quick sample loaders.
- **Pure CSS Architecture**: 
  - Zero `<style>` blocks in the markup.
  - Zero inline `style="..."` attributes on HTML tags.
  - All styling centralized and modularized in `prototype/css/style.css`.
- **100% Solid Opaque Whiteout Masking**:
  - Eliminates transparency issues where original PDF characters bleed through replacement text.
  - Combines solid opaque white backgrounds (`#FFFFFF` on canvas and `PDFLib.rgb(1, 1, 1)` with `opacity: 1.0` in exported PDFs) with replacement text.
  - Guaranteed clipping prevents any text bleed onto unmasked regions.
- **Automatic Bounding Box Auto-Fitting**:
  - `autoFitItemToText()` automatically calculates text metrics and expands whiteout rectangles with generous horizontal and vertical padding (`padX = 12px`, `padY = 8px`).
  - Added dedicated **"Fit Box"** button in the contextual format bar.
- **In-Place Live Text Editing with Dedicated Save Actions**:
  - Floating action pill attached directly below the active text input box with **Save Edit** and **Cancel** buttons.
  - Contextual **Done** button in the format bar.
  - Header **Save / Export PDF** button with real-time status badge:
    - `No Edits`: Clean state.
    - `● Unsaved Edits (N)`: Amber alert for pending modifications.
    - `✓ All Changes Saved`: Green indicator after successful export.
- **Consigner Stamping Dropdown**:
  - Direct 1-click header dropdown to stamp verified export particulars (Reliance Granito Morbi Plant I & II, Reliance Ceramic, Marwar Granite) with IEC, GSTIN, AD Code, and forex bank details.
  - Generates solid opaque whiteout masks over the exporter particulars area, ensuring seamless compliance and alignment.
- **Annotation & Transformation Tools**:
  - **Select** (<kbd>V</kbd>): Select, drag, and resize annotations via bottom-right corner handles.
  - **Whiteout** (<kbd>W</kbd>): Draw clean, solid white masking boxes.
  - **Replace Text** (<kbd>T</kbd>): Direct click or drag box to type replacement values on top of solid white masks.
  - **Redact** (<kbd>R</kbd>): Draw solid black redaction bars.
- **Multi-Page Handling & Navigation**:
  - Floating bottom dock with page stepper (`< Page X / Y >`).
  - Smooth high-DPI zoom controls (`-`, `+`, percentage readout).
  - Snapshot-based History Engine supporting **Undo** (<kbd>Ctrl+Z</kbd>) and **Redo** (<kbd>Ctrl+Y</kbd>).
- **Native Vector PDF Generation (`pdf-lib`)**:
  - Compiles raw PDF bytes and burns modifications directly into vector objects across all document pages.
  - Embedded standard typefaces (Helvetica and Helvetica-Bold).
  - Generates downloadable `[Document]_CLEAN.pdf` without rasterization or quality degradation.

---

## 📂 Project Directory Structure

```text
reliance_granito_export_workflow/
├── docs/                                # Authentic Export Documents
│   ├── 1469 BL.pdf                      # Bill of Lading (Carrier Original)
│   ├── 1469 INVOICE ORIGINAL.pdf        # Commercial Invoice (Original)
│   ├── 1469 SB ORIGINAL.PDF             # Shipping Bill #1469 (Customs EDI Original)
│   ├── 1469 SB HIDE.pdf                 # Reference Masked Shipping Bill
│   └── BL HIDE.pdf                      # Reference Masked Bill of Lading
│
├── prototype/                           # EDMS Web Application Prototype
│   ├── css/
│   │   └── style.css                    # Centralized Application & Editor Stylesheet
│   ├── includes/
│   │   ├── header.php                   # Common HTML Header & Navigation
│   │   ├── sidebar.php                  # EDMS Navigation Drawer
│   │   └── footer.php                   # Common Footer & Core Scripts
│   ├── js/
│   │   ├── app.js                       # EDMS Application Helpers & Notifications
│   │   ├── consigners.js                # Consigner CRUD Operations
│   │   ├── data.js                      # Mock Datasets & Master Records
│   │   └── pdf-editor.js                # Standalone Reference Editor Engine
│   ├── consigners.php                   # Registered Consigners Management (CRUD)
│   ├── load-sample.php                  # PDF Streaming & Secure File Access Endpoint
│   ├── login.php                        # Authentication Screen
│   ├── index.php                        # EDMS Dashboard & Consignment Overview
│   └── pdf-editor.php                   # Visual PDF Editor & Masker
│
└── README.md                            # Project Documentation & Change Log
```

---

## 🛠️ Technology Stack

- **Backend / Server**: PHP 8.2+ (Laragon / Apache)
- **PDF Rendering**: [Mozilla PDF.js 3.11](https://mozilla.github.io/pdf.js/) for high-DPI canvas rendering
- **PDF Vector Manipulation**: [pdf-lib 1.17](https://pdf-lib.js.org/) for client-side vector compilation
- **Frontend / Styling**: Bootstrap 5.3.3, Bootstrap Icons 1.11.3, Vanilla CSS
- **Typography**: Inter, Plus Jakarta Sans, Courier, Helvetica

---

## ⏱️ Chronological Audit Trail & Change Log (Date & Time Wise)

| Timestamp (IST) | Version | Module / Scope | Action & Description | Target Files | Status |
|:---|:---|:---|:---|:---|:---|
| **2026-10-22 01:55 PM** | `v1.7.3` | **Transparent Drag & Drop Section** | Transparent background by default for drag & drop consigner names with interactive White Mask / Transparent format bar toggle | `prototype/pdf-editor.php`<br>`prototype/js/pdf-editor.js` | ✅ Verified |
| **2026-10-22 01:10 PM** | `v1.7.2` | **Box Auto-Fit Precision** | Dynamic shrink-wrapping of selection box and whiteout mask tightly according to text bounds on drag and drop | `prototype/pdf-editor.php` | ✅ Verified |
| **2026-10-22 01:00 PM** | `v1.7.1` | **Consigner Stamp Format** | Simplified consigner stamping to output legal company name only, auto-fitted with crisp typography | `prototype/pdf-editor.php`<br>`prototype/js/pdf-editor.js` | ✅ Verified |
| **2026-10-22 12:55 PM** | `v1.7.0` | **Consigner Drag & Drop Placement** | Native HTML5 Drag & Drop from Consigner menu directly onto PDF canvas, auto-select with interactive move & drag | `prototype/pdf-editor.php`<br>`prototype/css/style.css` | ✅ Verified |
| **2026-10-22 12:45 PM** | `v1.6.1` | **Coordinate Engine & Precision** | Verified 1:1 mathematical coordinate mapping & sub-pixel font baseline alignment for exact exported text placement | `prototype/pdf-editor.php` | ✅ Verified |
| **2026-10-22 12:20 PM** | `v1.6.0` | **UI & Dropzone Overhaul** | Removed all sample PDF dependencies; redesigned sleek uncrowded header ribbon, rich consigner popover, and modern drag-and-drop upload zone | `prototype/pdf-editor.php`<br>`prototype/css/style.css`<br>`prototype/index.php` | ✅ Verified |
| **2026-10-22 12:10 PM** | `v1.5.0` | **Documentation & Audit** | Structured complete chronological audit trail with date/time stamps for change traceability | `README.md` | ✅ Verified |
| **2026-10-22 12:05 PM** | `v1.4.0` | **Mobile Responsiveness** | Full project mobile & tablet responsiveness, drawer navigation, touch canvas & auto-scaling | `prototype/css/style.css`<br>`prototype/pdf-editor.php`<br>`prototype/js/app.js`<br>`prototype/includes/` | ✅ Verified |
| **2026-10-22 11:45 AM** | `v1.3.0` | **Consigners Integration** | Direct `<select>` dropdown suite, auto-stamping & in-place particulars replacement | `prototype/pdf-editor.php`<br>`prototype/css/style.css` | ✅ Verified |
| **2026-10-22 11:15 AM** | `v1.2.0` | **Pure CSS Architecture** | Enforced zero inline styles and zero `<style>` blocks across all pages into centralized stylesheet | `prototype/pdf-editor.php`<br>`prototype/css/style.css`<br>`prototype/includes/` | ✅ Verified |
| **2026-10-22 10:45 AM** | `v1.1.0` | **Visual Editor UX** | Added in-place Save Edit pill, format bar Done button, and header dirty/saved indicator | `prototype/pdf-editor.php`<br>`prototype/css/style.css` | ✅ Verified |
| **2026-10-22 10:05 AM** | `v1.0.1` | **Masking & Transparency** | Fixed text transparency bleed-through; enforced 100% opaque solid whiteout & auto-fit metrics | `prototype/pdf-editor.php`<br>`prototype/css/style.css` | ✅ Verified |
| **2026-10-22 09:15 AM** | `v1.0.0` | **Core Re-architecture** | Complete ground-up rebuild of Visual PDF Editor & Masker, replacing legacy studio | `prototype/pdf-editor.php`<br>`prototype/css/style.css` | ✅ Verified |

---

### Detailed Audit Trail Entries

#### [2026-10-22 13:55:00 IST] - Release v1.7.3: Transparent Drag & Drop Consigner Section
- **Scope**: Background Mask Transparency, Canvas Rendering & Interactive Toggle
- **Files Modified**: 
  - `prototype/pdf-editor.php`
  - `prototype/js/pdf-editor.js`
- **Changes**:
  - **Transparent Background for Stamped Consigners**: Set `backgroundColor: "transparent"` and `isTransparent: true` by default when dragging and dropping or applying a consigner name onto the PDF. Text renders directly over underlying document lines, tables, or form boxes without opaque white cutouts.
  - **Conditional Canvas Mask Rendering**: Updated `redraw()` to skip `ctx.fillRect()` when `isTransparent` is true, ensuring pure text rendering without white patches over existing lines.
  - **Conditional Vector Export (`pdf-lib`)**: In `downloadPdf()`, omitted `page.drawRectangle` for transparent items so exported vector PDFs retain clean background transparency beneath the text.
  - **Interactive Format Bar Toggle (`toggleBackgroundMask`)**: Replaced static mask badge with an interactive toggle button (`[ ◯ Transparent / 🛡️ White Mask ]`) allowing users to switch any text box between Transparent and Solid Whiteout Mask with one click.

#### [2026-10-22 13:10:00 IST] - Release v1.7.2: Text-Based Selection Box Auto-Fitting Precision
- **Scope**: Bounding Box Metrics, Canvas Interaction & Typography Auto-Fit
- **Files Modified**: 
  - `prototype/pdf-editor.php`
- **Changes**:
  - **Zero-Slack Shrink-Wrapping**: Updated `autoFitItemToText(item, forceExact = true)` to measure text width and calculate needed box dimensions tightly around the text (`padX = 6px`, `padY = 4px`), eliminating the trailing whitespace gap observed to the right of the company name.
  - **Drag & Drop Exact Box Fitting**: Initialized consigner stamp bounding dimensions to zero and forced dynamic text-metric measurement on creation so the selection box snaps snugly to the company name string length.
  - **Live Edit Auto-Sizing**: Updated `commitLiveText()`, `stepFontSize()`, `toggleBold()`, and `fitSelected()` to automatically re-fit the selection box directly to the exact width and height of the updated text.

#### [2026-10-22 13:00:00 IST] - Release v1.7.1: Simplified Consigner Name Only Stamping
- **Scope**: Typography, Mask Sizing & Document Particulars
- **Files Modified**: 
  - `prototype/pdf-editor.php`
  - `prototype/js/pdf-editor.js`
- **Changes**:
  - **Single Legal Entity Name Stamping**: Streamlined the consigner stamping content to stamp strictly the legal company name in uppercase (e.g. `RELIANCE GRANITO PRIVATE LIMITED`), removing the secondary 4-line block (address, IEC, GSTIN, AD Code, Bank).
  - **Single-Line Mask Box Auto-Sizing**: Reduced initial mask bounding box dimensions to single-line proportions (`w: 0.35, h: 0.035, fontSize: 10, isBold: true`) with automated shrink-wrap fitting via `autoFitItemToText()`.
  - **Seamless In-Place Replacement**: If an existing box or name is selected, applying any consigner replaces it cleanly with the new company name.

#### [2026-10-22 12:55:00 IST] - Release v1.7.0: Consigner Drag & Drop Placement & Move Freedom
- **Scope**: Master Data Stamping, Drag & Drop UX & Canvas Placement
- **Files Modified**: 
  - `prototype/pdf-editor.php`
  - `prototype/css/style.css`
- **Changes**:
  - **Native HTML5 Drag & Drop from Consigner Menu**: Enabled `draggable="true"` on all active consigner menu items with grab grip handles (`<i class="bi bi-grip-vertical"></i>`). Users can drag any registered company directly from the dropdown and drop it onto the exact target location on the PDF.
  - **Auto-Position & Viewport Tracking**: Stamping via the "Apply" button places the consigner particulars directly within the user's visible viewport and immediately switches tool to Select with active handles, allowing the user to freely drag & drop / reposition it anywhere across the document.
  - **Selected Box In-Place Substitution**: If any existing text box or whiteout box is selected, applying a consigner updates that exact box instantly without creating a duplicate.
  - **Dynamic Cursor Feedback**: Added `move` cursor feedback when hovering over selected annotations, and `pointer` cursor for non-selected items, making interactive drag-and-drop intuitive.
  - **Zero Inline Styles**: Centralized all drag styles (`.consigner-item-link`, `.consigner-hint-pill`, `.consigner-drag-target`) strictly in `prototype/css/style.css`.

#### [2026-10-22 12:45:00 IST] - Release v1.6.1: 1:1 Coordinate Precision & Sub-Pixel Baseline Alignment
- **Scope**: Coordinate System Mapping, Font Metrics & Vector Export Fidelity
- **Files Modified**: 
  - `prototype/pdf-editor.php`
- **Changes**:
  - **1:1 Normalized Coordinate Mapping**: Confirmed that canvas overlay percentage coordinates `(it.x, it.y)` map directly to unscaled 72-DPI PDF point coordinates `(it.x * pdfW, pdfH - (it.y * pdfH) - boxH)`, guaranteeing exact placement fidelity regardless of browser zoom or device pixel ratio.
  - **Sub-Pixel Font Baseline Alignment**: Refined the `textY` vector export calculation to `let textY = (y + boxH) - 4 - (fontSize * 0.75)`. This accurately accounts for Helvetica's cap-height (~0.75×) so the exported text baseline aligns identically with the screen preview's `padY = 4px` padding.
  - **Zero Visual Drift**: Eliminates any vertical or horizontal drift between on-screen positioning and the downloaded clean vector PDF.

#### [2026-10-22 12:20:00 IST] - Release v1.6.0: Visual UI Declutter & Complete Sample Removal
- **Scope**: User Interface, Visual Hierarchy & First-Class Upload Workflow
- **Files Modified**: 
  - `prototype/pdf-editor.php`
  - `prototype/css/style.css`
  - `prototype/index.php`
- **Changes**:
  - **Removed All Sample PDF Dependencies**: Removed the `Samples` dropdown button, purged automatic loading of `1469 SB ORIGINAL.PDF` on page start, and cleared sample loader handlers from `index.php`.
  - **Glassmorphic Drag & Drop Upload Zone**: Transformed the empty state into a modern, spacious drag-and-drop zone (`.empty-dropzone`) with animated dropzone icon bubble, clear instructional typography, drag-over highlight animations, and standard file dialog picker.
  - **Re-engineered Header Ribbon Architecture**:
    - **Document Identity (Left)**: Sized-up icon badge (`36x36px`), clear bold document title, page count readout, and soft state badge (`No File`, `No Edits`, `Unsaved Edits`, `Saved`).
    - **Centered Segmented Tool Palette**: Replaced crowded inline buttons with clean Apple/Figma style segmented pill controls with white elevation cards for the active tool.
    - **Rich Consigner Stamping Popover**: Replaced clunky 4-piece input group with an elegant dropdown button (`#btnConsignerDropdown`) opening a clean popover menu (`.consigner-menu-popover`) with company names, IEC, region tags, and 1-click stamp execution.
    - **Polished Actions (Right)**: Crisp outline "Upload PDF" button and high-contrast gradient "Save / Export PDF" button with hover micro-elevation.
  - **Zero Inline Styles Preserved**: Maintained strict compliance with pure external CSS classes.

#### [2026-10-22 12:10:00 IST] - Release v1.5.0: Audit Trail & Traceability Overhaul
- **Scope**: Documentation & Project Governance
- **Files Modified**: `README.md`
- **Changes**:
  - Replaced informal bullet points with a date-and-time indexed audit trail matrix.
  - Added exact timestamps, component scopes, file references, and validation statuses.
  - Enabled one-glance chronological tracking of all enhancements, bug fixes, and architectural decisions.

#### [2026-10-22 12:05:00 IST] - Release v1.4.0: Full Project Mobile & Tablet Responsiveness
- **Scope**: Cross-Device Compatibility, Mobile UX & Touch Interaction
- **Files Modified**: 
  - `prototype/css/style.css`
  - `prototype/pdf-editor.php`
  - `prototype/js/app.js`
  - `prototype/includes/header.php`
  - `prototype/includes/sidebar.php`
  - `prototype/index.php`
- **Changes**:
  - **Off-Canvas Sidebar Drawer**: On screens `<= 991.98px`, the fixed 275px sidebar slides off-canvas (`transform: translateX(-100%)`). Added `.sidebar-backdrop` with backdrop blur, `#sidebarCloseBtn`, and `#mobileMenuBtn` hamburger toggle.
  - **Responsive Top Navigation Bar**: Adjusted height (`60px`), streamlined padding, and collapsed secondary subtitles on small viewports.
  - **Multi-Tier Adaptive Editor Header Ribbon**: Header ribbon wraps cleanly into organized tiers on tablets and phones without horizontal overflow:
    - Tier 1: Document badge, sample picker, and Download button.
    - Tier 2: Full-width Consigner select dropdown with Stamp button.
    - Tier 3: Horizontally scrollable tool palette and format bar.
  - **Touchscreen Canvas Support**: Added `touchstart`, `touchmove`, and `touchend` event abstraction to `setupCanvasEvents()`. Drawing, moving, and resizing annotations now function smoothly on touch devices.
  - **Mobile Initial Page Auto-Scaling**: Implemented auto-scaling in `loadPdfBytes()` to horizontally fit PDF pages to mobile viewport widths on initial load.
  - **Thumb-Zone Floating Bottom Dock**: Scaled dock down to `bottom: 14px`, 28px buttons, and compact font readouts for easy one-handed operation on phones.
  - **Zero Inline Styles Preserved**: Eliminated legacy inline styles from `includes/header.php`, `includes/sidebar.php`, and `index.php`; all media queries (`1200px`, `992px`, `768px`, `576px`) centralized in `style.css`.

#### [2026-10-22 11:45:00 IST] - Release v1.3.0: Consigners Master Dropdown & Stamping Suite
- **Scope**: Compliance Automation & Master Data Integration
- **Files Modified**: 
  - `prototype/pdf-editor.php`
  - `prototype/css/style.css`
- **Changes**:
  - **Direct `<select>` Dropdown**: Added `<select id="consignerSelect">` directly in the editor header ribbon with an instant Stamp button (`#btnStampConsigner`) and master directory link (`consigners.php`).
  - **Dynamic Population**: Automatically loads registered active export entities from `getRegisteredConsigners()` with `localStorage` persistence and fallback master seed.
  - **Smart Stamping**: Generates solid opaque whiteout-backed text annotations at the Exporter section (Box 1) containing Legal Name, Factory/Registered Address, IEC Code, GSTIN, AD Code, and Authorized Forex Bank.
  - **In-Place Replacement**: If an existing text annotation is selected, changing the dropdown or clicking Stamp automatically updates that box with the chosen consigner details.
  - **Session Integration**: Connected with `consigners.php` to handle `sessionStorage` transfers ("Stamp on PDF in Editor" button) upon navigation.

#### [2026-10-22 11:15:00 IST] - Release v1.2.0: Pure CSS Architecture (Zero Inline Styles)
- **Scope**: Code Quality, Maintainability & Architecture Constraints
- **Files Modified**: 
  - `prototype/pdf-editor.php`
  - `prototype/css/style.css`
- **Changes**:
  - **Removed All `<style>` Tags**: Completely purged in-page `<style>` blocks from `pdf-editor.php`.
  - **Removed All Inline `style="..."` Attributes**: Converted all layout and typography declarations to semantic CSS classes in `prototype/css/style.css`.
  - **Modular Component Classes Added**: `.editor-app`, `.editor-header`, `.doc-info`, `.doc-icon-badge`, `.doc-pill`, `.doc-status-text`, `.doc-save-badge`, `.tool-palette`, `.tool-btn`, `.format-bar`, `.paper-sheet`, `#liveInputActions`, `.floating-dock`, etc.

#### [2026-10-22 10:45:00 IST] - Release v1.1.0: Dedicated Save Controls & In-Place Editing Actions
- **Scope**: User Experience & Data Integrity
- **Files Modified**: 
  - `prototype/pdf-editor.php`
  - `prototype/css/style.css`
- **Changes**:
  - **Attached Input Action Pill (`#liveInputActions`)**: Placed floating "Save Edit" and "Cancel" buttons directly below the active in-place textarea overlay.
  - **Format Bar "Done" Button**: Added instant deselect/commit button in the format bar.
  - **Real-Time Save / Dirty State Badge**: Added `#saveBadge` next to the document title with 3 distinct live states:
    - `No Edits`: Gray badge for unchanged initial state.
    - `● Unsaved Edits (N)`: Amber alert for pending modifications.
    - `✓ All Changes Saved`: Green indicator following successful vector PDF export.
  - **Dedicated Header Download Button**: Upgraded `#btnDownload` ("Save / Export PDF") with clear tooltips and disabled states.

#### [2026-10-22 10:05:00 IST] - Release v1.0.1: 100% Solid Whiteout Masking & Auto-Fit Box Metrics
- **Scope**: Core Rendering Engine & Export Compliance
- **Files Modified**: 
  - `prototype/pdf-editor.php`
- **Changes**:
  - **Eliminated Character Bleed-Through**: Fixed issue where replacement text rendered transparently, exposing original document text beneath.
  - **100% Opaque Solid White Backgrounds**: Enforced `#FFFFFF` opaque canvas rectangles and `pdf-lib` vector rectangles with `opacity: 1.0` and `PDFLib.rgb(1, 1, 1)`.
  - **Dynamic Bounding Box Auto-Fitting (`autoFitItemToText`)**: Measures text width/height using 2D canvas font metrics and automatically expands mask dimensions with generous padding (`padX = 12px`, `padY = 8px`).
  - **"Fit Box" Action**: Added dedicated auto-fit button in the format bar.

#### [2026-10-22 09:15:00 IST] - Release v1.0.0: Visual PDF Editor Ground-Up Rebuild
- **Scope**: System Architecture & Core Editor Implementation
- **Files Modified**: 
  - `prototype/pdf-editor.php`
  - `prototype/css/style.css`
- **Changes**:
  - Discarded legacy, bloated studio logic in favor of a lean, responsive canvas-based visual editor.
  - Implemented core toolset: Select / Move / Resize (<kbd>V</kbd>), Solid Whiteout Mask (<kbd>W</kbd>), Replace Text (<kbd>T</kbd>), Redact (<kbd>R</kbd>).
  - Snapshot-based Undo / Redo history engine (<kbd>Ctrl+Z</kbd>, <kbd>Ctrl+Y</kbd>).
  - Native client-side vector compilation and download via `pdf-lib` with standard Helvetica embedded fonts.

---

## 💻 Local Development Setup

1. Place the project folder in your local server root (e.g. `C:\laragon\www\laravel_project\reliance_granito_export_workflow`).
2. Start Apache via Laragon or run PHP's built-in server:
   ```bash
   cd prototype
   php -S localhost:8000
   ```
3. Open `http://localhost:8000/pdf-editor.php` in your browser.
4. Select a sample export document (e.g., Shipping Bill #1469) or upload a local PDF to start editing.
