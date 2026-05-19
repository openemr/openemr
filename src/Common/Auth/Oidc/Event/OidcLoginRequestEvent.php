<?php

/**
 * Dispatched in auth.inc.php before existing Google/password authentication.
 *
 * An OIDC module listener inspects the request data (e.g., a POSTed ID token)
 * and, if it can handle the authentication, validates the token, resolves the
 * local user, and marks the event as handled. If no listener handles the event,
 * the existing auth paths (password, Google Sign-In) run unchanged.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Event;

use Symfony\Contracts\EventDispatcher\Event;

final class OidcLoginRequestEvent extends Event
{
    public const EVENT_NAME = 'oidc.login.request';

    private bool $handled = false;

    /** @var array<string, mixed> Authenticated user info (set by listener) */
    private array $userInfo = [];

    private string $username = '';

    private string $authGroup = '';

    private string $passwordHash = '';

    /**
     * @param array<string, string> $postData  The POST parameters from the login form.
     * @param array<string, string> $getData   The GET parameters from the request.
     */
    public function __construct(
        private readonly array $postData,
        private readonly array $getData = [],
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function getPostData(): array
    {
        return $this->postData;
    }

    /**
     * @return array<string, string>
     */
    public function getGetData(): array
    {
        return $this->getData;
    }

    /**
     * Get a specific POST parameter.
     */
    public function getPostParam(string $key): ?string
    {
        return $this->postData[$key] ?? null;
    }

    /**
     * Called by the module listener after successful OIDC authentication.
     *
     * @param string               $username     The local OpenEMR username.
     * @param string               $passwordHash The user's password hash (for session verification).
     * @param array<string, mixed> $userInfo     User record from the users table.
     * @param string               $authGroup    The user's auth/ACL group.
     */
    public function setAuthenticatedUser(
        string $username,
        string $passwordHash,
        array $userInfo,
        string $authGroup,
    ): void {
        $this->handled = true;
        $this->username = $username;
        $this->passwordHash = $passwordHash;
        $this->userInfo = $userInfo;
        $this->authGroup = $authGroup;
    }

    public function isHandled(): bool
    {
        return $this->handled;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    /**
     * @return array<string, mixed>
     */
    public function getUserInfo(): array
    {
        return $this->userInfo;
    }

    public function getAuthGroup(): string
    {
        return $this->authGroup;
    }
}
