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
     * @return array{v_major: int, v_minor: int, v_patch: int, v_realpatch: int, v_tag: string, v_database: int, v_acl: int}
     */
    public function fetch(): array
    {
        $row = QueryUtils::querySingleRow("SELECT * FROM `version`");
        if (!is_array($row)) {
            throw new \RuntimeException('Missing version entry in database');
        }

        $major = $row['v_major'] ?? null;
        $minor = $row['v_minor'] ?? null;
        $patch = $row['v_patch'] ?? null;
        $realpatch = $row['v_realpatch'] ?? null;
        $tag = $row['v_tag'] ?? null;
        $database = $row['v_database'] ?? null;
        $acl = $row['v_acl'] ?? null;

        if (
            !is_numeric($major) || !is_numeric($minor) || !is_numeric($patch)
            || !is_numeric($realpatch) || !is_numeric($database) || !is_numeric($acl)
        ) {
            throw new \RuntimeException('Non-numeric version data in database');
        }

        if (!is_string($tag)) {
            throw new \RuntimeException("Non-string v_tag in version table");
        }

        return [
            'v_major' => (int) $major,
            'v_minor' => (int) $minor,
            'v_patch' => (int) $patch,
            'v_realpatch' => (int) $realpatch,
            'v_tag' => $tag,
            'v_database' => (int) $database,
            'v_acl' => (int) $acl,
        ];
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
     * Update the sole version entry in the database. If the release contains
     * a patch file, also update the real patch indicator.
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
