<?php

/**
 * X12 tracker search page for ClaimRev integration
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\Common\Database\QueryUtils;

class X12TrackerPage
{
    /**
     * @param  array<string, mixed> $postData
     * @return list<array<string, mixed>>
     */
    public static function searchX12Tracker(array $postData): array
    {
        $startDate = TypeCoerce::asString($postData['startDate'] ?? '') . ' 00:00:00';
        $endDate = TypeCoerce::asString($postData['endDate'] ?? '') . ' 23:59:59';

        $sql = "SELECT * FROM x12_remote_tracker where created_at BETWEEN ? AND ?";
        $rows = QueryUtils::fetchRecordsNoLog($sql, [$startDate, $endDate]);

        $out = [];
        foreach ($rows as $row) {
            /** @var array<string, mixed> $row */
            $out[] = $row;
        }
        return $out;
    }
}
