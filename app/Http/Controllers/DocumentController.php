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
     * Stream a PDF document inline with strict path traversal protection.
     */
    public function stream(string $filename): BinaryFileResponse
    {
        // 1. Strip any directory traversal sequences
        $safeFilename = basename($filename);

        // 2. Enforce PDF extension
        if (strtolower(pathinfo($safeFilename, PATHINFO_EXTENSION)) !== 'pdf') {
            abort(403, 'Unauthorized file type requested.');
        }

        $docsDir = realpath(base_path('docs'));
        if (! $docsDir || ! is_dir($docsDir)) {
            abort(500, 'Documents repository directory not accessible.');
        }

        $targetPath = realpath($docsDir.DIRECTORY_SEPARATOR.$safeFilename);

        // 3. Strict containment check within docs directory
        if (! $targetPath || ! file_exists($targetPath) || ! is_file($targetPath) || ! str_starts_with($targetPath, $docsDir)) {
            abort(404, 'Document not found or inaccessible.');
        }

        return response()->file($targetPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$safeFilename.'"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'public',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
