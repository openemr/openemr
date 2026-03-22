<?php
// Minimal standalone HTTP router for MedEx module API

require_once __DIR__ . '/compat.php';
require_once __DIR__ . '/../src/MedExAPI.php';

use OpenEMR\Modules\MedEx\MedExAPI;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

// Initialize PDO from environment or default to in-memory SQLite
$dsn = getenv('DB_DSN') ?: 'sqlite::memory:';
$dbUser = getenv('DB_USER') ?: null;
$dbPass = getenv('DB_PASS') ?: null;
try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => 'Failed to connect to DB: ' . $e->getMessage()]);
    exit;
}

QueryUtils::init($pdo);

// Set some sensible defaults from environment
$globals = OEGlobalsBag::getInstance();
$globals->set('medex_api_host', getenv('MEDEX_HOST') ?: 'https://medexbank.com/cart/upload');
$globals->set('medex_enable', getenv('MEDEX_ENABLE') ?: '1');

$medex = new MedExAPI();
$action = $_GET['action'] ?? 'testConnection';
header('Content-Type: application/json');

try {
    switch ($action) {
        case 'testConnection':
            echo json_encode($medex->testConnection());
            break;
        case 'login':
            $force = isset($_GET['force']) ? (bool)$_GET['force'] : false;
            echo json_encode($medex->login($force));
            break;
        case 'register':
            $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            echo json_encode($medex->register(is_array($data) ? $data : []));
            break;
        default:
            echo json_encode(['error' => 'unknown action']);
    }
} catch (Throwable $t) {
    http_response_code(500);
    echo json_encode(['error' => $t->getMessage()]);
}
