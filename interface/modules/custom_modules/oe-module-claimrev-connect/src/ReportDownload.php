<?php

/**
 * Report download service for ClaimRev.
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

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\BaseService;

class ReportDownload extends BaseService
{
    public static function getWaitingFiles(): void
    {
        $reportTypes = ['999', '277'];
        $siteDir = $GLOBALS['OE_SITE_DIR'];

        try {
            $api = ClaimRevApi::makeFromGlobals();
        } catch (ClaimRevException) {
            return;
        }

        foreach ($reportTypes as $reportType) {
            $reportFolder = 'f' . $reportType;
            if ($reportType === '999') {
                $reportFolder = 'f997';
            }

            $savePath = $siteDir . '/documents/edi/history/' . $reportFolder . '/';

            if (!file_exists($savePath)) {
                mkdir($savePath, 0750, true);
            }

            try {
                $datas = $api->getReportFiles($reportType);
            } catch (ClaimRevApiException) {
                continue;
            }

            foreach ($datas as $data) {
                if (isset($data['fileText'])) {
                    $fileText = $data['fileText'];
                    $fileName = $data['fileName'];
                    $filePathName = $savePath . $fileName . '.txt';
                    file_put_contents($filePathName, $fileText);
                    chmod($filePathName, 0640);
                } else {
                    (new SystemLogger())->error('Unable to find property fileText in response', ['class' => self::class, 'method' => 'getWaitingFiles']);
                }
            }
        }
    }

    public static function download835(string $objectId): void
    {
        $siteDir = $GLOBALS['OE_SITE_DIR'];

        try {
            $api = ClaimRevApi::makeFromGlobals();
        } catch (ClaimRevException) {
            return;
        }

        $savePath = $siteDir . '/documents/era/';

        if (!file_exists($savePath)) {
            mkdir($savePath, 0750, true);
        }

        try {
            $data = $api->getFileForDownload($objectId);
        } catch (ClaimRevApiException $e) {
            (new SystemLogger())->error('Unable to download file', ['class' => self::class, 'method' => 'download835', 'exception' => $e->getMessage()]);
            return;
        }

        if (isset($data['fileText'])) {
            $fileText = $data['fileText'];
            $fileName = $objectId . '.edi';
            $filePathName = $savePath . $fileName;
            file_put_contents($filePathName, $fileText);
            chmod($filePathName, 0640);
        } else {
            (new SystemLogger())->error('Unable to find property fileText in response', ['class' => self::class, 'method' => 'download835']);
        }
    }
}
