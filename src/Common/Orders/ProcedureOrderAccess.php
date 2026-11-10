<?php

/**
 * Procedure-order access guard for encounter sensitivity.
 *
 * A procedure order's results belong to the encounter the order was placed
 * in. When that encounter carries a sensitivity level
 * (`form_encounter.sensitivity`, e.g. "high"), viewing the order's results
 * requires the matching `sensitivities` ACL. This is the same rule
 * `interface/patient_file/encounter/load_form.php` applies to encounter forms.
 *
 * Design: the decision is isolated in the pure `isSensitivityPermitted()`
 * method (unit-testable, no DB, no ACL, no process termination).
 * `assertCanViewOrder()` composes it with a DB lookup and the ACL check, and
 * terminates the request via `AccessDeniedHelper::denyWithTemplate()` when
 * access is not permitted.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Orders;

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;

final class ProcedureOrderAccess
{
    /**
     * Pure decision: may a user view an order whose encounter has this sensitivity?
     *
     * `null` means the order is not linked to an encounter (or does not exist)
     * and an empty string means the encounter has no sensitivity set; neither
     * restricts access. Any other value is decided by `$hasSensitivityAcl`,
     * which is only consulted in that case.
     *
     * @param callable(string): bool $hasSensitivityAcl
     */
    public static function isSensitivityPermitted(?string $sensitivity, callable $hasSensitivityAcl): bool
    {
        if ($sensitivity === null || $sensitivity === '') {
            return true;
        }
        return $hasSensitivityAcl($sensitivity);
    }

    /**
     * Sensitivity of the encounter an order was placed in. Returns null when
     * the order does not exist or is not linked to an encounter.
     */
    public static function fetchEncounterSensitivity(int $orderId): ?string
    {
        $row = QueryUtils::querySingleRow(
            "SELECT fe.sensitivity FROM procedure_order AS po " .
            "JOIN form_encounter AS fe ON fe.pid = po.patient_id AND fe.encounter = po.encounter_id " .
            "WHERE po.procedure_order_id = ?",
            [$orderId],
        );
        if (!is_array($row)) {
            return null;
        }
        $sensitivity = $row['sensitivity'] ?? null;
        return is_string($sensitivity) ? $sensitivity : null;
    }

    /**
     * Terminate the request unless the current user may view this order under
     * its encounter's sensitivity.
     *
     * @param string $pageTitle Translated title for the access-denied page.
     */
    public static function assertCanViewOrder(int $orderId, string $pageTitle): void
    {
        $permitted = self::isSensitivityPermitted(
            self::fetchEncounterSensitivity($orderId),
            static fn(string $sensitivity): bool => AclMain::aclCheckCore('sensitivities', $sensitivity),
        );
        if (!$permitted) {
            AccessDeniedHelper::denyWithTemplate(
                sprintf('Encounter sensitivity ACL failed for procedure order %d', $orderId),
                $pageTitle,
            );
        }
    }
}
