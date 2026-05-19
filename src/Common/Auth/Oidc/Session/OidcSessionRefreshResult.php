<?php

/**
 * Immutable outcome of an OIDC session-refresh attempt.
 *
 * Returned by {@see OidcSessionRefreshHandler}. The caller (typically an
 * HTTP endpoint) translates this into the appropriate HTTP response and
 * performs any session-side effects (token-expiry update, cooldown record)
 * on success.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Session;

use OpenEMR\Common\Auth\Oidc\Token\ValidatedToken;

final readonly class OidcSessionRefreshResult
{
    /**
     * @param array<string, mixed> $body JSON-serializable response payload.
     */
    private function __construct(
        public bool $success,
        public int $httpStatus,
        public array $body,
        public ?ValidatedToken $validatedToken = null,
    ) {
    }

    public static function ok(ValidatedToken $token): self
    {
        return new self(
            success: true,
            httpStatus: 200,
            body: ['success' => true, 'expires_at' => $token->expiresAt->getTimestamp()],
            validatedToken: $token,
        );
    }

    public static function error(int $httpStatus, string $error, ?string $message = null): self
    {
        $body = ['error' => $error];
        if ($message !== null) {
            $body['message'] = $message;
        }
        return new self(success: false, httpStatus: $httpStatus, body: $body);
    }
}
