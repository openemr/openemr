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
     * discard, or delete the given audit_master row. The caller must either
     * be the user who uploaded the referenced CCDA/CCR (pre-approval phase
     * where the row's `pid` is still 0) or hold patient-record access to
     * the stored `pid` the row carries. When $claimedPid is supplied it is
     * validated against the stored row so an attacker cannot slip a
     * foreign pid alongside an amid they are legitimately scoped to.
     */
    private function isAuditMasterAccessible(int $auditMasterId, ?int $claimedPid = null): bool
    {
        if ($auditMasterId <= 0) {
            return false;
        }
        $row = QueryUtils::fetchRecords(
            'SELECT pid, user_id FROM audit_master WHERE id = ?',
            [$auditMasterId]
        );
        if ($row === []) {
            return false;
        }
        $storedPid = (int) ($row[0]['pid'] ?? 0);
        $uploaderId = (int) ($row[0]['user_id'] ?? 0);
        if ($claimedPid !== null && $claimedPid > 0 && $claimedPid !== $storedPid) {
            return false;
        }
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $authUserId = (int) ($session->get('authUserID') ?? 0);
        if ($uploaderId > 0 && $authUserId === $uploaderId) {
            return true;
        }
        if ($storedPid <= 0) {
            return false;
        }
        return $this->isPatientAuthorized($storedPid);
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
