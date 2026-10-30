<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConsignerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceSettingController;
use App\Http\Controllers\PdfEditorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

// First page: Login Page (or Dashboard if already authenticated)
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Protected application routes (Requires Authentication)
Route::middleware('auth')->group(function () {
    // Main Control Tower Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Consigners Master CRUD
    Route::prefix('consigners')->name('consigners.')->group(function () {
        Route::get('/', [ConsignerController::class, 'index'])->name('index');
        Route::get('/create', [ConsignerController::class, 'create'])->name('create');
        Route::post('/', [ConsignerController::class, 'store'])->name('store');
        Route::get('/export', [ConsignerController::class, 'export'])->name('export');
        Route::post('/reset-demo', [ConsignerController::class, 'resetDemo'])->name('reset-demo');
        Route::get('/{id}', [ConsignerController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ConsignerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ConsignerController::class, 'update'])->name('update');
        Route::match(['patch', 'post'], '/{id}/status', [ConsignerController::class, 'toggleStatus'])->name('toggle-status');
        Route::match(['delete', 'post'], '/{id}/delete', [ConsignerController::class, 'destroy'])->name('delete-action');
        Route::match(['delete', 'post'], '/{id}', function (Request $request, string|int $id) {
            if ($request->input('_method') === 'PUT' || $request->input('_method') === 'PATCH') {
                return app(ConsignerController::class)->update($request, $id);
            }

            return app(ConsignerController::class)->destroy($request, $id);
        })->name('destroy');
    });

    // PDF Visual Editor & Masker
    Route::prefix('pdf-editor')->name('pdf-editor.')->group(function () {
        Route::get('/', [PdfEditorController::class, 'index'])->name('index');
        Route::post('/save', [PdfEditorController::class, 'saveEdits'])->name('save');
        Route::get('/edits/{documentName}', [PdfEditorController::class, 'loadEdits'])->name('load-edits');
    });

    // Commercial Export Invoices
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('create');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::get('/settings', [InvoiceSettingController::class, 'edit'])->name('settings');
        Route::post('/settings', [InvoiceSettingController::class, 'update'])->name('settings.update');
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/packing-list', [InvoiceController::class, 'packingList'])->name('packing-list');
        Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
        Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('update');
        Route::match(['delete', 'post'], '/{invoice}/delete', [InvoiceController::class, 'destroy'])->name('delete-alt');
        Route::match(['delete', 'post'], '/{invoice}', function (Request $request, Invoice $invoice) {
            if ($request->input('_method') === 'PUT' || $request->input('_method') === 'PATCH') {
                return app(InvoiceController::class)->update($request, $invoice);
            }

            return app(InvoiceController::class)->destroy($request, $invoice);
        })->name('destroy');
    });
});

// Documents streaming & API endpoints
Route::get('/docs/{filename}', [DocumentController::class, 'stream'])->where('filename', '.*')->name('documents.stream');
Route::get('/api/documents', [DocumentController::class, 'index'])->name('api.documents.index');
Route::get('/api/sample-docs', [DocumentController::class, 'listSamples'])->name('api.documents.samples');

// Backward compatibility for prototype sample loader
Route::get('/load-sample.php', function (Request $request) {
    if ($request->query('action') === 'list') {
        return app(DocumentController::class)->listSamples();
    }
    $file = $request->query('file');
    if ($file) {
        return app(DocumentController::class)->stream($file);
    }

    return response()->json(['error' => 'Missing file parameter'], 400);
});

// Web route to execute database migrations
Route::get('/migrate', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        $output = Artisan::output();

        return response('<pre style="background: #111; color: #10b981; padding: 20px; font-family: monospace; border-radius: 8px;">'.($output ?: 'Nothing to migrate. Database is up to date.').'</pre>');
    } catch (Throwable $e) {
        return response('<pre style="background: #111; color: #ef4444; padding: 20px; font-family: monospace; border-radius: 8px;">Migration Error: '.$e->getMessage().'</pre>', 500);
    }
})->name('migrate');

Route::get('/run-migrate', function () {
    return redirect()->route('migrate');
});
