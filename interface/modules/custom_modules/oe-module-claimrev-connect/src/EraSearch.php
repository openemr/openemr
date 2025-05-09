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

use OpenEMR\Modules\ClaimRevConnector\ClaimRevApi;
use OpenEMR\Common\Crypto\CryptoGen;

class EraSearch
{
    public static function search($search)
    {
        $token = ClaimRevApi::getAccessToken();
        $data = ClaimRevApi::searchDownloadableFiles($search, $token);

        return $data;
    }

    public static function downloadEra($objectId)
    {
        $token = ClaimRevApi::getAccessToken();
        $data = ClaimRevApi::getFileForDownload($objectId, $token);
        return $data;
    }
}
