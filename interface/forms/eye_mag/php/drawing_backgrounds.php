<?php

/**
 * Provider drawing backgrounds. This helper deliberately does not bootstrap OpenEMR.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2026 Ray Magauran <rmagauran@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms\EyeMag\Drawing;

use finfo;
use GdImage;
use InvalidArgumentException;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Forms\EyeMag\BaseDrawing;
use OpenEMR\Forms\EyeMag\DrawingBackgroundOption;
use OpenEMR\Forms\EyeMag\SavedOverlayMetadata;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/** Collect a drawing-library CSRF token from the authenticated session. */
function eye_mag_drawing_background_csrf_token(SessionInterface $session): string
{
    return CsrfUtils::collectCsrfToken(session: $session);
}

/** Reject absent or invalid drawing-library tokens without exposing session errors. */
function eye_mag_drawing_background_csrf_verify(mixed $token, SessionInterface $session): bool
{
    if (!is_string($token) || $token === '') {
        return false;
    }
    try {
        return CsrfUtils::verifyCsrfToken($token, session: $session);
    } catch (RuntimeException) {
        return false;
    }
}

/**
 * Accept only the Eye Form zones that support drawing backgrounds.
 *
 * @phpstan-assert-if-true =string $zone
 */
function eye_mag_drawing_background_zone_valid(mixed $zone): bool
{
    return is_string($zone) && in_array($zone, ['HPI', 'PMH', 'EXT', 'ANTSEG', 'RETINA', 'SDRETINA', 'NEURO', 'IMPPLAN'], true);
}

/**
 * Accept stock selections and bounded internal keys for uploaded or saved images.
 *
 * @phpstan-assert-if-true =string $key
 */
function eye_mag_drawing_background_key_valid(mixed $key, mixed $zone = null): bool
{
    return is_string($key) && strlen($key) <= 32 && (in_array($key, ['', 'default', 'large-retina', 'retina', 'practice', 'blank'], true)
        || preg_match('/\Astock-(?:ext|antseg|retina|neuro|sdretina)\z/D', $key) === 1
        || preg_match('/\A(?:custom|saved)-[a-f0-9]{16}\z/D', $key) === 1
        || (in_array($zone, ['RETINA', 'SDRETINA'], true)
            && ($key === 'x-practice' || preg_match('/\Ax-(?:custom|saved)-[a-f0-9]{16}\z/D', $key) === 1)));
}

/**
 * Resolve a retinal cross-library key without changing existing stored image paths.
 * @return array{string, string}
 */
function eye_mag_drawing_background_library_scope(mixed $zone, mixed $key): array
{
    if (!eye_mag_drawing_background_zone_valid($zone) || !is_string($key)) {
        throw new InvalidArgumentException('Invalid drawing library scope.');
    }
    if (str_starts_with($key, 'x-')) {
        if (!in_array($zone, ['RETINA', 'SDRETINA'], true)) throw new InvalidArgumentException('Invalid drawing library scope.');
        $zone = $zone === 'RETINA' ? 'SDRETINA' : 'RETINA';
        $key = substr($key, 2);
    }
    if (preg_match('/\A(?:saved|custom)-[a-f0-9]{16}\z/D', $key) !== 1) {
        throw new InvalidArgumentException('Choose a saved drawing image.');
    }
    return [$zone, $key];
}

/** Select the large retinal background for scleral depression and the normal default elsewhere. */
function eye_mag_drawing_background_default(mixed $zone): string
{
    return $zone === 'SDRETINA' ? 'large-retina' : 'default';
}

/** Return the active application web root without a trailing separator. */
function eye_mag_drawing_background_webroot(): string
{
    return rtrim(OEGlobalsBag::getInstance()->getWebRoot(), '/');
}

/**
 * Paths contain only authenticated-site configuration and validated internal identifiers.
 * @return array<string, string>
 */
