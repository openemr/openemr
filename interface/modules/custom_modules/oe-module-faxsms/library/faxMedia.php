<?php

/**
 * Outbound fax media handout for SignalWire.
 *
 * SignalWire's fax-send API fetches the document from a URL we provide. That
 * document is PHI, so instead of dropping a world-readable file in the web root
 * we stage it OUTSIDE the web root (see SignalWireClient::uploadFileForFax())
 * and serve it here, gated SOLELY by a short-lived, encrypted, tamper-proof
 * token - there is no OpenEMR session on a provider-side request.
 *
 * The response body MUST be nothing but the raw PDF bytes: any stray notice,
 * warning, whitespace, or gzip wrapping makes the provider report "not a PDF
 * file", so we suppress error output, disable compression, and flush buffered
 * bootstrap output before streaming.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Core\OEGlobalsBag;

// Login-less: access is gated entirely by the encrypted token. Locate globals.php
// by walking up to the interface/ bootstrap so this works regardless of how deep
// the file sits in the module (module root, library/, etc.).
$ignoreAuth = true;
$oeBootstrap = __DIR__;
for ($oeUp = 0; $oeUp < 8 && !is_file($oeBootstrap . '/globals.php'); $oeUp++) {
    $oeBootstrap = dirname($oeBootstrap);
}
require_once($oeBootstrap . '/globals.php');

// Keep the body pure: no displayed errors, no gzip wrapping.
ini_set('display_errors', '0');
ini_set('zlib.output_compression', '0');

$token = filter_input(INPUT_GET, 't') ?? '';
if (!is_string($token) || $token === '') {
    http_response_code(400);
    exit;
}

try {
    $crypto = new CryptoGen();
    $plain = $crypto->decryptStandard($token);
    if (!is_string($plain) || $plain === '') {
        http_response_code(403);
        exit;
    }

    $payload = json_decode($plain, true);
    if (!is_array($payload)) {
        http_response_code(403);
        exit;
    }

    // Expiry.
    if ((int)($payload['exp'] ?? 0) < time()) {
        http_response_code(410); // Gone
        exit;
    }

    // Strict name shape (random hex only) - no wrappers, no traversal.
    $name = (string)($payload['f'] ?? '');
    if (!preg_match('/^fax_out_[a-f0-9]{32}\.pdf$/', $name)) {
        http_response_code(403);
        exit;
    }

    // Resolve the file from a server-side directory listing rather than building
    // a path out of the token value. The path passed to the file functions below
    // is therefore filesystem-sourced (from glob()), not request-derived - which,
    // together with the authenticated-encrypted token and the strict name shape,
    // leaves no path-injection / SSRF surface.
    $dir = OEGlobalsBag::getInstance()->get('OE_SITE_DIR')
        . '/documents/logs_and_misc/fax_outbound';
    $path = null;
    foreach ((glob($dir . '/fax_out_*.pdf') ?: []) as $candidate) {
        if (basename($candidate) === $name) {
            $path = $candidate;
            break;
        }
    }
    if ($path === null || !is_file($path)) {
        http_response_code(404);
        exit;
    }

    // Only ever hand back a real PDF.
    $fh = fopen($path, 'rb');
    $head = $fh ? (string)fread($fh, 5) : '';
    if ($fh) {
        fclose($fh);
    }
    if (!str_starts_with($head, '%PDF-')) {
        ServiceContainer::getLogger()->error('faxMedia.php: staged file is not a PDF', ['name' => $name]);
        http_response_code(415);
        exit;
    }

    // Discard anything the bootstrap may have buffered so ONLY the PDF goes out.
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: application/pdf');
    header('Content-Length: ' . filesize($path));
    header('Content-Disposition: inline; filename="fax.pdf"');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');

    // HEAD: headers only.
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD') {
        exit;
    }

    readfile($path);
    // TTL-only retention: the staged file is removed by uploadFileForFax's TTL
    // sweep, not on serve - keeping fetches retry-safe (HEAD+GET, ranges, retries)
    // while the PHI window stays bounded by the short token TTL.
    exit;
} catch (Throwable $e) {
    ServiceContainer::getLogger()->error('faxMedia.php request failed', ['exception' => $e]);
    http_response_code(500);
    exit;
}
