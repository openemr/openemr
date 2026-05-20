<?php

/**
 * Resolves the active-patient context for AJAX endpoints.
 *
 * OpenEMR scopes a user's chart-side requests to the patient currently open in
 * their session (set by {@see set_pid()} during chart navigation). AJAX
 * endpoints that act on a pid passed in POST should refuse to operate on any
 * other patient, otherwise a billing-permitted user can swap the pid and
 * touch another patient's data.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\Common\Session\SessionWrapperFactory;

final class PatientContext
{
    public static function activePid(): int
    {
        $raw = SessionWrapperFactory::getInstance()->getActiveSession()->get('pid');
        if (is_int($raw)) {
            return $raw > 0 ? $raw : 0;
        }
        if (is_string($raw) && $raw !== '' && ctype_digit($raw)) {
            return (int) $raw;
        }
        return 0;
    }

    public static function pidMatchesActivePatient(int $pid): bool
    {
        return $pid > 0 && $pid === self::activePid();
    }
}
