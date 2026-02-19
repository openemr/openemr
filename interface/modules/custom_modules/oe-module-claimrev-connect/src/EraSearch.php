<?php

/**
 * ERA file search wrapper for ClaimRev API.
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

class EraSearch
{
    /**
     * Search for downloadable ERA files.
     *
     * @return array<string, mixed>|false Returns false on error for backward compatibility
     */
    public static function search(object $search): array|false
    {
        try {
            $api = ClaimRevApi::makeFromGlobals();
            return $api->searchDownloadableFiles($search);
        } catch (ClaimRevException) {
            return false;
        }
    }

    /**
     * Download an ERA file by object ID.
     *
     * @return array<string, mixed>|false Returns false on error for backward compatibility
     */
    public static function downloadEra(string $objectId): array|false
    {
        try {
            $api = ClaimRevApi::makeFromGlobals();
            return $api->getFileForDownload($objectId);
        } catch (ClaimRevException) {
            return false;
        }
    }
}
