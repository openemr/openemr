<?php

/** Save a logged-in user's drawing-background preference without touching an encounter. */

require_once __DIR__ . '/../../globals.php';
require_once __DIR__ . '/php/drawing_backgrounds.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Session\SessionWrapperFactory;

header('Content-Type: application/json');
header('Cache-Control: no-store');

function eye_mag_drawing_background_reply($status, $payload): void
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    eye_mag_drawing_background_reply(405, ['success' => false, 'error' => 'Use POST to change a background.']);
}

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$authId = $session->get('authUserID');
if ((!is_int($authId) && !is_string($authId)) || !preg_match('/\A[1-9][0-9]*\z/D', (string) $authId)) {
    eye_mag_drawing_background_reply(403, ['success' => false, 'error' => 'Sign in to change your background.']);
}
$providerId = (int) $authId;
$requiredAccess = ($_POST['action'] ?? null) === 'list' ? '' : 'write';
if (!AclMain::aclCheckCore('patients', 'med', '', $requiredAccess)) {
    eye_mag_drawing_background_reply(403, ['success' => false, 'error' => 'You are not authorized to edit Eye Form drawings.']);
}
$token = $_POST['csrf_token_form'] ?? null;
if (!eye_mag_drawing_background_csrf_verify($token, $session)) {
    eye_mag_drawing_background_reply(403, ['success' => false, 'error' => 'Your session could not be verified. Reload and try again.']);
}

$action = $_POST['action'] ?? null;
$zone = $_POST['zone'] ?? null;
if (!is_string($action) || !in_array($action, ['list', 'save', 'select', 'upload', 'rename', 'delete'], true) || !eye_mag_drawing_background_zone_valid($zone)) {
    eye_mag_drawing_background_reply(400, ['success' => false, 'error' => 'Choose a valid drawing background.']);
}
$allowedFields = ['action', 'zone', 'csrf_token_form'];
if (in_array($action, ['select', 'rename', 'delete'], true)) $allowedFields[] = 'key';
if (in_array($action, ['save', 'rename'], true)) $allowedFields[] = 'name';
$expectedFiles = $action === 'upload' ? ['background_file'] : ($action === 'save' ? ['image_file'] : []);
if (array_diff(array_keys($_POST), $allowedFields) || array_keys($_FILES) !== $expectedFiles) {
    eye_mag_drawing_background_reply(400, ['success' => false, 'error' => 'Invalid background request.']);
}

try {
    if ($action === 'list') {
        eye_mag_drawing_background_reply(200, ['success' => true,
            'key' => eye_mag_drawing_background_preference($zone, $providerId),
            'options' => eye_mag_drawing_background_options($zone, $providerId)]);
    }
    if (in_array($action, ['rename', 'delete'], true)) {
        eye_mag_drawing_background_library_manage($zone, $providerId, $action, $_POST['key'] ?? null, $_POST['name'] ?? null);
        eye_mag_drawing_background_reply(200, ['success' => true,
            'key' => eye_mag_drawing_background_preference($zone, $providerId),
            'options' => eye_mag_drawing_background_options($zone, $providerId)]);
    }
    if ($action === 'upload') {
        $key = eye_mag_drawing_background_upload($zone, $providerId, $_FILES['background_file']);
    } elseif ($action === 'save') {
        $key = eye_mag_drawing_background_library_save($zone, $providerId, $_FILES['image_file'], $_POST['name'] ?? null);
    } else {
        $key = $_POST['key'] ?? null;
        if (!eye_mag_drawing_background_key_valid($key)) {
            throw new InvalidArgumentException('Choose a valid drawing background.');
        }
        if ($key === '' || $key === 'default') {
            $key = eye_mag_drawing_background_default($zone);
        }
    }
    $options = eye_mag_drawing_background_options($zone, $providerId);
    $available = array_column($options, 'key');
    if (!in_array($key, $available, true)) {
        throw new InvalidArgumentException('That background is not available for your account.');
    }
    if ($action !== 'save') {
        if (strpos($key, 'saved-') === 0) {
            $paths = eye_mag_drawing_background_paths($zone, $providerId);
            eye_mag_drawing_background_library_locked($paths, static function () use ($paths, $zone, $providerId, $key) {
                eye_mag_drawing_background_library_metadata($paths, $key);
                eye_mag_drawing_background_save_preference($zone, $providerId, $key);
            });
        } else {
            eye_mag_drawing_background_save_preference($zone, $providerId, $key);
        }
    }
    eye_mag_drawing_background_reply(200, ['success' => true, 'key' => $key, 'options' => $options]);
} catch (InvalidArgumentException $e) {
    eye_mag_drawing_background_reply(400, ['success' => false, 'error' => $e->getMessage()]);
} catch (Throwable $e) {
    eye_mag_drawing_background_reply(500, ['success' => false, 'error' => 'The background could not be saved. Please try again.']);
}
