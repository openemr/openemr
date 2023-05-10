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

use OpenEMR\Modules\ClaimRevConnector\EraSearch;

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
    public static function downloadEra($id)
    {
        $data = EraSearch::downloadEra($id);
        $data->fileName = $data->ediType . "-" . $data->payerNumber . "-" .  convert_safe_file_dir_name($id) . ".txt";

        return $data;
    }
}
