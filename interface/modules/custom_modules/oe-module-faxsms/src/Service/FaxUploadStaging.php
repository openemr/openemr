<?php

/**
 * Common staging behavior for outbound fax uploads.
 *
 * Each fax provider client used to inline its own copy of the upload-
 * staging logic (MIME filter, sanitized random filename, encrypt at
 * rest, decrypt-to-tempnam handoff, finally-cleanup). This service
 * collects that behavior in one place so the controllers can focus on
 * their per-vendor send semantics.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\FaxSMS\Service;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Crypto\CryptoInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

final readonly class FaxUploadStaging
{
    /**
     * On-disk shape produced by processUpload(): sanitized basename,
     * underscore, 8-char random hex suffix, fax-safe extension.
     */
    private const STAGED_PATH_REGEX =
        '/^[A-Za-z0-9_.-]+_[a-f0-9]{8}\\.(pdf|tiff|jpg|png|txt)$/';

    /**
     * MIME types accepted as fax payloads. Anything else is rejected at
     * upload time so attacker-chosen extensions like .php or .htaccess
     * can never reach the staging directory.
     *
     * @var array<string, string>
     */
    private const ACCEPTED_MIME = [
        'application/pdf' => '.pdf',
        'image/tiff' => '.tiff',
        'image/jpeg' => '.jpg',
        'image/png' => '.png',
        'text/plain' => '.txt',
    ];

    public function __construct(
        private CryptoInterface $crypto,
        private LoggerInterface $logger,
    ) {
    }

    public static function create(): self
    {
        return new self(
            ServiceContainer::getCrypto(),
            ServiceContainer::getLogger(),
        );
    }

    /**
     * Process a single uploaded file entry (a slice of $_FILES). MIME-
     * sniff the bytes, reject anything outside the whitelist, write the
     * contents encrypted at rest (when drive_encryption is on) under a
     * filename consisting of a sanitized basename, a short random hex
     * suffix, and a server-determined extension. Returns the on-disk
     * path, or an empty string on failure.
     *
     * Callers pass `$_FILES['fax']` directly; the service never reads
     * superglobals so this stays testable and the openemr.forbiddenGlobals
     * rule is honored at the boundary.
     *
     * @param array{name?: mixed, tmp_name?: mixed, error?: mixed} $upload
     */
    public function processUpload(string $baseDir, array $upload): string
    {
        if (($upload['error'] ?? null) !== UPLOAD_ERR_OK) {
            $this->logger->warning('Fax upload missing or in error state');
            return '';
        }

        $tmpName = $upload['tmp_name'] ?? null;
        if (!is_string($tmpName)) {
            return '';
        }

        $mime = mime_content_type($tmpName);
        $ext = is_string($mime) ? (self::ACCEPTED_MIME[$mime] ?? null) : null;
        if ($ext === null) {
            $this->logger->warning(
                'Unsupported fax upload content type',
                ['mime' => $mime]
            );
            return '';
        }

        $targetDir = $this->ensureStagingDir($baseDir);
        if ($targetDir === null) {
            return '';
        }

        $rawName = $upload['name'] ?? null;
        $origName = is_string($rawName) ? basename($rawName) : 'fax';
        $sanitized = convert_safe_file_dir_name(pathinfo($origName, PATHINFO_FILENAME));
        $base = is_string($sanitized) && $sanitized !== '' ? $sanitized : 'fax';
        $filepath = $targetDir . '/' . $base . '_' . bin2hex(random_bytes(4)) . $ext;

        $content = file_get_contents($tmpName);
        if ($content === false) {
            return '';
        }

        $written = file_put_contents(
            $filepath,
            $this->crypto->encryptForFilesystem($content)
        );
        if ($written === false) {
            $this->logger->error(
                'Failed to store uploaded fax',
                ['filepath' => $filepath]
            );
            return '';
        }

        return $filepath;
    }

    /**
     * True if the path matches the on-disk shape processUpload() creates.
     * Use this to scope cleanup to files this service staged, rather
     * than to caller-managed temp files that happen to live in the same
     * directory.
     */
    public function isStagedUploadPath(string $path): bool
    {
        return preg_match(self::STAGED_PATH_REGEX, basename($path)) === 1;
    }

    /**
     * Read an encrypted staged upload, decrypt it, and write the
     * plaintext to a per-request tempnam. Returns the tempnam path so
     * callers can hand a real plaintext file to their vendor SDK or to
     * email-attachment helpers. Returns null if any step fails.
     *
     * Legacy plaintext files (created before the at-rest encryption
     * work landed) flow through unchanged because decryptFromFilesystem
     * version-prefix-checks before decrypting.
     */
    public function decryptStagedToTemp(string $stagedPath): ?string
    {
        $raw = file_get_contents($stagedPath);
        if ($raw === false) {
            return null;
        }
        $plaintext = $this->crypto->decryptFromFilesystem($raw);

        $tempPath = tempnam(sys_get_temp_dir(), 'fax_');
        if ($tempPath === false) {
            return null;
        }
        if (file_put_contents($tempPath, $plaintext) === false) {
            return null;
        }
        return $tempPath;
    }

    /**
     * Best-effort removal of staging artifacts. Filesystem::remove
     * swallows the benign already-gone race; a real permission failure
     * surfaces as IOException, which is logged at warning level for the
     * individual path so the loop keeps cleaning up the remaining paths.
     */
    public function removeStagedArtifacts(?string ...$paths): void
    {
        $fs = new Filesystem();
        foreach ($paths as $path) {
            if ($path === null) {
                continue;
            }
            try {
                $fs->remove($path);
            } catch (IOException $cleanupError) {
                $this->logger->warning(
                    'Failed to remove staged fax upload artifact',
                    ['path' => $path, 'exception' => $cleanupError]
                );
            }
        }
    }

    /**
     * Ensure the per-controller fax staging directory exists at
     * `<baseDir>/send/` with 0700 permissions, and re-apply chmod on
     * every call so installs that were created earlier with looser
     * permissions get tightened on the next pass. Returns the directory
     * path on success, or null if the directory could not be created.
     */
    public function ensureStagingDir(string $baseDir): ?string
    {
        $targetDir = $baseDir . '/send';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0700, true)) {
            $this->logger->error(
                'Failed to create fax staging directory',
                ['directory' => $targetDir]
            );
            return null;
        }
        chmod($targetDir, 0700);
        return $targetDir;
    }
}
