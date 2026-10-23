<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConsignerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PdfEditorController;
use Illuminate\Http\Request;
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
        Route::get('/{consigner}', [ConsignerController::class, 'show'])->name('show');
        Route::get('/{consigner}/edit', [ConsignerController::class, 'edit'])->name('edit');
        Route::put('/{consigner}', [ConsignerController::class, 'update'])->name('update');
        Route::delete('/{consigner}', [ConsignerController::class, 'destroy'])->name('destroy');
    });

    // PDF Visual Editor & Masker
    Route::prefix('pdf-editor')->name('pdf-editor.')->group(function () {
        Route::get('/', [PdfEditorController::class, 'index'])->name('index');
        Route::post('/save', [PdfEditorController::class, 'saveEdits'])->name('save');
        Route::get('/edits/{documentName}', [PdfEditorController::class, 'loadEdits'])->name('load-edits');
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
