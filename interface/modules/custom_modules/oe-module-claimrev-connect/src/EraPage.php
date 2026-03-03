<?php

/**
 * ERA page controller
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\Modules\ClaimRevConnector\EraSearch;
use OpenEMR\Modules\ClaimRevConnector\ClaimRevApiException;

class EraPage
{
    public static function searchEras($postData)
    {
        $startDate = $postData['startDate'];
        $endDate = $postData['endDate'];
        $fileStatus = $postData['downloadStatus'];

        $model = new FileSearchModel();
        $model->fileStatus = intval($fileStatus);
        $model->ediType = "835";
        $model->receivedDateStart = $startDate;
        $model->receivedDateEnd = $endDate;

        if ($model->receivedDateStart == "") {
            $model->receivedDateStart = null;
        }
        if ($model->receivedDateEnd == "") {
            $model->receivedDateEnd = null;
        }
        $data = EraSearch::search($model);
        return $data;
    }
    /**
     * Download an ERA file by ID.
     *
     * @param string $id ERA identifier (alphanumeric and hyphens only)
     * @return array<string, mixed>|false
     * @throws \InvalidArgumentException If the ID format is invalid
     * @throws ClaimRevApiException If the API call fails
     */
    public static function downloadEra(string $id): array|false
    {
        if ($id === '' || !preg_match('/^[a-zA-Z0-9\-]+$/', $id)) {
            throw new \InvalidArgumentException('Invalid ERA ID format');
        }

        $data = EraSearch::downloadEra($id);
        if ($data === false) {
            return false;
        }

        $ediType = $data['ediType'] ?? '';
        $payerNumber = $data['payerNumber'] ?? '';
        $data['fileName'] = $ediType . '-' . $payerNumber . '-' . convert_safe_file_dir_name($id) . '.txt';

        return $data;
    }
}
