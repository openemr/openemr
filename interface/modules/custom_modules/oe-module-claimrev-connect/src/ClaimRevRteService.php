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

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\Modules\ClaimRevConnector\EligibilityData;

class ClaimRevRteService
{
    public static function createEligibilityFromAppointment($eid)
    {
        $row = EligibilityData::getPatientIdFromAppointment($eid);
        if ($row != null) {
            $pid = $row["pc_pid"];
            $appointmentDate = $row["appointmentDate"];
            $facilityId = $row["facilityId"];
            $providerId = $row["providerId"];

            $requestObjects = EligibilityObjectCreator::buildObject($pid, "", $appointmentDate, $facilityId, $providerId);
            EligibilityObjectCreator::saveToDatabase($requestObjects, $pid);
        }
    }
}
