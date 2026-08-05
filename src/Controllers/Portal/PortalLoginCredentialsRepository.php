<?php

/**
 * PortalLoginCredentialsRepository — read/write surface the portal login flow uses
 * against the patient credential and demographic tables.
 *
 * The interface exists to make PatientPortalLoginController unit-testable: tests inject
 * an in-memory implementation; production uses the SQL-backed implementation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Controllers\Portal;

/**
 * Typed shapes for rows produced by this repository. The SQL implementation normalises
 * the raw DB-string columns to these types at the boundary so the controller can treat
 * them as honest ints/strings without casting.
 *
 * @phpstan-type PatientAccessOnsiteRow array{
 *   id: int,
 *   pid: int,
 *   portal_pwd: string,
 *   portal_username: string,
 *   portal_login_username: string,
 *   portal_pwd_status: int,
 *   portal_onetime?: string|null,
 * }
 * @phpstan-type PatientDataRow array{
 *   pid: int,
 *   fname: string,
 *   lname: string,
 *   email: string,
 *   providerID: int,
 *   allow_patient_portal: string,
 * }
 * @phpstan-type ProviderInfoRow array{
 *   fname: string,
 *   lname: string,
 *   username: string|null,
 * }
 */
interface PortalLoginCredentialsRepository
{
    /**
     * Fetch the `patient_access_onsite` row matching the supplied one-time PIN-reset token.
     *
     * @return PatientAccessOnsiteRow|null Row, or null if not found.
     */
    public function fetchByOneTimeToken(string $token): ?array;

    /**
     * Fetch by portal_login_username (the user-entered login).
     *
     * @return PatientAccessOnsiteRow|null Row, or null if not found.
     */
    public function fetchByLoginUsername(string $loginUsername): ?array;

    /**
     * Fetch by portal_username (the canonical username; used when password_update mode 1
     * is active because the patient may have changed login_username).
     *
     * @return PatientAccessOnsiteRow|null Row, or null if not found.
     */
    public function fetchByUsername(string $username): ?array;

    /**
     * Clear a consumed one-time PIN-reset token so it cannot be reused.
     */
    public function clearOneTimeToken(string $token): void;

    /**
     * Persist a rehashed password without changing the login username. Called when the
     * existing hash uses an older algorithm and we want to upgrade transparently.
     */
    public function updatePasswordHash(int $id, string $newHash): void;

    /**
     * Persist a new login username, new password hash, and clear the must-change status
     * — performed when the patient completes a password-change flow.
     */
    public function updateLoginAndPassword(int $id, string $loginUsername, string $newHash): void;

    /**
     * Fetch the `patient_data` row for the given pid.
     *
     * @return PatientDataRow|null Patient demographics row, or null if not found
     *   (treated as "not active patient").
     */
    public function fetchPatientData(int $pid): ?array;

    /**
     * Resolve a provider user id to display info. Returns null if the provider id is zero
     * or the user lookup fails.
     *
     * @return ProviderInfoRow|null
     */
    public function fetchProviderInfo(int $providerId): ?array;
}