function eye_mag_drawing_background_paths(mixed $zone, mixed $providerId): array
{
    if (!eye_mag_drawing_background_zone_valid($zone) || !is_numeric($providerId) || (int) $providerId <= 0
        || (string) (int) $providerId !== (string) $providerId) {
        throw new InvalidArgumentException('Invalid drawing background scope.');
    }
    $site = rtrim(OEGlobalsBag::getInstance()->getString('OE_SITE_DIR'), '/');
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

/**
 * The caller supplies the logged-in user's server-side ID, never a request provider ID.
 * @return list<DrawingBackgroundOption>
 */
function eye_mag_drawing_background_options(mixed $zone, mixed $providerId, mixed $side = 'OU', bool $includeArchived = false): array
{
    if (!eye_mag_drawing_background_zone_valid($zone) || !is_string($side) || !in_array($side, ['OU', 'OD', 'OS'], true)) {
        throw new InvalidArgumentException('Invalid drawing background zone.');
    }
    $base = eye_mag_drawing_background_webroot() . '/interface/forms/eye_mag/images/';
    $options = [new DrawingBackgroundOption(
        eye_mag_drawing_background_default($zone),
        $zone === 'SDRETINA' ? xl('Large retinal drawing') : xl('Default drawing'),
        BaseDrawing::fromGlobals(OEGlobalsBag::getInstance())->url($side, $zone),
        true,
    )];
    if ($zone === 'SDRETINA') {
        $options[] = new DrawingBackgroundOption('retina', xl('ROP'), $base . $side . '_RETINA_BASE.jpg', true);
    }
    foreach (['EXT' => xl('External eye'), 'ANTSEG' => xl('Anterior segment'), 'RETINA' => xl('Retina'), 'NEURO' => xl('Neuro / motility'), 'SDRETINA' => xl('Large retinal drawing')] as $stockZone => $label) {
        if ($stockZone === $zone || ($zone === 'SDRETINA' && $stockZone === 'RETINA')) {
            continue;
        }
        $options[] = new DrawingBackgroundOption('stock-' . strtolower($stockZone), $label,
            $base . 'OU_' . $stockZone . '_BASE.jpg', $zone === 'NEURO' && $stockZone === 'NEURO');
    }
    try {
        // Both retinal panels see the same saved images. Keep the two existing
        // storage directories so older encounter URLs and keys remain valid.
        $libraryZones = in_array($zone, ['RETINA', 'SDRETINA'], true) ? ['RETINA', 'SDRETINA'] : [$zone];
        $customIndex = 0;
        foreach ($libraryZones as $libraryZone) {
            $libraryPaths = eye_mag_drawing_background_paths($libraryZone, $providerId);
            $prefix = $libraryZone === $zone ? '' : 'x-';
            if (is_file($libraryPaths['practice']) && is_readable($libraryPaths['practice'])) {
                $practiceLabel = count($libraryZones) === 1 ? xl('Practice background')
                    : ($libraryZone === 'RETINA' ? xl('Retina practice background') : xl('Large Retina practice background'));
                $options[] = new DrawingBackgroundOption($prefix . 'practice', $practiceLabel, $libraryPaths['practice_url'], false);
            }
            foreach (eye_mag_drawing_background_library_options($libraryPaths, $includeArchived) as $savedOption) {
                $options[] = new DrawingBackgroundOption($prefix . $savedOption->key, $savedOption->label, $savedOption->url,
                    $savedOption->structured, true, $savedOption->archived);
            }
            $files = glob($libraryPaths['directory'] . '/*.jpg') ?: [];
            sort($files, SORT_STRING);
            foreach ($files as $file) {
                $name = basename($file, '.jpg');
                if (preg_match('/\A[a-f0-9]{16}\z/D', $name) !== 1 || !is_file($file) || !is_readable($file) || is_link($file)) {
                    continue;
                }
                $options[] = new DrawingBackgroundOption($prefix . 'custom-' . $name, xl('Uploaded background') . ' ' . ++$customIndex,
                    $libraryPaths['url'] . '/' . $name . '.jpg', false);
            }
        }
    } catch (RuntimeException) {
        // Defaults remain usable before a site's images directory has been provisioned.
    }
    $options[] = new DrawingBackgroundOption('blank', xl('Blank background'), $base . 'BLANK_BASE.jpg', false);
    return $options;
}

/**
 * Store longer image keys in this preference row's existing 50-character field.
 * @return array{string, string}
 */
function eye_mag_drawing_background_preference_storage(string $key): array
{
    return strlen($key) > 10 ? ['@image', $key] : [$key, ''];
}

/** Resolve a provider's saved zone default, falling back when its image is unavailable. */
function eye_mag_drawing_background_preference(mixed $zone, mixed $providerId): string
{
    if (!eye_mag_drawing_background_zone_valid($zone) || !is_numeric($providerId) || (int) $providerId <= 0) {
        return eye_mag_drawing_background_default($zone);
    }
    $row = QueryUtils::querySingleRow(
        "SELECT GOVALUE,GORIGHT FROM form_eye_mag_prefs WHERE PEZONE = 'DRAW_BACKGROUND' AND id = ? AND LOCATION = ? LIMIT 1",
        [(int) $providerId, $zone . '_BACKGROUND']
    );
    $key = ($row['GOVALUE'] ?? '') === '@image' ? ($row['GORIGHT'] ?? '') : ($row['GOVALUE'] ?? '');
    $options = eye_mag_drawing_background_options($zone, $providerId);
    // Older releases wrote long image keys into GOVALUE (varchar(10)). Recover
    // only an unambiguous prefix; never guess when two images share it.
    if ($key === 'large-reti' && $zone === 'SDRETINA') $key = 'large-retina';
    if (is_string($key) && strlen($key) === 10 && preg_match('/\A(?:(?:x-)?(?:saved|custom)-[a-f0-9]+|stock-[a-z]+)\z/D', $key)) {
        $matches = array_values(array_filter($options, static fn(DrawingBackgroundOption $option): bool => str_starts_with($option->key, $key)));
        if (count($matches) === 1) $key = $matches[0]->key;
    }
    if (!eye_mag_drawing_background_key_valid($key, $zone) || $key === '' || $key === 'default') {
        $key = eye_mag_drawing_background_default($zone);
    }
    foreach ($options as $option) {
        if ($option->key === $key) {
            return $key;
        }
    }
    return eye_mag_drawing_background_default($zone);
}

/** Persist a validated background key for one provider and drawing zone. */
function eye_mag_drawing_background_save_preference(mixed $zone, mixed $providerId, mixed $key): void
{
    if (!eye_mag_drawing_background_zone_valid($zone) || !eye_mag_drawing_background_key_valid($key, $zone)
        || !is_numeric($providerId) || (int) $providerId <= 0) {
        throw new InvalidArgumentException('Invalid drawing background preference.');
    }
    $location = $zone . '_BACKGROUND';
    [$shortKey, $longKey] = eye_mag_drawing_background_preference_storage($key);
    QueryUtils::sqlStatementThrowException(
        "REPLACE INTO form_eye_mag_prefs (PEZONE, LOCATION, LOCATION_text, id, selection, ZONE_ORDER, GOVALUE, GORIGHT, GOLEFT, UNSPEC, ordering)
         VALUES ('DRAW_BACKGROUND', ?, 'Drawing background', ?, ?, 0, ?, ?, '', '', 0)",
        [$location, (int) $providerId, $location, $shortKey, $longKey]
    );
}

/**
 * Decode only bounded, MIME-verified multipart images; original names are never retained.
 * @return array{GdImage, positive-int, positive-int}
 */
function eye_mag_drawing_background_uploaded_image(mixed $file): array
{
    if (!$file instanceof UploadedFile) {
        throw new InvalidArgumentException('Choose one JPEG or PNG image.');
    }
    if (!$file->isValid()) {
        throw new InvalidArgumentException('The image upload did not complete.');
    }
    $temporary = $file->getPathname();
    $size = $file->getSize();
    if ($size < 1 || $size > 2 * 1024 * 1024) {
        throw new InvalidArgumentException('Choose an image no larger than 2 MB.');
    }
    if (!class_exists(finfo::class) || !function_exists('imagecreatetruecolor')) {
        throw new RuntimeException('Image uploads are unavailable on this server.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($temporary);
    $info = @getimagesize($temporary);
    if ($info === false || !in_array($mime, ['image/jpeg', 'image/png'], true)
        || $info['mime'] !== $mime || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
        throw new InvalidArgumentException('Choose a valid JPEG or PNG image.');
    }
    $width = $info[0];
    $height = $info[1];
    if ($width < 1 || $height < 1 || $width > 4096 || $height > 4096 || $width * $height > 8000000) {
        throw new InvalidArgumentException('The image must be at most 4096 pixels per side and 8 million pixels total.');
    }
    $source = $mime === 'image/png' ? @imagecreatefrompng($temporary) : @imagecreatefromjpeg($temporary);
    if ($source === false) {
        throw new InvalidArgumentException('The image could not be read.');
    }
    return [$source, $width, $height];
}

/** Validate before decoding, discard source metadata, and persist an opaque normalized JPEG. */
function eye_mag_drawing_background_upload(mixed $zone, mixed $providerId, mixed $file): string
{
    [$source, $width, $height] = eye_mag_drawing_background_uploaded_image($file);
    $targetWidth = $zone === 'SDRETINA' ? 1000 : 450;
    $targetHeight = $zone === 'SDRETINA' ? 500 : 250;
    $image = imagecreatetruecolor($targetWidth, $targetHeight);
    if ($image === false) {
        throw new RuntimeException('The background could not be prepared.');
    }
    $white = imagecolorallocate($image, 255, 255, 255);
    if ($white === false) {
        throw new RuntimeException('The background could not be prepared.');
    }
    imagefill($image, 0, 0, $white);
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
        if (!eye_mag_drawing_background_library_matches($destination, $bytes)) {
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

/** Normalize a saved-image name and reject invalid UTF-8, control characters, or excessive length. */
function eye_mag_drawing_background_library_name(mixed $name): string
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

/**
 * Read only valid named entries; retained PNGs remain available to existing encounters.
 * @param array<string, string> $paths
 */
function eye_mag_drawing_background_library_metadata(array $paths, mixed $key, bool $includeArchived = false): SavedOverlayMetadata
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
    return new SavedOverlayMetadata(
        $key,
        eye_mag_drawing_background_library_name($metadata['label'] ?? null),
        $metadata['sha256'],
        ($metadata['deleted'] ?? false) === true,
    );
}

/**
 * List valid saved overlays in natural label order, optionally including archived entries.
 *
 * @param array<string, string> $paths
 * @return list<DrawingBackgroundOption>
 */
function eye_mag_drawing_background_library_options(array $paths, bool $includeArchived = false): array
{
    $options = [];
    foreach (glob($paths['library_directory'] . '/*.json') ?: [] as $entry) {
        $hash = basename($entry, '.json');
        try {
            $metadata = eye_mag_drawing_background_library_metadata($paths, 'saved-' . $hash, $includeArchived);
        } catch (InvalidArgumentException) {
            continue;
        }
        $options[] = new DrawingBackgroundOption('saved-' . $hash, $metadata->label, $paths['library_url'] . '/' . $hash . '.png',
            false, true, $metadata->deleted);
    }
    /** Compare saved overlays by natural label order, using stable keys to break ties. */
    usort($options, static fn(DrawingBackgroundOption $left, DrawingBackgroundOption $right): int => strnatcasecmp($left->label, $right->label) ?: strcmp($left->key, $right->key));
    return $options;
}

/**
 * Serialize changes to one user's zone library, including saved-default selection.
 * @template T
 * @param array<string, string> $paths
 * @param callable(): T $operation
 * @return T
 */
function eye_mag_drawing_background_library_locked(array $paths, callable $operation, bool $create = false): mixed
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

/**
 * Atomically replace saved-overlay metadata within the provider's zone library.
 *
 * @param array<string, string> $paths
 */
function eye_mag_drawing_background_library_replace(array $paths, SavedOverlayMetadata $metadata): void
{
    $destination = $paths['library_directory'] . '/' . substr($metadata->key, 6) . '.json';
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
function eye_mag_drawing_background_library_manage(mixed $zone, mixed $providerId, mixed $action, mixed $key, mixed $name = null): void
{
    if (!is_string($action) || !in_array($action, ['rename', 'delete'], true)) throw new InvalidArgumentException('Choose a saved overlay action.');
    if (!is_string($zone) || !is_numeric($providerId)) {
        throw new InvalidArgumentException('Invalid drawing background scope.');
    }
    [$sourceZone, $sourceKey] = eye_mag_drawing_background_library_scope($zone, $key);
    $paths = eye_mag_drawing_background_paths($sourceZone, $providerId);
    if (!str_starts_with($sourceKey, 'saved-')) throw new InvalidArgumentException('Choose a saved overlay.');
    if ($action === 'rename') $name = eye_mag_drawing_background_library_name($name);
    eye_mag_drawing_background_library_metadata($paths, $sourceKey, $action === 'delete');
    /** Rename or archive the locked library entry and clear its default selection when deleted. */
    eye_mag_drawing_background_library_locked($paths, static function () use ($paths, $sourceKey, $sourceZone, $name, $action, $providerId): void {
        $metadata = eye_mag_drawing_background_library_metadata($paths, $sourceKey, $action === 'delete');
        if ($action === 'rename') {
            if ($metadata->label !== $name) {
                eye_mag_drawing_background_library_replace($paths, $metadata->withLabel(EyeFormData::text($name)));
            }
            return;
        }
        $zones = in_array($sourceZone, ['RETINA', 'SDRETINA'], true) ? ['RETINA', 'SDRETINA'] : [$sourceZone];
        $defaultsToClear = [];
        foreach ($zones as $viewZone) {
            $reference = $viewZone === $sourceZone ? $sourceKey : 'x-' . $sourceKey;
            $location = $viewZone . '_BACKGROUND';
            $row = QueryUtils::querySingleRow("SELECT GOVALUE,GORIGHT FROM form_eye_mag_prefs WHERE PEZONE = 'DRAW_BACKGROUND' AND id = ? AND LOCATION = ? LIMIT 1", [(int) $providerId, $location]) ?: [];
            if (eye_mag_drawing_background_preference($viewZone, $providerId) === $reference) {
                $defaultsToClear[] = [$viewZone, $location, $row['GOVALUE'] ?? '', $row['GORIGHT'] ?? ''];
            }
        }
        if (!$metadata->deleted) {
            eye_mag_drawing_background_library_replace($paths, $metadata->archived());
        }
        foreach ($defaultsToClear as [$viewZone, $location, $oldShort, $oldLong]) {
            [$shortKey, $longKey] = eye_mag_drawing_background_preference_storage(eye_mag_drawing_background_default($viewZone));
            QueryUtils::sqlStatementThrowException("UPDATE form_eye_mag_prefs SET GOVALUE = ?, GORIGHT = ? WHERE PEZONE = 'DRAW_BACKGROUND' AND id = ? AND LOCATION = ? AND GOVALUE = ? AND GORIGHT = ?",
                [$shortKey, $longKey, (int) $providerId, $location, $oldShort, $oldLong]);
        }
    });
}

/** Recheck file identity after a competing writer may have created the destination. */
function eye_mag_drawing_background_library_matches(string $destination, string $bytes): bool
{
    clearstatcache(true, $destination);
    return !is_link($destination) && is_file($destination)
        && hash_equals(hash('sha256', $bytes), (string) @hash_file('sha256', $destination));
}

/** Write complete immutable bytes, then atomically make the final name visible. */
function eye_mag_drawing_background_library_store(string $destination, string $bytes): void
{
    $directory = dirname($destination);
    if (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('The image library could not be stored.');
    }
    if (file_exists($destination) || is_link($destination)) {
        if (!eye_mag_drawing_background_library_matches($destination, $bytes)) {
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
            clearstatcache(true, $destination);
            if (!eye_mag_drawing_background_library_matches($destination, $bytes)) {
                throw new RuntimeException('The image library could not be stored.');
            }
        }
    } finally {
        @unlink($temporary);
    }
}

/**
 * Store already-validated PNG bytes as an independent named library entry.
 * @param array<string, string> $paths
 */
function eye_mag_drawing_background_library_save_bytes(array $paths, string $bytes, string $name): string
{
    /** Reuse an identical named overlay or store a new PNG and metadata pair under the library lock. */
    return eye_mag_drawing_background_library_locked($paths, static function () use ($paths, $bytes, $name): string {
        $sha = hash('sha256', $bytes);
        foreach (eye_mag_drawing_background_library_options($paths) as $option) {
            $entry = eye_mag_drawing_background_library_metadata($paths, $option->key);
            if ($entry->label === $name && hash_equals($entry->sha256, $sha)
                && eye_mag_drawing_background_library_matches($paths['library_directory'] . '/' . substr($entry->key, 6) . '.png', $bytes)) {
                return $entry->key;
            }
        }
        $hash = substr(hash('sha256', $bytes . "\0" . $name), 0, 16);
        // A previous item with this content may have been renamed or deleted.
        while (file_exists($paths['library_directory'] . '/' . $hash . '.json') || file_exists($paths['library_directory'] . '/' . $hash . '.png')) {
            $hash = bin2hex(random_bytes(8));
        }
        $key = 'saved-' . $hash;
        $metadata = json_encode(new SavedOverlayMetadata($key, $name, $sha), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
        eye_mag_drawing_background_library_store($paths['library_directory'] . '/' . $hash . '.png', $bytes);
        eye_mag_drawing_background_library_store($paths['library_directory'] . '/' . $hash . '.json', $metadata);
        return $key;
    }, true);
}

/** Save a named reusable PNG independently of the user's default or any encounter. */
function eye_mag_drawing_background_library_save(mixed $zone, mixed $providerId, mixed $file, mixed $name): string
{
    $name = eye_mag_drawing_background_library_name($name);
    $paths = eye_mag_drawing_background_paths($zone, $providerId);
    [$source, $width, $height] = eye_mag_drawing_background_uploaded_image($file);
    $targetWidth = $zone === 'SDRETINA' ? 1000 : 450;
    $targetHeight = $zone === 'SDRETINA' ? 500 : 250;
    $image = imagecreatetruecolor($targetWidth, $targetHeight);
    if ($image === false) {
        throw new RuntimeException('The image could not be prepared.');
    }
    imagealphablending($image, false);
    imagesavealpha($image, true);
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    if ($transparent === false) {
        throw new RuntimeException('The image could not be prepared.');
    }
    imagefill($image, 0, 0, $transparent);
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
    return eye_mag_drawing_background_library_save_bytes($paths, $bytes, $name);
}

/** Copy one owned overlay or uploaded background to another user's independent library. */
function eye_mag_drawing_background_share(mixed $zone, mixed $providerId, mixed $recipientId, mixed $key): string
{
    [$sourceZone, $sourceKey] = eye_mag_drawing_background_library_scope($zone, $key);
    $source = eye_mag_drawing_background_paths($sourceZone, $providerId);
    $destination = eye_mag_drawing_background_paths($sourceZone, $recipientId);
    if (is_numeric($providerId) && is_numeric($recipientId) && (int) $providerId === (int) $recipientId) {
        throw new InvalidArgumentException('Choose an owned image and another user.');
    }
    // Do not follow redirected provider or zone directories while copying to another account.
    foreach ([$source, $destination] as $paths) {
        foreach ([dirname($paths['directory']), $paths['directory'], $paths['library_directory']] as $directory) {
            if (is_link($directory)) throw new RuntimeException('The image library could not be accessed.');
        }
    }
    if (str_starts_with($sourceKey, 'saved-')) {
        /** Read a consistent image and its label while the sender's library is locked. */
        [$bytes, $name] = eye_mag_drawing_background_library_locked($source, static function () use ($source, $sourceKey): array {
            $metadata = eye_mag_drawing_background_library_metadata($source, $sourceKey);
            $path = $source['library_directory'] . '/' . substr($sourceKey, 6) . '.png';
            $size = filesize($path);
            if ($size === false || $size < 1 || $size > 8 * 1024 * 1024) throw new RuntimeException('The saved image could not be read.');
            $bytes = file_get_contents($path);
            if (!is_string($bytes) || !hash_equals($metadata->sha256, hash('sha256', $bytes))) {
                throw new RuntimeException('The saved image could not be read.');
            }
            return [$bytes, $metadata->label];
        });
        return eye_mag_drawing_background_library_save_bytes($destination, $bytes, $name);
    }
    $hash = substr($sourceKey, 7);
    $path = $source['directory'] . '/' . $hash . '.jpg';
    if (is_link($path) || !is_file($path) || !is_readable($path)) throw new InvalidArgumentException('Choose an owned image.');
    $size = filesize($path);
    if ($size === false || $size < 1 || $size > 8 * 1024 * 1024) throw new RuntimeException('The saved image could not be read.');
    $bytes = file_get_contents($path);
    if (!is_string($bytes) || !hash_equals($hash, substr(hash('sha256', $bytes), 0, 16))) {
        throw new RuntimeException('The saved image could not be read.');
    }
    eye_mag_drawing_background_library_store($destination['directory'] . '/' . $hash . '.jpg', $bytes);
    return $sourceKey;
}
