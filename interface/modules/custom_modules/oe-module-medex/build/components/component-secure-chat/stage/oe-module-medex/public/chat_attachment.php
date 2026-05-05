<?php

/**
 * MedEx Secure Chat Attachment Storage (OpenEMR side)
 *
 * POST JSON:
 *   practice_id, pid, api_key, file_name, mime, size, content_base64
 * GET:
 *   mode=get&api_key=...&path=...
 */

$ignoreAuth = true;
$sessionAllowWrite = false;

require_once(__DIR__ . "/../../../../globals.php");

/**
 * Ensure the "Secure Chat" document category exists (child of root "Categories").
 * Returns the category id.
 */
function ensureSecureChatCategory(): int
{
    $catRow = sqlQuery("SELECT id FROM categories WHERE name = 'Secure Chat' AND parent = 1 LIMIT 1");
    if ($catRow && (int)$catRow['id'] > 0) {
        return (int)$catRow['id'];
    }
    // Use nested-set: append after the current rightmost node.
    $maxRow = sqlQuery("SELECT MAX(rght) AS max_rght FROM categories");
    $nextLeft = (int)($maxRow['max_rght'] ?? 0) + 1;
    sqlStatement(
        "INSERT INTO categories (name, value, parent, lft, rght, aco_spec, codes) VALUES (?, '', 1, ?, ?, 'patients|docs', '')",
        ['Secure Chat', $nextLeft, $nextLeft + 1]
    );
    $newRow = sqlQuery("SELECT id FROM categories WHERE name = 'Secure Chat' AND parent = 1 LIMIT 1");
    return (int)($newRow['id'] ?? 0);
}

/**
 * Register an uploaded attachment in OpenEMR's documents table so it appears
 * in the patient chart under Documents → Secure Chat.
 */
function registerDocumentInOpenEMR(int $pidInt, string $relativeUrl, string $mime, int $size, string $displayName): void
{
    $categoryId = ensureSecureChatCategory();
    if ($categoryId <= 0) {
        error_log("MedEx chat_attachment: could not get/create Secure Chat category");
        return;
    }

    // documents.id is not auto-increment — use MAX(id)+1.
    $idRow = sqlQuery("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM documents");
    $documentId = (int)($idRow['next_id'] ?? 1);

    sqlStatement(
        "INSERT INTO documents
            (id, type, size, date, url, mimetype, owner, foreign_id, docdate, name, storagemethod, encounter_id, deleted)
         VALUES
            (?, 'file_url', ?, NOW(), ?, ?, 0, ?, CURDATE(), ?, 0, 0, 0)",
        [$documentId, $size, $relativeUrl, $mime, $pidInt, $displayName]
    );

    sqlStatement(
        "INSERT IGNORE INTO categories_to_documents (category_id, document_id) VALUES (?, ?)",
        [$categoryId, $documentId]
    );
}

header('Content-Type: application/json');

function apiKeyMatches(string $practiceId, string $providedKey): bool
{
    $apiRow = sqlQuery("SELECT ME_api_key FROM medex_prefs WHERE MedEx_id = ? LIMIT 1", [(int)$practiceId]);
    $storedApiKey = (string)($apiRow['ME_api_key'] ?? '');
    if ($storedApiKey === '' || $providedKey === '') {
        return false;
    }

    return hash_equals($storedApiKey, $providedKey)
        || str_starts_with($storedApiKey, $providedKey)
        || str_starts_with($providedKey, $storedApiKey);
}

