<?php

/**
 * WenoPharmacyService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @copyright Copyright (c) 2023 omega systems group international <info@omegasystemsgroup.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule\Services;

use Exception;

class WenoLogService
{
    public function __construct()
    {
    }

    public function getLastPrescriptionLogStatus()
    {
        $params  = "prescription";
        $sql = "SELECT * FROM weno_download_log WHERE ";
        $sql .= "VALUE = ? ORDER BY created_at DESC LIMIT 1";

        $result = sqlQuery($sql, [$params]);

        return $result;
    }

    public function getLastPharmacyDownloadStatus()
    {
        $params = "pharmacy";
        $sql = "SELECT * FROM weno_download_log WHERE ";
        $sql .= "VALUE = ? ORDER BY created_at DESC LIMIT 1";

        $result = sqlQuery($sql, [$params]);

        return $result;
    }

    public function insertWenoLog($value, $status)
    {
        $sql = "INSERT INTO weno_download_log SET ";
        $sql .= "value = ?, ";
        $sql .= "status = ? ";

        try {
            sqlInsert($sql, [$value, $status]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
