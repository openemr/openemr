<?php

/**
 * X12 tracker search page for ClaimRev integration
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

class X12TrackerPage
{
    /**
     * @param array<string, mixed> $postData
     */
    public static function searchX12Tracker(array $postData)
    {
        $startDate = $postData['startDate'] ?? '';
        $endDate = $postData['endDate'] ?? '';

        $sql = "SELECT * FROM x12_remote_tracker where created_at BETWEEN ? AND ?";
        $files = sqlStatementNoLog($sql, [$startDate,$endDate]);

        return $files;
    }
}
