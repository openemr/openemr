<?php

/**
 * Sanitized log-context builders for the OAuth2 / SMART authorization
 * flow. Every call site in {@see \OpenEMR\RestControllers\AuthorizationController}
 * routes through this class so log payloads cannot accidentally leak
 * session secrets or query parameters that would let an attacker replay
 * an in-flight authorization (CSRF/state, OIDC nonce, PKCE codeChallenge,
 * raw SMART launch token), or PII (user_id, email, username).
 *
 * The output is intentionally fingerprint-only: present-keys + booleans
 * for "did this slot get populated" + the non-secret client identifier.
 * That preserves the operational value of the existing debug lines
 * (was the session restored? did login populate the user?) without
 * persisting any value that an attacker reading the log file could
 * use to forge a token.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\OpenIDConnect\Logging;

use Lcobucci\JWT\Token\Plain;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class OAuthLogContext
{
    /**
     * Sensitive session keys whose *values* must never be logged. Listed
     * here so each one is named and reviewable in one place.
     *
     * - csrf:               OAuth state / CSRF token (RFC 6749 §10.12)
     * - nonce:              OIDC nonce — binds an ID token to the session
     * - launch:             Raw SMART launch token (opaque, bearer-like)
     * - authRequestSerial:  JSON containing PKCE codeChallenge
     * - user_id, username, email: PII once login phase has populated them
     */
    private const SENSITIVE_SESSION_KEYS = [
        'csrf',
        'nonce',
        'launch',
        'authRequestSerial',
        'user_id',
        'username',
        'email',
    ];

    /**
     * Sensitive /authorize query parameters whose *values* must never be
     * logged. The OAuth-public params (client_id, response_type, scope)
     * are surfaced separately in the safe payload.
     */
    private const SENSITIVE_QUERY_KEYS = [
        'state',
        'nonce',
        'code_challenge',
        'launch',
        'redirect_uri',
        'id_token_hint',
    ];

    /**
     * Maximum character length of a sanitized JWT scalar (kid, jti,
     * alg). 64 is well above legitimate values and tight enough that
     * a megabyte-scale attacker payload can't bloat a log record.
     */
    private const MAX_JWT_SCALAR_LEN = 64;

    /**
     * Maximum number of claim/header key names in a sanitized list.
     * Real JWTs carry a handful of claims; 50 leaves margin without
     * letting an attacker pack a structured record full of garbage.
     */
    private const MAX_JWT_KEY_LIST_LEN = 50;

    private function __construct()
    {
    }

    /**
     * Build a fingerprint of the current session for debug logging.
     * Returns the list of keys present + has_* booleans for the
     * sensitive slots + the non-secret client_id. Values for the
     * sensitive keys are never returned.
     *
     * @return array<string, mixed>
     */
    public static function forSession(SessionInterface $session): array
    {
        $all = $session->all();

        $context = [
            'session_keys' => array_keys($all),
            'client_id'    => $session->get('client_id'),
        ];

        foreach (self::SENSITIVE_SESSION_KEYS as $key) {
            $context['has_' . $key] = self::hasNonEmptyValue($all, $key);
        }

        return $context;
    }

    /**
     * Match the application's own !empty()/empty() guard semantics
     * — a missing key, null, '', '0', 0, false, or [] all read as
     * "not populated". The project's PHPStan configuration forbids
     * the empty() language construct (level-10 strictness), so this
     * helper exists to keep the comparison explicit and reusable.
     *
     * @param array<array-key, mixed> $haystack
     */
    private static function hasNonEmptyValue(array $haystack, string $key): bool
    {
        $value = $haystack[$key] ?? null;
        return $value !== null
            && $value !== ''
            && $value !== '0'
            && $value !== false
            && $value !== 0
            && $value !== [];
    }

    /**
     * Build a redacted summary of the /authorize query parameters.
     * Drops state/nonce/code_challenge/launch/redirect_uri values,
     * keeps the OAuth-public params (client_id, response_type, scope,
     * code_challenge_method) and the full key list.
     *
     * Accepts `array<array-key, mixed>` rather than `array<string, mixed>`
     * so PSR-7's `getQueryParams(): array` flows through the call site
     * without a per-call type assertion. The OAuth /authorize endpoint
     * only ever receives string-keyed query parameters in practice.
     *
     * @param  array<array-key, mixed> $queryParams
     * @return array<string, mixed>
     */
    public static function forAuthorizeQueryParams(array $queryParams): array
    {
        return [
            'queryParam_keys'        => array_keys($queryParams),
            'client_id'              => $queryParams['client_id']              ?? null,
            'response_type'          => $queryParams['response_type']          ?? null,
            'scope'                  => $queryParams['scope']                  ?? null,
            'code_challenge_method'  => $queryParams['code_challenge_method']  ?? null,
            // Sensitive values intentionally omitted; track presence only.
            'has_state'              => self::hasNonEmptyValue($queryParams, 'state'),
            'has_nonce'              => self::hasNonEmptyValue($queryParams, 'nonce'),
            'has_code_challenge'     => self::hasNonEmptyValue($queryParams, 'code_challenge'),
            'has_launch'             => self::hasNonEmptyValue($queryParams, 'launch'),
            'has_redirect_uri'       => self::hasNonEmptyValue($queryParams, 'redirect_uri'),
        ];
    }

    /**
     * Build a redacted summary of a parsed JWT for log context.
     *
     * The full claims/headers are attacker-supplied via the
     * `client_assertion` request parameter — they may carry
     * sensitive material (CWE-532), control characters that forge
     * log lines (CWE-117), or huge payloads that amplify into
     * structured log records (CWE-400). This helper emits only:
     *
     *   - the non-attacker-controlled `client_id`
     *   - sanitized scalar identifiers (`kid`, `alg` from headers,
     *     `jti` from claims) capped at 64 chars and stripped of
     *     any non-`[A-Za-z0-9._-]` character
     *   - the lists of claim/header keys (sanitized + capped at 50
     *     entries each) so operators can see the *shape* of the
     *     assertion without seeing the values
     *
     * Aisle round-5 #3 (CWE-532 / CWE-117 / CWE-400).
     *
     * @return array<string, mixed>
     */
    public static function forJwtAssertion(Plain $token, string $clientId): array
    {
        return [
            'client_id'   => $clientId,
            'kid'         => self::sanitizeJwtScalar($token->headers()->get('kid')),
            'alg'         => self::sanitizeJwtScalar($token->headers()->get('alg')),
            'jti'         => self::sanitizeJwtScalar($token->claims()->get('jti')),
            'claim_keys'  => self::sanitizeJwtKeyList(array_keys($token->claims()->all())),
            'header_keys' => self::sanitizeJwtKeyList(array_keys($token->headers()->all())),
        ];
    }

    /**
     * Names of the sensitive session keys, exposed for tests so the
     * "no value leak" assertion in OAuthLogContextTest cannot drift
     * out of sync with the redaction list above.
     *
     * @return list<string>
     */
    public static function sensitiveSessionKeys(): array
    {
        return self::SENSITIVE_SESSION_KEYS;
    }

    /**
     * Sanitize a single JWT-derived scalar for log context. Returns
     * '' for non-strings, empty strings, and anything that fails the
     * shape check. Otherwise strips any character outside the safe
     * set (alphanumeric, dot, underscore, hyphen — covers RFC 7519
     * jti/kid forms) and caps length at MAX_JWT_SCALAR_LEN.
     */
    private static function sanitizeJwtScalar(mixed $value): string
    {
        if (!is_string($value) || $value === '') {
            return '';
        }
        $stripped = preg_replace('/[^A-Za-z0-9._-]/', '_', $value);
        if (!is_string($stripped)) {
            return '';
        }
        return substr($stripped, 0, self::MAX_JWT_SCALAR_LEN);
    }

    /**
     * Sanitize a list of JWT key names (claim names, header names)
     * for log context. Caps the count at MAX_JWT_KEY_LIST_LEN and
     * runs each name through sanitizeJwtScalar so a key like
     * `"\nattacker_log_inject"` can't forge log lines.
     *
     * @param  list<int|string> $keys
     * @return list<string>
     */
    private static function sanitizeJwtKeyList(array $keys): array
    {
        $capped = array_slice($keys, 0, self::MAX_JWT_KEY_LIST_LEN);
        $sanitized = [];
        foreach ($capped as $key) {
            $sanitized[] = self::sanitizeJwtScalar((string) $key);
        }
        return $sanitized;
    }

    /**
     * Names of the sensitive query-param keys, exposed for tests for
     * the same drift-prevention reason as {@see sensitiveSessionKeys()}.
     *
     * @return list<string>
     */
    public static function sensitiveQueryKeys(): array
    {
        return self::SENSITIVE_QUERY_KEYS;
    }
}
