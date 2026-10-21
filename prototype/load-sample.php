<?php
// Reliance Granito EDMS - Sample PDF Document Streamer & API
header("Access-Control-Allow-Origin: *");

$docsDir = realpath(__DIR__ . '/../docs');
if (!$docsDir || !is_dir($docsDir)) {
    http_response_code(500);
    echo json_encode(["error" => "Docs directory not found"]);
    exit;
}

$action = $_GET['action'] ?? 'get';

if ($action === 'list') {
    header('Content-Type: application/json; charset=utf-8');
    $files = scandir($docsDir);
    $pdfFiles = [];
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'pdf') {
            $pdfFiles[] = [
                'name' => $file,
                'size' => filesize($docsDir . '/' . $file),
                'url' => 'load-sample.php?file=' . urlencode($file)
            ];
        }
    }
    echo json_encode($pdfFiles);
    exit;
}

// Default: stream requested file
$requestedFile = $_GET['file'] ?? '';
if (empty($requestedFile)) {
    http_response_code(400);
    echo "Missing file parameter";
    exit;
}

// Security: Prevent directory traversal
$safeFilename = basename($requestedFile);
$filePath = $docsDir . DIRECTORY_SEPARATOR . $safeFilename;

if (!file_exists($filePath) || !is_file($filePath)) {
    http_response_code(404);
    echo "File not found: " . htmlspecialchars($safeFilename);
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $safeFilename . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

readfile($filePath);
exit;
