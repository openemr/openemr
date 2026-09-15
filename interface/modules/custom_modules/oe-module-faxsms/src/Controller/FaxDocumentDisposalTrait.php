<?php

/**
 * Shared fax document disposal (stage/download) for fax vendor clients.
 *
 * Consolidates the hardened disposeDocument() flow into a single implementation
 * shared by every fax vendor client (EtherFax, RingCentral, SignalWire) instead
 * of a per-vendor copy that can drift. The routine authorises the caller against
 * the patients/docs ACL, confines the request-supplied file_path to the module's
 * temporary base directory, restricts writes to fax-safe artifacts, and only
 * ever streams decrypted bytes for files inside that directory. Keeping one
 * implementation means a new vendor cannot reintroduce the authenticated
 * arbitrary file read/write that the divergent copies had drifted into.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2018-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\FaxSMS\Controller;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AclMain;

/**
 * @internal Used by AppDispatch subclasses that expose $baseDir, $crypto and
 *           $uploadStaging plus the getRequest()/getSession() accessors.
 */
trait FaxDocumentDisposalTrait
{
    /**
     * Stage (action=setup) or stream (action=download) a fax document.
     *
     * The request-supplied file_path is authorised and confined before any
     * filesystem access: the caller must hold the patients/docs ACL and the
     * resolved path must sit inside the module's temporary base directory.
     *
     * @return string JSON status (setup / errors) or streams the file (download)
     */
    public function disposeDocument(): string
    {
        if (!$this->authorizeDocumentAccess()) {
            header('HTTP/1.1 403 Forbidden', true, 403);
            return $this->disposalResponse(['success' => false, 'message' => xlt('Unauthorized')]);
        }

        $whereRaw = $this->getRequest('file_path') ?? $this->getSession('where') ?? '';
        $where = is_string($whereRaw) ? $whereRaw : '';

        if ($where === '') {
            return $this->disposalResponse(['success' => false, 'message' => xlt('No file path specified')]);
        }

        $allowedRoot = realpath($this->baseDir) ?: $this->baseDir;
        $targetPath = $this->normalizePath($where);

        if (!$this->isPathAllowed($targetPath, $allowedRoot)) {
            ServiceContainer::getLogger()->warning(
                'Fax disposeDocument denied: path outside allowed root',
                ['target' => $targetPath]
            );
            header('HTTP/1.1 403 Forbidden', true, 403);
            return $this->disposalResponse(['success' => false, 'message' => xlt('Access denied')]);
        }

        $contentRaw = $this->getRequest('content', '');
        $content = is_string($contentRaw) ? $contentRaw : '';
        $actionRaw = $this->getRequest('action', '');
        $action = is_string($actionRaw) ? $actionRaw : '';

        if ($action === 'download') {
            // Only allow download of an existing file inside the allowed root.
            if (!is_file($targetPath)) {
                return $this->disposalResponse(['success' => false, 'message' => xlt('File not found')]);
            }
            // sendFile() streams the decrypted bytes, removes the staged temp
            // and exits, so the return below is only for the type system.
            $this->sendFile($targetPath);
            return $this->disposalResponse(['success' => true, 'url' => $targetPath]);
        }

        if ($content !== '' && $action === 'setup') {
            // Only allow fax-safe extensions.
            $ext = strtolower(pathinfo((string) $targetPath, PATHINFO_EXTENSION));
            $allowedExtensions = ['pdf', 'tif', 'tiff', 'txt'];
            if (!in_array($ext, $allowedExtensions, true)) {
                ServiceContainer::getLogger()->warning(
                    'Fax disposeDocument denied: disallowed file extension',
                    ['extension' => $ext]
                );
                return $this->disposalResponse(['success' => false, 'message' => xlt('Invalid file type')]);
            }

            $decoded = base64_decode($content, true);
            if ($decoded === false) {
                return $this->disposalResponse(['success' => false, 'message' => xlt('Invalid content encoding')]);
            }

            // Simple executable content guard (PHP tags, HTML script). This is a
            // defense-in-depth measure on top of the extension allowlist.
            if ($this->containsExecutableCode($decoded)) {
                ServiceContainer::getLogger()->warning('Fax disposeDocument denied: executable content blocked');
                return $this->disposalResponse(['success' => false, 'message' => xlt('Malicious content detected')]);
            }

            // Ensure directory exists (still inside the allowed root).
            $dir = dirname((string) $targetPath);
            if (!is_dir($dir) && !mkdir($dir, 0770, true)) {
                return $this->disposalResponse(['success' => false, 'message' => xlt('Failed to create directory')]);
            }

            // Write atomically; encrypt-at-rest so the download branch's
            // sendFile call (which decrypts via decryptFileBytes) is the only
            // path that ever sees the plaintext bytes on disk.
            $tmp = tempnam($dir, 'fax_');
            if ($tmp === false) {
                return $this->disposalResponse(['success' => false, 'message' => xlt('Failed to create temp file')]);
            }
            $bytes = file_put_contents($tmp, $this->crypto->encryptForFilesystem($decoded), LOCK_EX);
            if ($bytes === false) {
                @unlink($tmp);
                return $this->disposalResponse(['success' => false, 'message' => xlt('Failed to write file')]);
            }
            if (!@rename($tmp, $targetPath)) {
                @unlink($tmp);
                return $this->disposalResponse(['success' => false, 'message' => xlt('Failed to finalize file')]);
            }
            @chmod($targetPath, 0660);

            return $this->disposalResponse(['success' => true, 'message' => '', 'url' => $targetPath]);
        }

        if ($action === 'setup') {
            // Setup without content should not create files; just acknowledge
            // that the (already validated) path is allowed.
            return $this->disposalResponse(['success' => true, 'message' => '', 'url' => $targetPath]);
        }

        // Default: unsupported action.
        return $this->disposalResponse(['success' => false, 'message' => xlt('Unsupported action')]);
    }

