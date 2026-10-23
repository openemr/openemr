<?php

/** Provider drawing backgrounds. This helper deliberately does not bootstrap OpenEMR. */

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Forms\EyeMag\BaseDrawing;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

function eye_mag_drawing_background_csrf_token(SessionInterface $session): string
{
    return CsrfUtils::collectCsrfToken(session: $session);
}

function eye_mag_drawing_background_csrf_verify($token, SessionInterface $session): bool
{
    if (!is_string($token) || $token === '') {
        return false;
    }
    try {
        return CsrfUtils::verifyCsrfToken($token, session: $session);
    } catch (Throwable $exception) {
        return false;
    }
}

function eye_mag_drawing_background_zone_valid($zone): bool
{
    return is_string($zone) && in_array($zone, ['HPI', 'PMH', 'EXT', 'ANTSEG', 'RETINA', 'SDRETINA', 'NEURO', 'IMPPLAN'], true);
}

function eye_mag_drawing_background_key_valid($key): bool
{
    return is_string($key) && strlen($key) <= 32 && (in_array($key, ['', 'default', 'large-retina', 'retina', 'practice', 'blank'], true)
        || preg_match('/\Astock-(?:ext|antseg|retina|neuro|sdretina)\z/D', $key) === 1
        || preg_match('/\A(?:custom|saved)-[a-f0-9]{16}\z/D', $key) === 1);
}

function eye_mag_drawing_background_default($zone): string
{
    return $zone === 'SDRETINA' ? 'large-retina' : 'default';
}

function eye_mag_drawing_background_webroot(): string
{
    return rtrim(OEGlobalsBag::getInstance()->getWebRoot(), '/');
}

/** Paths contain only authenticated-site configuration and validated internal identifiers. */
function eye_mag_drawing_background_paths($zone, $providerId): array
{
    if (!eye_mag_drawing_background_zone_valid($zone) || !is_numeric($providerId) || (int) $providerId <= 0
        || (string) (int) $providerId !== (string) $providerId) {
        throw new InvalidArgumentException('Invalid drawing background scope.');
    }
    $site = rtrim((string) ($GLOBALS['OE_SITE_DIR'] ?? ''), '/');
    if ($site === '' || !is_dir($site)) {
        throw new RuntimeException('The site image directory is unavailable.');
    }
    $relative = '/images/eye_mag';
    $url = eye_mag_drawing_background_webroot() . '/sites/' . rawurlencode(basename($site)) . $relative;
    return [
        'practice' => $site . $relative . '/OU_' . $zone . '_BASE.jpg',
        'practice_url' => $url . '/OU_' . $zone . '_BASE.jpg',
        'directory' => $site . $relative . '/providers/' . (int) $providerId . '/' . $zone,
        'url' => $url . '/providers/' . (int) $providerId . '/' . $zone,
        'library_directory' => $site . $relative . '/providers/' . (int) $providerId . '/' . $zone . '/library',
        'library_url' => $url . '/providers/' . (int) $providerId . '/' . $zone . '/library',
    ];
}

