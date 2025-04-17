<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\Modules\ClaimRevConnector\ClaimSearch;
use OpenEMR\Modules\ClaimRevConnector\ClaimSearchModel;

class ClaimsPage
{
    public static function searchClaims($postData)
    {
        $firstName = $_POST['patFirstName'];
        $lastName = $_POST['patLastName'];
        $startDate = $_POST['startDate'];
        $endDate = $_POST['endDate'];

        $model = new ClaimSearchModel();
        $model->patientFirstName = $firstName;
        $model->patientLastName = $lastName;
        $model->receivedDateStart = $startDate;
        $model->receivedDateEnd = $endDate;

        $data = ClaimSearch::search($model);
        return $data;
    }
}
