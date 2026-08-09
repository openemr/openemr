<?php

/**
 * Encounter-form access guard.
 *
 * Confirms that a form identified by (form_id, formdir) belongs to the
 * session's active patient (and optionally, the session's active encounter).
 * Intended to be called at the top of form entry-point scripts
 * (`interface/forms/<form>/save.php`, `view.php`, `new.php`, and equivalents)
 * that read the form id from the request.
 *
 * Generalization of the portal-only guard previously offered by
 * `CoreFormToPortalUtility::confirmFormBootstrapPatient`. Where a form
 * has additional invariants (e.g. eye_mag's per-encounter binding), the
 * caller can opt into encounter enforcement via the fourth argument.
 *
 * Design: the ownership comparison is isolated in the pure
 * `isFormOwnedBySession()` method (unit-testable, no DB, no session, no
 * process termination). `assertFormBelongsToSessionPatient()` composes
 * the pure check with a DB fetch and terminates the request on mismatch
 * via `AccessDeniedHelper::deny()`.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Forms;

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\PatientSessionUtil;
use Symfony\Component\HttpFoundation\Response;

final class EncounterFormAccess
{
    /**
     * Pure comparison: does the form's owner pid/encounter permit access from
     * the given session pid/encounter?
     *
     * A `null` $ownerPid represents "form not found" (fetch returned no row);
     * that case always returns false. A `$sessionPid <= 0` means "no active
     * patient in session" (PatientSessionUtil::getPid() sentinel); also always
     * denies. Encounter enforcement only fires when `$sessionEncounter` is
     * non-null (opt-in per caller); when omitted, the check is pid-only.
     */
    public static function isFormOwnedBySession(
        ?int $ownerPid,
        int $sessionPid,
        ?int $ownerEncounter = null,
        ?int $sessionEncounter = null,
    ): bool {
        if ($sessionPid <= 0 || $ownerPid === null || $ownerPid !== $sessionPid) {
            return false;
        }

        if ($sessionEncounter !== null && $ownerEncounter !== $sessionEncounter) {
            return false;
        }

        return true;
    }

    /**
     * Confirm the form belongs to the session patient (and optionally the
     * session encounter). Terminates the request with an audit-logged 404
     * on mismatch, via `AccessDeniedHelper::deny()`.
     *
     * Callers pass the form-scoped identifiers they already have on hand.
     * The check is skipped when `$formId` is not a positive integer — this
     * covers the "new form being created" case (no existing row to verify)
     * as well as absent/malformed request input. `int|false|null` is chosen
     * to accept `filter_input(..., FILTER_VALIDATE_INT)` directly so callers
     * do not need to widen the value before calling.
     *
     * @param int|false|null $formId           The `forms.form_id` of the target row.
     * @param string         $formDir          The `forms.formdir` of the target row.
     * @param int|null       $sessionPid       Session's active patient id. Defaults to `$_SESSION['pid']`.
     * @param int|null       $sessionEncounter If non-null, additionally require the form's encounter to match.
     */
    public static function assertFormBelongsToSessionPatient(
        int|false|null $formId,
        string $formDir,
        ?int $sessionPid = null,
        ?int $sessionEncounter = null,
    ): void {
        if (!is_int($formId) || $formId <= 0) {
            return;
        }

        $sessionPid ??= PatientSessionUtil::getPid();
        $owner = self::fetchFormOwner($formId, $formDir);

        if (!self::isFormOwnedBySession(
            $owner['pid'] ?? null,
            $sessionPid,
            $owner['encounter'] ?? null,
            $sessionEncounter,
        )) {
            AccessDeniedHelper::deny(
                sprintf('Form %d/%s not accessible by session pid %d', $formId, $formDir, $sessionPid),
                'security-access',
                Response::HTTP_NOT_FOUND,
            );
        }
    }

    /**
     * Pure predicate: is the given value a positive integer form id?
     *
     * @phpstan-assert-if-true int $formId
     */
    public static function isPositiveFormId(int|false|null $formId): bool
    {
        return is_int($formId) && $formId > 0;
    }

    /**
     * Terminates the request with an audit-logged 404 unless `$formId` is a
     * positive integer. Intended for update-mode branches where a missing or
     * malformed form id should not silently proceed as a no-op UPDATE.
     */
    public static function requirePositiveFormId(int|false|null $formId, string $formDir): int
    {
        if (!self::isPositiveFormId($formId)) {
            AccessDeniedHelper::deny(
                sprintf('Missing or invalid form id for %s', $formDir),
                'security-access',
                Response::HTTP_NOT_FOUND,
            );
        }
        return $formId;
    }

    /**
     * Set a hydrated form object's pid to the session patient. Intended to
     * run after a generic hydration step (e.g. `Controller::populate_object`
     * which iterates `$_POST` and calls `set_$field` for each key), so the
     * persisted row's pid tracks session context rather than whatever the
     * caller submitted.
     *
     * Fails closed (audit-logged 404) when the session has no active patient
     * (`PatientSessionUtil::getPid()` returns a non-positive value) — form
     * save flows require a patient context. `$sessionPid` may be supplied
     * for tests.
     */
    public static function applySessionPidToForm(mixed $form, ?int $sessionPid = null): void
    {
        $sessionPid ??= PatientSessionUtil::getPid();
        if ($sessionPid <= 0) {
            AccessDeniedHelper::deny(
                'Form save attempted without an active patient session',
                'security-access',
                Response::HTTP_NOT_FOUND,
            );
        }
        if (is_object($form) && method_exists($form, 'set_pid')) {
            $form->set_pid($sessionPid);
        }
    }

    /**
     * Look up the pid+encounter of a form. Returns null when the form row is
     * absent or deleted. Callers that need the form's encounter for downstream
     * checks (e.g. sensitivity ACL) can compose this with
     * `isFormOwnedBySession()` and reuse the returned encounter.
     *
     * @return array{pid: int, encounter: int}|null
     */
    public static function fetchFormOwner(int $formId, string $formDir): ?array
    {
        $row = QueryUtils::querySingleRow(
            "SELECT pid, encounter FROM forms WHERE form_id = ? AND formdir = ? AND deleted = 0",
            [$formId, $formDir],
        );
        if (!is_array($row)) {
            return null;
        }
        $pidRaw = $row['pid'] ?? null;
        $encounterRaw = $row['encounter'] ?? null;
        if (!is_numeric($pidRaw) || !is_numeric($encounterRaw)) {
            return null;
        }
        return ['pid' => (int) $pidRaw, 'encounter' => (int) $encounterRaw];
    }
}
