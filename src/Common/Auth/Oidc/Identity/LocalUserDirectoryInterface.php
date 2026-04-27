<?php

/**
 * Contract for resolving OpenEMR local-user information during OIDC
 * authentication.
 *
 * Complements {@see ExternalIdentityRepository}: after an external identity
 * maps to a local `users.id`, an implementation of this interface exposes
 * the local-user shape that the login flow needs (user row, ACL group,
 * password hash).
 *
 * Missing records are returned as `null` for the user row and empty
 * strings for the string-returning methods. Callers treat those as "user
 * not authenticatable" and log accordingly.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\Oidc\Identity;

interface LocalUserDirectoryInterface
{
    /**
     * Fetch a single row from the local `users` table by primary key.
     *
     * @return array<string, mixed>|null The row as an associative array, or null when not found.
     */
    public function findUserById(int $userId): ?array;

    /**
     * Resolve the gACL auth-group name for a username.
     *
     * Returns an empty string when the user has no group membership — callers
     * treat this as "not authorized to log in."
     */
    public function findAuthGroupFor(string $username): string;

    /**
     * Fetch the password hash from local credential storage for session verification.
     *
     * Returns an empty string when the record is absent.
     */
    public function findPasswordHashFor(string $username): string;
}