    /**
     * Authorise the caller for fax-document disposal. A fax artifact is a
     * patient document, so this requires the patients/docs ACL — the same
     * permission the outbound send path enforces — independent of any vendor
     * API authentication.
     */
    protected function authorizeDocumentAccess(): bool
    {
        return AclMain::aclCheckCore('patients', 'docs');
    }

    /**
     * Encode a disposal status payload. JSON_THROW_ON_ERROR guarantees a string
     * return (the scalar controller responses here never fail to encode).
     *
     * @param array<string, bool|string> $payload
     */
    private function disposalResponse(array $payload): string
    {
        return json_encode($payload, JSON_THROW_ON_ERROR);
    }

    /**
     * Normalize a filesystem path without resolving symlinks from user input.
     */
    private function normalizePath(string $path): string
    {
        $parts = [];
        foreach (explode(DIRECTORY_SEPARATOR, $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($parts);
            } else {
                $parts[] = $segment;
            }
        }
        $prefix = DIRECTORY_SEPARATOR;
        $isWindowsDrive = strlen($path) >= 3 && ctype_alpha($path[0]) && $path[1] === ':' && ($path[2] === DIRECTORY_SEPARATOR);
        if ($isWindowsDrive) {
            $prefix = '';
        }
        return $prefix . implode(DIRECTORY_SEPARATOR, $parts);
    }

    /**
     * Enforce that the target path is at or below the allowed root. The
     * comparison is boundary-aware: a sibling directory that merely shares the
     * root as a string prefix (e.g. "<root>_evil") is rejected.
     */
    private function isPathAllowed(string $targetPath, string $allowedRoot): bool
    {
        $allowed = realpath($allowedRoot) ?: $allowedRoot;
        $real = realpath($targetPath);
        if ($real === false) {
            // File does not exist yet: fall back to its (possibly not-yet-created)
            // directory, resolving as far as the filesystem allows.
            $real = realpath(dirname($targetPath));
            if ($real === false) {
                $intended = $this->normalizePath(dirname($targetPath));
                return $this->pathWithinRoot($intended, $allowed);
            }
        }
        return $this->pathWithinRoot($real, $allowed);
    }

    /**
     * True when $path is the root itself or lives strictly beneath it, using a
     * trailing-separator boundary so a prefix-only sibling never passes.
     */
    private function pathWithinRoot(string $path, string $root): bool
    {
        $root = rtrim($root, DIRECTORY_SEPARATOR);
        return $path === $root || str_starts_with($path, $root . DIRECTORY_SEPARATOR);
    }

    /**
     * Lightweight detector for executable content we never expect for fax
     * artifacts.
     */
    private function containsExecutableCode(string $data): bool
    {
        // Check for PHP tags or HTML/JS script tags in first 1MB to be safe.
        $snippet = substr($data, 0, 1024 * 1024);
        return (bool)preg_match('/<\?(php|=)|<script\b/i', $snippet);
    }

    /**
     * Decrypt the at-rest fax file and stream it to the browser, then remove the
     * staged temp copy. Legacy plaintext files flow through unchanged via
     * decryptFromFilesystem's version-prefix check.
     */
    protected function sendFile(string $filePath): void
    {
        $payload = $this->uploadStaging->decryptFileBytes($filePath);
        if ($payload === null) {
            http_response_code(500);
            echo xlt('Failed to read fax file');
            exit;
        }
        ob_end_clean();
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=" . basename($filePath));
        header("Content-Type: application/pdf");
        header("Content-Transfer-Encoding: binary");
        header('Content-Length: ' . strlen($payload));

        echo $payload;
        @unlink($filePath);
        exit;
    }
}
