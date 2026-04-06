<?php

/**
 * VersionService
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2017 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

class VersionService extends BaseService implements VersionServiceInterface
{
    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct('version');
    }

    /**
     * @return array<string, scalar> the sole version entry in the database.
     */
    public function fetch(): array
    {
        $rows = QueryUtils::fetchRecords("SELECT * FROM `version`");
        return $rows[0] ?? [];
    }

    public function getSoftwareVersion(): SoftwareVersion
    {
        return SoftwareVersion::fromGlobals(OEGlobalsBag::getInstance());
    }

    public function getSchemaVersion(): SchemaVersion
    {
        return SchemaVersion::fromDatabaseRow($this->fetch());
    }

    /**
     * Updates the sole version entry in the database. If the release contains
     * a patch file, also updates the real patch indicator.
     *
     * @param $version array the new version entry.
     * @return void.
     */
    public function update(array $version): void
    {
        if (!$this->canRealPatchBeApplied($version)) {
            $version['v_realpatch'] = 0;
        }

        sqlStatement("DELETE FROM `version`");

        $query = $this->buildInsertColumns($version);
        $sql = "INSERT INTO `version` SET ";
        $sql .= $query['set'];
        sqlStatement($sql, $query['bind']);
    }

    /**
     * @param $version array
     * @return bool if the release contains a patch file or not.
     */
    public function canRealPatchBeApplied(array $version): bool
    {
        //Collected below function call to a variable, since unable to directly include
        // function calls within empty() in php versions < 5.5 .
        $version_getrealpatch = $version['v_realpatch'];
        return !empty($version_getrealpatch) && ($version['v_realpatch'] != "") && ($version['v_realpatch'] > 0);
    }
}
