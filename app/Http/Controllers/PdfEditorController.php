<?php

namespace App\Http\Controllers;

use App\Models\Consigner;
use App\Models\Document;
use App\Models\PdfEdit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PdfEditorController extends Controller
{
    /**
     * Show the Visual PDF Editor workspace.
     */
    public function index(Request $request): View
    {
        $documents = Document::orderBy('is_primary', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        $consigners = Consigner::where('status', 'Active')
            ->orderBy('id', 'asc')
            ->get();

        $selectedDocName = $request->query('doc');
        $selectedDoc = $selectedDocName
            ? $documents->first(fn ($doc) => strcasecmp($doc->name, $selectedDocName) === 0)
            : null;

        return view('pdf-editor', [
            'documents' => $documents,
            'consigners' => $consigners,
            'selectedDoc' => $selectedDoc,
        ]);
    }

    /**
     * Save annotations/edits to MySQL.
     */
    public function saveEdits(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'document_name' => ['required', 'string'],
            'annotations' => ['required'],
            'edits_count' => ['nullable', 'integer'],
        ]);

        $annotationsData = is_string($validated['annotations'])
            ? json_decode($validated['annotations'], true)
            : $validated['annotations'];

        $pdfEdit = PdfEdit::updateOrCreate(
            ['document_name' => $validated['document_name']],
            [
                'annotations' => $annotationsData,
                'edits_count' => $validated['edits_count'] ?? count($annotationsData ?? []),
                'user_id' => Auth::id(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'PDF annotations and masks saved to MySQL database successfully.',
            'data' => $pdfEdit,
        ]);
    }

    /**
     * Retrieve saved annotations from MySQL.
     */
    public function loadEdits(Request $request, string $documentName): JsonResponse
    {
        $safeName = basename($documentName);
        $pdfEdit = PdfEdit::where('document_name', $safeName)->first();

        return response()->json([
            'success' => true,
            'document_name' => $safeName,
            'annotations' => $pdfEdit ? $pdfEdit->annotations : [],
        ]);
    }
}
