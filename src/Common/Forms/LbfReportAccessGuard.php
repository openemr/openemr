<?php

/**
 * Access-context checks for Layout Based Form reports.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Forms;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class LbfReportAccessGuard
{
    public static function shouldSkipRestrictedForm(SessionInterface $session): bool
    {
        return $session->has('patient_portal_onsite_two');
    }
}