/** The caller supplies the logged-in user's server-side ID, never a request provider ID. */
function eye_mag_drawing_background_options($zone, $providerId, $side = 'OU', bool $includeArchived = false): array
{
    if (!eye_mag_drawing_background_zone_valid($zone) || !is_string($side) || !in_array($side, ['OU', 'OD', 'OS'], true)) {
        throw new InvalidArgumentException('Invalid drawing background zone.');
    }
    $base = eye_mag_drawing_background_webroot() . '/interface/forms/eye_mag/images/';
    $options = [[
        'key' => eye_mag_drawing_background_default($zone),
        'label' => $zone === 'SDRETINA' ? 'Large retinal drawing' : 'Default drawing',
        'url' => BaseDrawing::fromGlobals(OEGlobalsBag::getInstance())->url($side, $zone),
        'structured' => true,
    ]];
    if ($zone === 'SDRETINA') {
        $options[] = ['key' => 'retina', 'label' => 'ROP', 'url' => $base . $side . '_RETINA_BASE.jpg', 'structured' => true];
    }
    foreach (['EXT' => 'External eye', 'ANTSEG' => 'Anterior segment', 'RETINA' => 'Retina', 'NEURO' => 'Neuro / motility', 'SDRETINA' => 'Large retinal drawing'] as $stockZone => $label) {
        if ($stockZone === $zone || ($zone === 'SDRETINA' && $stockZone === 'RETINA')) {
            continue;
        }
        $options[] = ['key' => 'stock-' . strtolower($stockZone), 'label' => $label,
            'url' => $base . 'OU_' . $stockZone . '_BASE.jpg', 'structured' => $zone === 'NEURO' && $stockZone === 'NEURO'];
    }
    try {
        $paths = eye_mag_drawing_background_paths($zone, $providerId);
        if (is_file($paths['practice']) && is_readable($paths['practice'])) {
            $options[] = ['key' => 'practice', 'label' => 'Practice background', 'url' => $paths['practice_url'], 'structured' => false];
        }
        foreach (eye_mag_drawing_background_library_options($paths, $includeArchived) as $savedOption) {
            $options[] = $savedOption;
        }
        $files = glob($paths['directory'] . '/*.jpg') ?: [];
        sort($files, SORT_STRING);
        $customIndex = 0;
        foreach ($files as $file) {
            $name = basename($file, '.jpg');
            if (preg_match('/\A[a-f0-9]{16}\z/D', $name) !== 1 || !is_file($file) || !is_readable($file) || is_link($file)) {
                continue;
            }
            $options[] = ['key' => 'custom-' . $name, 'label' => 'Uploaded background ' . ++$customIndex,
                'url' => $paths['url'] . '/' . $name . '.jpg', 'structured' => false];
        }
    } catch (RuntimeException $e) {
        // Defaults remain usable before a site's images directory has been provisioned.
    }
    $options[] = ['key' => 'blank', 'label' => 'Blank background', 'url' => $base . 'BLANK_BASE.jpg', 'structured' => false];
    return $options;
}

function eye_mag_drawing_background_preference($zone, $providerId): string
{
    if (!eye_mag_drawing_background_zone_valid($zone) || !is_numeric($providerId) || (int) $providerId <= 0) {
        return eye_mag_drawing_background_default($zone);
    }
    $row = QueryUtils::querySingleRow(
        "SELECT GOVALUE FROM form_eye_mag_prefs WHERE PEZONE = 'DRAW_BACKGROUND' AND id = ? AND LOCATION = ? LIMIT 1",
        [(int) $providerId, $zone . '_BACKGROUND']
    );
    $key = $row['GOVALUE'] ?? '';
    if (!eye_mag_drawing_background_key_valid($key) || $key === '' || $key === 'default') {
        $key = eye_mag_drawing_background_default($zone);
    }
    foreach (eye_mag_drawing_background_options($zone, $providerId) as $option) {
        if ($option['key'] === $key) {
            return $key;
        }
    }
    return eye_mag_drawing_background_default($zone);
}

function eye_mag_drawing_background_save_preference($zone, $providerId, $key): void
{
    if (!eye_mag_drawing_background_zone_valid($zone) || !eye_mag_drawing_background_key_valid($key)
        || (int) $providerId <= 0) {
        throw new InvalidArgumentException('Invalid drawing background preference.');
    }
    $location = $zone . '_BACKGROUND';
    QueryUtils::sqlStatementThrowException(
        "REPLACE INTO form_eye_mag_prefs (PEZONE, LOCATION, LOCATION_text, id, selection, ZONE_ORDER, GOVALUE, ordering)
         VALUES ('DRAW_BACKGROUND', ?, 'Drawing background', ?, ?, 0, ?, 0)",
        [$location, (int) $providerId, $location, $key]
    );
}

