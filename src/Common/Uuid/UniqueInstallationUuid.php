<?php

/**
 * uniqueInstallationUuid.php class
 *
 *    Support for unique installation UUID. Will create it if it doesn't yet exists.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Uuid;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

class UniqueInstallationUuid
{
    public static function getUniqueInstallationUuid(): string
    {
        // Return the loaded global setting if it is set
        $loaded = OEGlobalsBag::getInstance()->getString('unique_installation_id');
        if ($loaded !== '') {
            return $loaded;
        }

        // Otherwise read it from the globals table
        $select = <<<'SQL'
            SELECT `gl_value` FROM `globals` WHERE `gl_name` = 'unique_installation_id'
            SQL;
        $row = QueryUtils::querySingleRow($select);
        $stored = is_array($row) ? $row['gl_value'] ?? null : null;
        if (is_string($stored) && $stored !== '') {
            return $stored;
        }

        // Neither has one yet, so create it and store it
        $uuid = ServiceContainer::getUuidFactory()->uuid4()->toString();
        $update = <<<'SQL'
            UPDATE `globals` SET `gl_value` = ? WHERE `gl_name` = 'unique_installation_id'
            SQL;
        QueryUtils::sqlStatementThrowException($update, [$uuid]);
        return $uuid;
    }
}
