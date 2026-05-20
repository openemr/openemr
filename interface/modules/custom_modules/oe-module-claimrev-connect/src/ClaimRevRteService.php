<?php

/**
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\Modules\ClaimRevConnector\EligibilityData;

class ClaimRevRteService
{
    public static function createEligibilityFromAppointment(int|string $eid): void
    {
        $row = EligibilityData::getPatientIdFromAppointment((string) $eid);
        if ($row === null) {
            return;
        }

        $pid = $row['pc_pid'];
        $appointmentDate = $row['appointmentDate'];
        $facilityId = $row['facilityId'];
        $providerId = $row['providerId'];

        $requestObjects = EligibilityObjectCreator::buildObject(
            $pid,
            "",
            $appointmentDate !== '' ? $appointmentDate : null,
            $facilityId !== 0 ? $facilityId : null,
            $providerId !== 0 ? $providerId : null,
        );
        EligibilityObjectCreator::saveToDatabase($requestObjects, $pid);
    }
}
