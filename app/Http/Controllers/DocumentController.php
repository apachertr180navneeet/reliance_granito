<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentController extends Controller
{
    /**
     * Return JSON list of available PDF documents.
     */
    public function index(Request $request): JsonResponse
    {
        $docs = Document::orderBy('is_primary', 'desc')->get();
        return response()->json($docs);
    }

    /**
     * Backward-compatible sample list API.
     */
    public function listSamples(): JsonResponse
    {
        $docs = Document::all()->map(function ($doc) {
            return [
                'id' => $doc->id,
                'name' => $doc->name,
                'title' => $doc->title,
                'size' => $doc->file_size,
                'url' => route('documents.stream', ['filename' => $doc->name]),
            ];
        });

        return response()->json($docs);
    }

    /**
     * Stream a PDF document inline with secure path verification.
     */
    public function stream(string $filename): BinaryFileResponse
    {
        $safeFilename = basename($filename);
        $docsDir = base_path('docs');
        $filePath = $docsDir . DIRECTORY_SEPARATOR . $safeFilename;

        if (!file_exists($filePath) || !is_file($filePath)) {
            abort(404, 'Document not found: ' . $safeFilename);
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $safeFilename . '"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'public',
        ]);
    }
}
