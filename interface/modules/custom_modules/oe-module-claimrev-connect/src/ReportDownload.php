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

use OpenEMR\Services\BaseService;
use OpenEMR\Modules\ClaimRevConnector\ClaimRevApi;

class ReportDownload extends BaseService
{
    public static function getWaitingFiles()
    {
        $reportTypes = array("999", "277");
        $siteDir = $GLOBALS['OE_SITE_DIR'];
        //should be something like '/var/www/localhost/htdocs/openemr/sites/default'

        $token = ClaimRevApi::GetAccessToken();
        foreach ($reportTypes as $reportType) {
            $reportFolder = "f" . $reportType;
            if ($reportType == "999") {
                $reportFolder = "f997";
            }

            $savePath = $siteDir . '/documents/edi/history/' . $reportFolder . '/';

            //$savePath = $siteDir . '/documents/edi/';
            if (!file_exists($savePath)) {
                // Create a direcotry
                mkdir($savePath, 0777, true);
            }

            $datas = ClaimRevApi::getReportFiles($reportType, $token);
            if (is_array($datas)) {
                foreach ($datas as $data) {
                    if (property_exists($data, 'fileText')) {
                        $fileText = $data->fileText;
                        $fileName = $data->fileName ;
                        $filePathName =  $savePath . $fileName . '.txt';
                        file_put_contents($filePathName, $fileText);
                        chmod($filePathName, 0777);
                    } else {
                        error_log("Unable to find property FileText in ClaimRevConnector.ReportDownload.getWaitingFiles");
                    }
                }
            }
        }
    }
    public static function download835($objectId)
    {
        $siteDir = $GLOBALS['OE_SITE_DIR'];
        //should be something like '/var/www/localhost/htdocs/openemr/sites/default'

        $token = ClaimRevApi::GetAccessToken();
        $savePath = $siteDir . '/documents/era/';

        //$savePath = $siteDir . '/documents/edi/';
        if (!file_exists($savePath)) {
            // Create a direcotry
            mkdir($savePath, 0777, true);
        }

        $data = ClaimRevApi::getFileForDownload($objectId, $token);

        if (property_exists($data, 'fileText')) {
            $fileText = $data->fileText;
            $fileName = $objectId . ".edi";
            $filePathName =  $savePath . $fileName;
            file_put_contents($filePathName, $fileText);
            chmod($filePathName, 0777);
        } else {
            error_log("Unable to find property FileText in ClaimRevConnector.ReportDownload.getWaitingFiles");
        }
    }
}
