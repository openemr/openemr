<?php

/**
 * Shared authorization helpers for Carecoordination/CCDA/QRDA controllers.
 *
 * Carecoordination routes have historically been gated only at the module
 * level (`send_to_hie` or `admin/super`); this trait centralizes the
 * per-patient and per-document checks that must sit alongside that gate.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace Carecoordination\Controller;

use Document;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;

trait CarecoordAuthzTrait
{
    /**
     * Return true when the current session is permitted to operate on the
     * given patient id. Callers that hold the base `patients/docs` ACL can
     * drive batch and admin flows across patients. Otherwise the session
     * must be scoped to that specific patient via `set_pid`. Session-pid
     * context alone is NOT an authorization boundary — it's purely UI
     * context — so it only grants access on top of the ACL check or when
     * no ACL is held but the user is already inside that patient's chart.
     */
    private function isPatientAuthorized(int $pid): bool
    {
        if ($pid <= 0) {
            return false;
        }
        if (AclMain::aclCheckCore('patients', 'docs') !== false) {
            return true;
        }
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $rawSessionPid = $session->get('pid');
        $sessionPid = is_numeric($rawSessionPid) ? (int) $rawSessionPid : 0;
        return $sessionPid > 0 && $sessionPid === $pid;
    }

    /**
     * Return true when the current session is permitted to read the given
     * Document (combines category-level `can_access()` with the per-patient
     * check against the document's `foreign_id`).
     */
    private function isDocumentAuthorized(Document $doc): bool
    {
        if (!$doc->can_access()) {
            return false;
        }
        $foreignId = $doc->get_foreign_id();
        return $this->isPatientAuthorized(is_numeric($foreignId) ? (int) $foreignId : 0);
    }

    /**
     * Return true when the current session is allowed to review, approve,
     * discard, or delete the given audit_master row. For rows already bound
     * to a patient the caller must hold patient-record access to the stored
     * `pid`, and a claimed pid that disagrees with the stored pid is
     * rejected so an attacker cannot slip a foreign pid alongside an amid
     * they are legitimately scoped to. For pre-approval staging rows the
     * caller must hold the shared `patients/docs` ACL (on top of the
     * module-level `send_to_hie` or `admin/super` gate enforced in
     * Carecoordination\Module.php), and when the caller is binding the row
     * to a target pid that pid must itself be patient-authorized.
     */
    private function isAuditMasterAccessible(int $auditMasterId, ?int $claimedPid = null): bool
    {
        if ($auditMasterId <= 0) {
            return false;
        }
        $row = QueryUtils::fetchRecords(
            'SELECT pid FROM audit_master WHERE id = ?',
            [$auditMasterId]
        );
        if ($row === []) {
            return false;
        }
        $storedPid = (int) ($row[0]['pid'] ?? 0);
        if ($storedPid > 0) {
            // Row is bound to a real patient. The claimed pid, if non-zero,
            // must match the stored pid; a caller otherwise authorized for
            // the stored pid can not slip a different pid into the write
            // path via the request. Gate on patient access to the stored
            // pid — once the row carries a pid, the usual per-patient
            // boundary applies to everyone.
            if ($claimedPid !== null && $claimedPid > 0 && $claimedPid !== $storedPid) {
                return false;
            }
            return $this->isPatientAuthorized($storedPid);
        }
        // Pre-approval staging row with no bound patient. The uploader is
        // not recorded on audit_master — `user_id` is reserved for the
        // approver/denier per the schema comment and is not written during
        // import — so there is no row-local identity to gate on. Defer to
        // the shared patients/docs ACL (any clinic user in the Care
        // Coordination group can finish a review another user started),
        // then require patient access to the claimed target pid when the
        // caller is binding one.
        if (AclMain::aclCheckCore('patients', 'docs') === false) {
            return false;
        }
        if ($claimedPid === null || $claimedPid <= 0) {
            // View / discard — no patient binding being made.
            return true;
        }
        return $this->isPatientAuthorized($claimedPid);
    }

    /**
     * Return the stored `pid` on the given audit_master row, or 0 if the
     * row does not exist. Controllers call this after
     * isAuditMasterAccessible() has already approved the row so the
     * approve-path write can be bound to the trusted stored pid rather
     * than the request-supplied `pid` (which may be zero or omitted).
     * For pre-approval rows (storedPid === 0) the caller is expected to
     * fall back to the claimed pid that `isAuditMasterAccessible` just
     * patient-authorized against.
     */
    private function resolveAuditMasterStoredPid(int $auditMasterId): int
    {
        if ($auditMasterId <= 0) {
            return 0;
        }
        $row = QueryUtils::fetchRecords(
            'SELECT pid FROM audit_master WHERE id = ?',
            [$auditMasterId]
        );
        return isset($row[0]['pid']) && is_numeric($row[0]['pid']) ? (int) $row[0]['pid'] : 0;
    }

    /**
     * Return the id of the single Document linked to the given audit_master
     * row, or 0 when no document is linked. The caller is expected to have
     * already passed isAuditMasterAccessible() against the same amid — this
     * helper exists so controllers can bind the audit event's `doc_id` and
     * the `UPDATE documents SET foreign_id` write to the audit's own
     * document rather than trusting an attacker-supplied request parameter
     * (which can otherwise reassign an unrelated document's foreign_id to
     * the caller's own patient).
     */
    private function resolveAuditMasterDocumentId(int $auditMasterId): int
    {
        if ($auditMasterId <= 0) {
            return 0;
        }
        $row = QueryUtils::fetchRecords(
            'SELECT id FROM documents WHERE audit_master_id = ? ORDER BY id ASC LIMIT 1',
            [$auditMasterId]
        );
        return isset($row[0]['id']) && is_numeric($row[0]['id']) ? (int) $row[0]['id'] : 0;
    }

    /**
     * Return `[authUser, authProvider]` as a pair of typed strings for the
     * current session, suitable for passing to EventAuditLogger::newEvent().
     * The raw session values are `mixed`; narrow them once here instead of
     * repeating the cast at every audit call site.
     *
     * @return array{string, string}
     */
    private function auditIdentity(): array
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $authUser = is_string($u = $session->get('authUser')) ? $u : '';
        $authProvider = is_string($p = $session->get('authProvider')) ? $p : '';
        return [$authUser, $authProvider];
    }
}
