<?php

/**
 * Dispatched when a user logs out from an OIDC-authenticated session.
 *
 * Module listeners can use this to perform RP-Initiated Logout — e.g.,
 * redirecting to the provider's end_session_endpoint with id_token_hint.
 * The event carries the OIDC session metadata so the module knows which
 * provider to contact.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class OidcLogoutEvent extends Event
{
    public const EVENT_NAME = 'oidc.logout';

    private ?string $redirectUrl = null;

    /**
     * @param string      $issuer  The OIDC issuer that authenticated this session.
     * @param string      $username The local OpenEMR username.
     * @param string|null $jti     The JWT ID from the session, if available.
     */
    public function __construct(
        private readonly string $issuer,
        private readonly string $username,
        private readonly ?string $jti = null,
    ) {
    }

    public function getIssuer(): string
    {
        return $this->issuer;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getJti(): ?string
    {
        return $this->jti;
    }

    /**
     * Set a redirect URL for RP-Initiated Logout.
     *
     * If set, the logout flow should redirect to this URL after clearing
     * the local session (provider's end_session_endpoint).
     */
    public function setRedirectUrl(string $url): void
    {
        $this->redirectUrl = $url;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function hasRedirectUrl(): bool
    {
        return $this->redirectUrl !== null;
    }
}
