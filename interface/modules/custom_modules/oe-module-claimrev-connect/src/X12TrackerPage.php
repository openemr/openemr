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

class X12TrackerPage
{
    public static function searchX12Tracker($postData)
    {
        $startDate = $_POST['startDate'];
        $endDate = $_POST['endDate'];

        $sql = "SELECT * FROM x12_remote_tracker where created_at BETWEEN ? AND ?";
        $files = sqlStatementNoLog($sql, array($startDate,$endDate));

        return $files;
    }
}
