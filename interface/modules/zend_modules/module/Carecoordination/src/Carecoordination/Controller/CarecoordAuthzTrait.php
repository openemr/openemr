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
     * given patient id. If the session has an active patient context, that
     * patient id must match. Otherwise require `patients/docs` so admin and
     * scope-wide readers can continue to drive batch flows.
     */
    private function isPatientAuthorized(int $pid): bool
    {
        if ($pid <= 0) {
            return false;
        }
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $sessionPid = (int) ($session->get('pid') ?? 0);
        if ($sessionPid > 0) {
            return $sessionPid === $pid;
        }
        return AclMain::aclCheckCore('patients', 'docs') !== false;
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
        return $this->isPatientAuthorized((int) $doc->get_foreign_id());
    }
}
