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
