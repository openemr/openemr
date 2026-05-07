<?php

/**
 * MedEx HMAC Request Validation
 *
 * Validates inbound Direction-2 requests (MedEx SaaS → OpenEMR).
 *
 * Protocol:
 *   Sender adds three headers:
 *     X-MedEx-Timestamp  : Unix timestamp (seconds)
 *     X-MedEx-Nonce      : Random hex string (prevents replay within window)
 *     X-MedEx-Signature  : hex(HMAC-SHA256(api_key, "{ts}\n{nonce}\n{body_sha256}"))
 *
 *   Receiver:
 *     1. Reject if |now - timestamp| > CLOCK_SKEW_SEC
 *     2. Recompute signature; reject if mismatch
 *
 * Usage:
 *   $raw = file_get_contents('php://input');
 *   [$ok, $err] = MedExHmac::validate($raw, $api_key);
 *   if (!$ok) { http_response_code(403); echo json_encode(['error' => $err]); exit; }
 */

namespace OpenEMR\Modules\MedEx;

class MedExHmac
{
    private const CLOCK_SKEW_SEC = 300; // ±5 minutes

    /**
     * Validate a signed inbound request.
     *
     * @param  string $rawBody  Raw POST body (file_get_contents('php://input'))
     * @param  string $apiKey   Expected shared secret (medex_prefs.ME_api_key)
     * @return array{0: bool, 1: string}  [true, ''] on success; [false, reason] on failure
     */
    public static function validate(string $rawBody, string $apiKey): array
    {
        $ts  = (string)($_SERVER['HTTP_X_MEDEX_TIMESTAMP'] ?? '');
        $nonce = (string)($_SERVER['HTTP_X_MEDEX_NONCE']  ?? '');
        $sig   = (string)($_SERVER['HTTP_X_MEDEX_SIGNATURE'] ?? '');

        if ($ts === '' || $nonce === '' || $sig === '') {
            return [false, 'Missing HMAC headers'];
        }

        $tsInt = (int)$ts;
        if (abs(time() - $tsInt) > self::CLOCK_SKEW_SEC) {
            return [false, 'Timestamp out of range'];
        }

        $bodyHash = hash('sha256', $rawBody);
        $message  = "{$tsInt}\n{$nonce}\n{$bodyHash}";
        $expected = hash_hmac('sha256', $message, $apiKey);

        if (!hash_equals($expected, strtolower($sig))) {
            return [false, 'Signature mismatch'];
        }

        return [true, ''];
    }

    /**
     * Build signing headers for an outbound Direction-2 request.
     * Use on the MedEx SaaS side before curl_exec().
     *
     * @param  string $body    Raw POST body string
     * @param  string $apiKey  Shared secret (openemr_api_key from hipaa_secure_chat_tokens)
     * @return array<string>   Array of "Header: value" strings ready for CURLOPT_HTTPHEADER
     */
    public static function signingHeaders(string $body, string $apiKey): array
    {
        $ts    = (string)time();
        $nonce = bin2hex(random_bytes(16));
        $bodyHash = hash('sha256', $body);
        $message  = "{$ts}\n{$nonce}\n{$bodyHash}";
        $sig   = hash_hmac('sha256', $message, $apiKey);

        return [
            'X-MedEx-Timestamp: ' . $ts,
            'X-MedEx-Nonce: '     . $nonce,
            'X-MedEx-Signature: ' . $sig,
        ];
    }
}
