<?php

/**
 * Claims search page for ClaimRev integration
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\Modules\ClaimRevConnector\ClaimSearch;
use OpenEMR\Modules\ClaimRevConnector\ClaimSearchModel;

class ClaimsPage
{
    /**
     * @param array<string, mixed> $postData
     */
    public static function searchClaims(array $postData)
    {
        $firstName = $postData['patFirstName'] ?? '';
        $lastName = $postData['patLastName'] ?? '';
        $startDate = $postData['startDate'] ?? '';
        $endDate = $postData['endDate'] ?? '';

        $model = new ClaimSearchModel();
        $model->patientFirstName = $firstName;
        $model->patientLastName = $lastName;
        $model->receivedDateStart = $startDate;
        $model->receivedDateEnd = $endDate;

        $data = ClaimSearch::search($model);
        return $data;
    }
}
