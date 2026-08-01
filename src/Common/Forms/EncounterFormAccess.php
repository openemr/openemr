<?php

/**
 * Encounter-form access guard.
 *
 * Confirms that a form identified by (form_id, formdir) belongs to the
 * session's active patient (and optionally, the session's active encounter).
 * Intended to be called at the top of form entry-point scripts
 * (`interface/forms/<form>/save.php`, `view.php`, `new.php`, and equivalents)
 * that accept an attacker-controllable `id` / `formid` request parameter.
 *
 * Generalization of the portal-only guard previously offered by
 * `CoreFormToPortalUtility::confirmFormBootstrapPatient`. Where a form
 * has additional invariants (e.g. eye_mag's per-encounter binding), the
 * caller can opt into encounter enforcement via the fourth argument.
 *
 * Design: the security-relevant comparison is isolated in the pure
 * `isFormOwnedBySession()` method (unit-testable, no DB, no session, no
 * process termination) so the trust boundary has direct test coverage.
 * `assertFormBelongsToSessionPatient()` composes the pure check with a DB
 * fetch and terminates the request on mismatch via `AccessDeniedHelper::deny()`.
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
     * that case always returns false. Encounter enforcement only fires when
     * `$sessionEncounter` is non-null (opt-in per caller); when omitted, the
     * check is pid-only.
     */
    public static function isFormOwnedBySession(
        ?int $ownerPid,
        int $sessionPid,
        ?int $ownerEncounter = null,
        ?int $sessionEncounter = null,
    ): bool {
        if ($ownerPid === null || $ownerPid !== $sessionPid) {
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

        $formOwner = QueryUtils::querySingleRow(
            "SELECT pid, encounter FROM forms WHERE form_id = ? AND formdir = ? AND deleted = 0",
            [$formId, $formDir],
        );

        $ownerPid = null;
        $ownerEncounter = null;
        if (is_array($formOwner)) {
            $ownerPidRaw = $formOwner['pid'] ?? null;
            $ownerEncounterRaw = $formOwner['encounter'] ?? null;
            $ownerPid = is_numeric($ownerPidRaw) ? (int) $ownerPidRaw : null;
            $ownerEncounter = is_numeric($ownerEncounterRaw) ? (int) $ownerEncounterRaw : null;
        }

        if (!self::isFormOwnedBySession($ownerPid, $sessionPid, $ownerEncounter, $sessionEncounter)) {
            AccessDeniedHelper::deny(
                sprintf('Form %d/%s not accessible by session pid %d', $formId, $formDir, $sessionPid),
                'security-access',
                Response::HTTP_NOT_FOUND,
            );
        }
    }
}
