<?php

/**
 * Campaign Service - Handles MedEx campaign operations
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018 MedEx <support@MedExBank.com>
 * @license   Proprietary - All Rights Reserved
 */

namespace MedExApi\Services;

use OpenEMR\Common\Database\QueryUtils;

class CampaignService extends BaseService
{
    /**
     * Get campaign events
     *
     * @param string $token API token
     * @return array<string,mixed>|false
     */
    public function events(string $token): array|false
    {
        $prefsRecords = QueryUtils::fetchRecords("SELECT * FROM medex_prefs");
        $info = $prefsRecords[0] ?? null;

        if (
            empty($info) ||
            empty($info['ME_username']) ||
            empty($info['ME_api_key']) ||
            empty($info['MedEx_id'])
        ) {
            return false;
        }

        $results = json_decode((string) $info['status'], true);
        return $results['status']['campaigns'] ?? false;
    }
}
