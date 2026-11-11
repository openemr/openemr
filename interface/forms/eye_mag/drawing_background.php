<?php

/**
 * Save a logged-in user's drawing-background preference without touching an encounter.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2026 Ray Magauran <rmagauran@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once __DIR__ . '/../../globals.php';
require_once __DIR__ . '/php/drawing_backgrounds.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Forms\EyeMag\EyeFormData;
use Random\RandomException;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_csrf_verify;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_default;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_key_valid;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_library_locked;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_library_manage;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_library_metadata;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_library_save;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_library_scope;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_options;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_paths;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_preference;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_save_preference;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_share;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_upload;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_zone_valid;

header('Content-Type: application/json');
header('Cache-Control: no-store');

/**
 * Send a JSON response with its HTTP status and stop processing the drawing request.
 *
 * @param array<string, mixed> $payload
 */
$reply = static function (int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
};

$request = CurrentRequest::get();
$post = $request->request->all();
$files = $request->files->all();
if (!$request->isMethod('POST')) {
    header('Allow: POST');
    $reply(405, ['success' => false, 'error' => xl('Use POST to change a background.')]);
}

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$authId = $session->get('authUserID');
if ((!is_int($authId) && !is_string($authId)) || !preg_match('/\A[1-9][0-9]*\z/D', (string) $authId)) {
    $reply(403, ['success' => false, 'error' => xl('Sign in to change your background.')]);
}
$providerId = (int) $authId;
$requiredAccess = ($post['action'] ?? null) === 'list' ? '' : 'write';
if (!AclMain::aclCheckCore('patients', 'med', '', $requiredAccess)) {
    $reply(403, ['success' => false, 'error' => xl('You are not authorized to edit Eye Form drawings.')]);
}
$token = $post['csrf_token_form'] ?? null;
if (!eye_mag_drawing_background_csrf_verify($token, $session)) {
    $reply(403, ['success' => false, 'error' => xl('Your session could not be verified. Reload and try again.')]);
}

$action = $post['action'] ?? null;
$zone = $post['zone'] ?? null;
if (!is_string($action) || !in_array($action, ['list', 'users', 'save', 'select', 'upload', 'rename', 'delete', 'share'], true) || !eye_mag_drawing_background_zone_valid($zone)) {
    $reply(400, ['success' => false, 'error' => xl('Choose a valid drawing background.')]);
}
$allowedFields = ['action', 'zone', 'csrf_token_form'];
if (in_array($action, ['select', 'rename', 'delete', 'share'], true)) $allowedFields[] = 'key';
if (in_array($action, ['save', 'rename'], true)) $allowedFields[] = 'name';
if ($action === 'share') $allowedFields[] = 'recipient';
$expectedFiles = $action === 'upload' ? ['background_file'] : ($action === 'save' ? ['image_file'] : []);
if (array_diff(array_keys($post), $allowedFields) || array_keys($files) !== $expectedFiles) {
    $reply(400, ['success' => false, 'error' => xl('Invalid background request.')]);
}

try {
    if ($action === 'list') {
        $reply(200, ['success' => true,
            'key' => eye_mag_drawing_background_preference($zone, $providerId),
            'options' => eye_mag_drawing_background_options($zone, $providerId)]);
    }
    if ($action === 'users') {
        $rows = QueryUtils::fetchRecords(
            "SELECT id,username,fname,lname FROM users WHERE active=1 AND username IS NOT NULL AND username<>'' AND id<>? ORDER BY lname,fname,username,id",
            [$providerId]
        );
        $users = [];
        foreach ($rows as $row) {
            $id = EyeFormData::identifier($row['id'] ?? null);
            $username = trim(EyeFormData::text($row['username'] ?? ''));
            if (!$id || $username === '') continue;
            $name = trim(EyeFormData::text($row['fname'] ?? '') . ' ' . EyeFormData::text($row['lname'] ?? ''));
            $users[] = ['id' => (string) $id, 'label' => $name !== '' ? $name . ' (' . $username . ')' : $username];
        }
        $reply(200, ['success' => true, 'users' => $users]);
    }
    if ($action === 'share') {
        $recipient = EyeFormData::identifier($post['recipient'] ?? null);
        $target = $recipient > 0 ? QueryUtils::querySingleRow(
            "SELECT id FROM users WHERE id=? AND active=1 AND username IS NOT NULL AND username<>'' LIMIT 1",
            [$recipient]
        ) : null;
        if (!$target || $recipient === $providerId) throw new InvalidArgumentException('Choose an active user.');
        $copiedKey = eye_mag_drawing_background_share($zone, $providerId, $recipient, $post['key'] ?? null);
        $reply(200, ['success' => true, 'key' => $copiedKey]);
    }
    if (in_array($action, ['rename', 'delete'], true)) {
        eye_mag_drawing_background_library_manage($zone, $providerId, $action, $post['key'] ?? null, $post['name'] ?? null);
        $reply(200, ['success' => true,
            'key' => eye_mag_drawing_background_preference($zone, $providerId),
            'options' => eye_mag_drawing_background_options($zone, $providerId)]);
    }
    if ($action === 'upload') {
        $key = eye_mag_drawing_background_upload($zone, $providerId, $files['background_file']);
    } elseif ($action === 'save') {
        $key = eye_mag_drawing_background_library_save($zone, $providerId, $files['image_file'], $post['name'] ?? null);
    } else {
        $key = $post['key'] ?? null;
        if (!eye_mag_drawing_background_key_valid($key, $zone)) {
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
        if (str_starts_with($key, 'saved-') || str_starts_with($key, 'x-saved-')) {
            [$sourceZone, $sourceKey] = eye_mag_drawing_background_library_scope($zone, $key);
            $paths = eye_mag_drawing_background_paths($sourceZone, $providerId);
            /** Recheck the saved image under its library lock before making it the provider's default. */
            eye_mag_drawing_background_library_locked($paths, static function () use ($paths, $zone, $providerId, $key, $sourceKey): void {
                eye_mag_drawing_background_library_metadata($paths, $sourceKey);
                eye_mag_drawing_background_save_preference($zone, $providerId, $key);
            });
        } else {
            eye_mag_drawing_background_save_preference($zone, $providerId, $key);
        }
    }
    $reply(200, ['success' => true, 'key' => $key, 'options' => $options]);
} catch (InvalidArgumentException) {
    $reply(400, ['success' => false, 'error' => xl('Choose a valid drawing background, image, or overlay name.')]);
} catch (RuntimeException | RandomException) {
    $reply(500, ['success' => false, 'error' => xl('The background could not be saved. Please try again.')]);
}