/** Decode only bounded, MIME-verified multipart images; original names are never retained. */
function eye_mag_drawing_background_uploaded_image($file): array
{
    if (!is_array($file)) {
        throw new InvalidArgumentException('Choose a JPEG or PNG image.');
    }
    foreach (['error', 'size', 'tmp_name', 'name', 'type'] as $field) {
        if (!array_key_exists($field, $file) || is_array($file[$field]) || is_object($file[$field])) {
            throw new InvalidArgumentException('Choose one JPEG or PNG image.');
        }
    }
    if ((int) $file['error'] !== UPLOAD_ERR_OK || !is_string($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new InvalidArgumentException('The image upload did not complete.');
    }
    $size = filesize($file['tmp_name']);
    if ($size === false || $size < 1 || $size > 2 * 1024 * 1024 || (int) $file['size'] !== $size) {
        throw new InvalidArgumentException('Choose an image no larger than 2 MB.');
    }
    if (!class_exists('finfo') || !function_exists('imagecreatetruecolor')) {
        throw new RuntimeException('Image uploads are unavailable on this server.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $info = @getimagesize($file['tmp_name']);
    if (!$info || !in_array($mime, ['image/jpeg', 'image/png'], true)
        || ($info['mime'] ?? '') !== $mime || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
        throw new InvalidArgumentException('Choose a valid JPEG or PNG image.');
    }
    $width = (int) $info[0];
    $height = (int) $info[1];
    if ($width < 1 || $height < 1 || $width > 4096 || $height > 4096 || $width * $height > 8000000) {
        throw new InvalidArgumentException('The image must be at most 4096 pixels per side and 8 million pixels total.');
    }
    $source = $mime === 'image/png' ? @imagecreatefrompng($file['tmp_name']) : @imagecreatefromjpeg($file['tmp_name']);
    if ($source === false) {
        throw new InvalidArgumentException('The image could not be read.');
    }
    return [$source, $width, $height];
}

/** Validate before decoding, discard source metadata, and persist an opaque normalized JPEG. */
function eye_mag_drawing_background_upload($zone, $providerId, $file): string
{
    [$source, $width, $height] = eye_mag_drawing_background_uploaded_image($file);
    $targetWidth = $zone === 'SDRETINA' ? 1000 : 450;
    $targetHeight = $zone === 'SDRETINA' ? 500 : 250;
    $image = imagecreatetruecolor($targetWidth, $targetHeight);
    imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
    imagealphablending($image, true);
    $scale = min($targetWidth / $width, $targetHeight / $height);
    $drawWidth = max(1, (int) round($width * $scale));
    $drawHeight = max(1, (int) round($height * $scale));
    imagecopyresampled($image, $source, (int) floor(($targetWidth - $drawWidth) / 2), (int) floor(($targetHeight - $drawHeight) / 2), 0, 0, $drawWidth, $drawHeight, $width, $height);
    ob_start();
    $encoded = imagejpeg($image, null, 90);
    $bytes = ob_get_clean();
    unset($source, $image);
    if (!$encoded || !is_string($bytes) || $bytes === '') {
        throw new RuntimeException('The background could not be prepared.');
    }
    $paths = eye_mag_drawing_background_paths($zone, $providerId);
    if (!is_dir($paths['directory']) && !@mkdir($paths['directory'], 0755, true) && !is_dir($paths['directory'])) {
        throw new RuntimeException('The background could not be stored.');
    }
    $hash = substr(hash('sha256', $bytes), 0, 16);
    $destination = $paths['directory'] . '/' . $hash . '.jpg';
    $stream = @fopen($destination, 'xb');
    if ($stream === false) {
        if (is_link($destination) || !is_file($destination) || !hash_equals(hash('sha256', $bytes), (string) @hash_file('sha256', $destination))) {
            throw new RuntimeException('The background could not be stored.');
        }
        return 'custom-' . $hash;
    }
    $written = 0;
    while ($written < strlen($bytes)) {
        $chunk = fwrite($stream, substr($bytes, $written));
        if ($chunk === false || $chunk === 0) {
            fclose($stream);
            @unlink($destination);
            throw new RuntimeException('The background could not be stored.');
        }
        $written += $chunk;
    }
    fclose($stream);
    @chmod($destination, 0644);
    return 'custom-' . $hash;
}

function eye_mag_drawing_background_library_name($name): string
{
    if (!is_string($name) || preg_match('//u', $name) !== 1 || preg_match('/[\x00-\x1f\x7f]/', $name)) {
        throw new InvalidArgumentException('Give this image a name of 1 to 80 characters.');
    }
    $name = trim($name);
    $length = function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') : preg_match_all('/./us', $name);
    if ($length < 1 || $length > 80 || strlen($name) > 320) {
        throw new InvalidArgumentException('Give this image a name of 1 to 80 characters.');
    }
    return $name;
}

/** Read only valid named entries; retained PNGs remain available to existing encounters. */
function eye_mag_drawing_background_library_metadata(array $paths, $key, bool $includeArchived = false): array
{
    if (!is_string($key) || preg_match('/\Asaved-[a-f0-9]{16}\z/D', $key) !== 1) {
        throw new InvalidArgumentException('Choose a saved overlay.');
    }
    $hash = substr($key, 6);
    $entry = $paths['library_directory'] . '/' . $hash . '.json';
    $image = $paths['library_directory'] . '/' . $hash . '.png';
    if (is_link($entry) || !is_file($entry) || !is_readable($entry) || filesize($entry) > 2048
        || is_link($image) || !is_file($image) || !is_readable($image)) {
        throw new InvalidArgumentException('That saved overlay is not available for your account.');
    }
    $metadata = json_decode((string) file_get_contents($entry), true);
    if (!is_array($metadata) || ($metadata['version'] ?? null) !== 1 || ($metadata['key'] ?? '') !== $key
        || !is_string($metadata['sha256'] ?? null) || preg_match('/\A[a-f0-9]{64}\z/D', $metadata['sha256']) !== 1
        || (!$includeArchived && ($metadata['deleted'] ?? false) === true)) {
        throw new InvalidArgumentException('That saved overlay is not available for your account.');
    }
    $metadata['label'] = eye_mag_drawing_background_library_name($metadata['label'] ?? null);
    return $metadata;
}

function eye_mag_drawing_background_library_options(array $paths, bool $includeArchived = false): array
{
    $options = [];
    foreach (glob($paths['library_directory'] . '/*.json') ?: [] as $entry) {
        $hash = basename($entry, '.json');
        try {
            $metadata = eye_mag_drawing_background_library_metadata($paths, 'saved-' . $hash, $includeArchived);
        } catch (InvalidArgumentException $e) {
            continue;
        }
        $option = ['key' => 'saved-' . $hash, 'label' => $metadata['label'], 'url' => $paths['library_url'] . '/' . $hash . '.png',
            'structured' => false, 'saved' => true];
        if (($metadata['deleted'] ?? false) === true) $option['archived'] = true;
        $options[] = $option;
    }
    usort($options, static function ($left, $right) {
        return strnatcasecmp($left['label'], $right['label']) ?: strcmp($left['key'], $right['key']);
    });
    return $options;
}

/** Serialize changes to one user's zone library, including saved-default selection. */
function eye_mag_drawing_background_library_locked(array $paths, callable $operation, bool $create = false)
{
    $directory = $paths['library_directory'];
    if (!is_dir($directory) && (!$create || (!@mkdir($directory, 0755, true) && !is_dir($directory)))) {
        throw new RuntimeException('The image library could not be stored.');
    }
    $lockPath = $directory . '/.library.lock';
    if (is_link($lockPath)) throw new RuntimeException('The image library could not be stored.');
    $lock = @fopen($lockPath, 'c+b');
    if (!$lock) throw new RuntimeException('The image library could not be stored.');
    try {
        if (!flock($lock, LOCK_EX)) throw new RuntimeException('The image library could not be stored.');
        return $operation();
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

function eye_mag_drawing_background_library_replace(array $paths, array $metadata): void
{
    $destination = $paths['library_directory'] . '/' . substr($metadata['key'], 6) . '.json';
    $bytes = json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
    $temporary = @tempnam($paths['library_directory'], '.saving-');
    if ($temporary === false || realpath(dirname($temporary)) !== realpath($paths['library_directory'])) {
        if (is_string($temporary)) @unlink($temporary);
        throw new RuntimeException('The saved overlay could not be changed.');
    }
    try {
        if (file_put_contents($temporary, $bytes) !== strlen($bytes) || !@chmod($temporary, 0644)
            || is_link($destination) || !@rename($temporary, $destination)) {
            throw new RuntimeException('The saved overlay could not be changed.');
        }
    } finally {
        if (is_file($temporary)) @unlink($temporary);
    }
}

/** Keep the PNG and its key stable for all drawings that already reference it. */
function eye_mag_drawing_background_library_manage($zone, $providerId, $action, $key, $name = null): void
{
    if (!in_array($action, ['rename', 'delete'], true)) throw new InvalidArgumentException('Choose a saved overlay action.');
    $paths = eye_mag_drawing_background_paths($zone, $providerId);
    if ($action === 'rename') $name = eye_mag_drawing_background_library_name($name);
    eye_mag_drawing_background_library_metadata($paths, $key, $action === 'delete');
    eye_mag_drawing_background_library_locked($paths, static function () use ($paths, $key, $name, $action, $zone, $providerId) {
        $metadata = eye_mag_drawing_background_library_metadata($paths, $key, $action === 'delete');
        if ($action === 'rename') {
            if ($metadata['label'] !== $name) {
                $metadata['label'] = $name;
                eye_mag_drawing_background_library_replace($paths, $metadata);
            }
            return;
        }
        if (($metadata['deleted'] ?? false) !== true) {
            $metadata['deleted'] = true;
            eye_mag_drawing_background_library_replace($paths, $metadata);
        }
        $location = $zone . '_BACKGROUND';
        $row = QueryUtils::querySingleRow("SELECT GOVALUE FROM form_eye_mag_prefs WHERE PEZONE = 'DRAW_BACKGROUND' AND id = ? AND LOCATION = ? LIMIT 1", [(int) $providerId, $location]);
        if (($row['GOVALUE'] ?? '') === $key) {
            QueryUtils::sqlStatementThrowException("UPDATE form_eye_mag_prefs SET GOVALUE = ? WHERE PEZONE = 'DRAW_BACKGROUND' AND id = ? AND LOCATION = ? AND GOVALUE = ?",
                [eye_mag_drawing_background_default($zone), (int) $providerId, $location, $key]);
        }
    });
}

/** Write complete immutable bytes, then atomically make the final name visible. */
function eye_mag_drawing_background_library_store(string $destination, string $bytes): void
{
    $directory = dirname($destination);
    if (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('The image library could not be stored.');
    }
    if (file_exists($destination) || is_link($destination)) {
        if (is_link($destination) || !is_file($destination) || !hash_equals(hash('sha256', $bytes), (string) @hash_file('sha256', $destination))) {
            throw new RuntimeException('The image library could not be stored.');
        }
        return;
    }
    $temporary = @tempnam($directory, '.saving-');
    if ($temporary === false || realpath(dirname($temporary)) !== realpath($directory)) {
        if (is_string($temporary)) @unlink($temporary);
        throw new RuntimeException('The image library could not be stored.');
    }
    try {
        if (file_put_contents($temporary, $bytes) !== strlen($bytes) || !@chmod($temporary, 0644)) {
            throw new RuntimeException('The image library could not be stored.');
        }
        if (!@link($temporary, $destination)) {
            if (is_link($destination) || !is_file($destination) || !hash_equals(hash('sha256', $bytes), (string) @hash_file('sha256', $destination))) {
                throw new RuntimeException('The image library could not be stored.');
            }
        }
    } finally {
        @unlink($temporary);
    }
}

/** Save a named reusable PNG independently of the user's default or any encounter. */
function eye_mag_drawing_background_library_save($zone, $providerId, $file, $name): string
{
    $name = eye_mag_drawing_background_library_name($name);
    $paths = eye_mag_drawing_background_paths($zone, $providerId);
    [$source, $width, $height] = eye_mag_drawing_background_uploaded_image($file);
    $targetWidth = $zone === 'SDRETINA' ? 1000 : 450;
    $targetHeight = $zone === 'SDRETINA' ? 500 : 250;
    $image = imagecreatetruecolor($targetWidth, $targetHeight);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
    $scale = min($targetWidth / $width, $targetHeight / $height);
    $drawWidth = max(1, (int) round($width * $scale));
    $drawHeight = max(1, (int) round($height * $scale));
    imagecopyresampled($image, $source, (int) floor(($targetWidth - $drawWidth) / 2), (int) floor(($targetHeight - $drawHeight) / 2), 0, 0, $drawWidth, $drawHeight, $width, $height);
    ob_start();
    $encoded = imagepng($image, null, 6);
    $bytes = ob_get_clean();
    unset($source, $image);
    if (!$encoded || !is_string($bytes) || $bytes === '') {
        throw new RuntimeException('The image could not be prepared.');
    }
    return eye_mag_drawing_background_library_locked($paths, static function () use ($paths, $bytes, $name) {
        $sha = hash('sha256', $bytes);
        foreach (eye_mag_drawing_background_library_options($paths) as $option) {
            $entry = eye_mag_drawing_background_library_metadata($paths, $option['key']);
            if ($entry['label'] === $name && hash_equals($entry['sha256'], $sha)) return $entry['key'];
        }
        $hash = substr(hash('sha256', $bytes . "\0" . $name), 0, 16);
        // A previous item with this content may have been renamed or deleted.
        while (file_exists($paths['library_directory'] . '/' . $hash . '.json') || file_exists($paths['library_directory'] . '/' . $hash . '.png')) {
            $hash = bin2hex(random_bytes(8));
        }
        $key = 'saved-' . $hash;
        $metadata = json_encode(['version' => 1, 'key' => $key, 'label' => $name, 'sha256' => $sha], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
        eye_mag_drawing_background_library_store($paths['library_directory'] . '/' . $hash . '.png', $bytes);
        eye_mag_drawing_background_library_store($paths['library_directory'] . '/' . $hash . '.json', $metadata);
        return $key;
    }, true);
}
