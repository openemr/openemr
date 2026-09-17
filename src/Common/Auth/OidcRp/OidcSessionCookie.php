<?php

/**
 * Session cookie policy for the cross-site identity-provider redirect.
 *
 * @package OpenEMR
 * @author Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\OidcRp;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class OidcSessionCookie
{
    /**
     * Lax permits the provider's top-level GET callback to carry the session
     * containing state, nonce and PKCE verifier. Restore Strict on callback;
     * successful authentication also rotates the session ID.
     *
     * @param array{lifetime: int, path: string, domain: string, secure: bool, httponly: bool, samesite: string} $params
     */
    public static function create(SessionInterface $session, array $params, bool $leavingForProvider): Cookie
    {
        return Cookie::create(
            name: $session->getName(),
            value: $session->getId(),
            expire: $params['lifetime'] > 0 ? time() + $params['lifetime'] : 0,
            path: $params['path'],
            domain: $params['domain'],
            secure: $params['secure'],
            httpOnly: $params['httponly'],
            sameSite: $leavingForProvider ? Cookie::SAMESITE_LAX : Cookie::SAMESITE_STRICT,
        );
    }
}
