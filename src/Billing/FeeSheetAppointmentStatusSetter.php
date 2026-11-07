<?php

/**
 * Sets the visit's appointment to the status the fee sheet offered after Save.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\AppointmentService;

/**
 * Only the appointment the fee sheet would ask about is set: the one
 * FeeSheetAppointmentStatusPrompt picks for the encounter's date, for an encounter of
 * the given patient. Anything else (another appointment, another patient's encounter,
 * an appointment no longer asked about) is refused.
 */
final readonly class FeeSheetAppointmentStatusSetter
{
    public function __construct(
        private FeeSheetAppointmentStatusPrompt $prompt,
        private AppointmentService $appointments,
    ) {
    }

    /**
     * @param string $username the user making the change, as the Flow Board records it
     * @return bool false when the appointment isn't the one the fee sheet would ask about
     */
    public function set(int $pid, int $encounter, int $eid, string $username): bool
    {
        $visitDate = QueryUtils::fetchSingleValue(
            'SELECT DATE(date) AS visit_date FROM form_encounter WHERE pid = ? AND encounter = ?',
            'visit_date',
            [$pid, $encounter]
        );
        if (!is_string($visitDate)) {
            return false;
        }
        $appointment = $this->prompt->appointmentFor($pid, $visitDate);
        if ($appointment === null || $appointment->eid !== $eid) {
            return false;
        }

        $this->appointments->updateAppointmentStatus($appointment->eid, $appointment->statusCode, $username, $encounter);

        return true;
    }
}