$mode = (string)($_GET['mode'] ?? '');
if ($mode === 'get') {
    $apiKey = (string)($_GET['api_key'] ?? '');
    $relativePath = trim((string)($_GET['path'] ?? ''));

    // Infer practice from path to validate key against that tenant.
    if (!preg_match('#^secure_chat/(\d+)/#', $relativePath, $m)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid path']);
        exit;
    }
    $practiceId = (int)$m[1];

    if (!apiKeyMatches((string)$practiceId, $apiKey)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Invalid API key']);
        exit;
    }

    if (str_contains($relativePath, '..') || str_starts_with($relativePath, '/')) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid path']);
        exit;
    }

    $baseDir = rtrim((string)$GLOBALS['OE_SITE_DIR'], '/\\') . '/documents';
    $fullPath = $baseDir . '/' . $relativePath;
    if (!is_file($fullPath)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Not found']);
        exit;
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($fullPath) ?: 'application/octet-stream';
    $name = basename($fullPath);

    header_remove('Content-Type');
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($fullPath));
    header('Content-Disposition: inline; filename="' . str_replace('"', '', $name) . '"');
    readfile($fullPath);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode((string)$raw, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

$required = ['practice_id', 'pid', 'api_key', 'file_name', 'mime', 'size', 'content_base64'];
foreach ($required as $field) {
    if (!isset($data[$field]) || $data[$field] === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing field: ' . $field]);
        exit;
    }
}

$practiceId = (int)$data['practice_id'];
$pidInt     = (int)$data['pid'];   // numeric OpenEMR patient pid for DB foreign_id
$pid = preg_replace('/[^A-Za-z0-9_-]/', '_', (string)$data['pid']);
$apiKey = (string)$data['api_key'];
$fileName = preg_replace('/[^A-Za-z0-9._-]/', '_', (string)$data['file_name']);
$mime = (string)$data['mime'];
$size = (int)$data['size'];
$contentBase64 = (string)$data['content_base64'];

if ($practiceId <= 0 || $pid === '' || $fileName === '' || $size <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid payload']);
    exit;
}

if (!apiKeyMatches((string)$practiceId, $apiKey)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid API key']);
    exit;
}

$allowed = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'application/pdf' => 'pdf',
    'application/msword' => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
];
if (!isset($allowed[$mime])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Unsupported type']);
    exit;
}
if ($size > (10 * 1024 * 1024)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Attachment too large']);
    exit;
}

$rawData = base64_decode($contentBase64, true);
if ($rawData === false || strlen($rawData) === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Attachment decode failed']);
    exit;
}
// Accept decoded size within ±10% of reported size to handle HEIC→JPEG, EXIF strip, etc.
$actualSize = strlen($rawData);

$baseDir = rtrim((string)$GLOBALS['OE_SITE_DIR'], '/\\') . '/documents';
$relativeDir = 'secure_chat/' . $practiceId . '/' . $pid;
$targetDir = $baseDir . '/' . $relativeDir;
if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Storage path error']);
    exit;
}

$ext = $allowed[$mime];
$storedName = date('Ymd_His') . '_' . substr(hash('sha256', $fileName . microtime(true) . random_int(1, PHP_INT_MAX)), 0, 16) . '.' . $ext;
$fullPath = $targetDir . '/' . $storedName;

// For images: auto-downscale to max 1600px on longest side before storing.
// This keeps the full original quality for display but reduces file weight.
$dataToWrite = $rawData;
if (strpos($mime, 'image/') === 0 && extension_loaded('gd')) {
    try {
        $src = imagecreatefromstring($rawData);
        if ($src !== false) {
            $origW = imagesx($src);
            $origH = imagesy($src);
            $maxDim = 1600;
            if ($origW > $maxDim || $origH > $maxDim) {
                $scale = min($maxDim / $origW, $maxDim / $origH);
                $newW  = (int)round($origW * $scale);
                $newH  = (int)round($origH * $scale);
                $dst   = imagecreatetruecolor($newW, $newH);
                // Preserve alpha for PNG/WebP
                if ($mime === 'image/png' || $mime === 'image/webp') {
                    imagealphablending($dst, false);
                    imagesavealpha($dst, true);
                }
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
                ob_start();
                if ($mime === 'image/png') {
                    imagepng($dst, null, 6);
                } elseif ($mime === 'image/webp') {
                    imagewebp($dst, null, 82);
                } else {
                    imagejpeg($dst, null, 85);
                }
                $resized = ob_get_clean();
                imagedestroy($dst);
                if ($resized !== false && strlen($resized) > 0) {
                    $dataToWrite = $resized;
                }
            }
            imagedestroy($src);
        }
    } catch (\Throwable $e) {
        error_log('MedEx chat_attachment: GD resize failed: ' . $e->getMessage());
        // Non-fatal — store original
    }
}

if (file_put_contents($fullPath, $dataToWrite) === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Write failed']);
    exit;
}

// Register in OpenEMR documents table so the file appears in the patient chart.
$storedSize = strlen($dataToWrite);
$relativeUrl = $relativeDir . '/' . $storedName;
try {
    registerDocumentInOpenEMR($pidInt, $relativeUrl, $mime, $storedSize, $fileName);
} catch (\Throwable $e) {
    error_log("MedEx chat_attachment: document registration failed: " . $e->getMessage());
    // Non-fatal — file is already written; continue to return success.
}

http_response_code(200);
echo json_encode([
    'success' => true,
    'relative_path' => $relativeDir . '/' . $storedName,
]);
