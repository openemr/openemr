<?php

/**
 * VersionService
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2017 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

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
     * @return array the sole version entry in the database.
     */
    public function fetch(): array
    {
        return sqlQuery("SELECT * FROM `version`");
    }

    /**
     * Return the running software version as a string.
     *
     * Reads from version.php globals so the displayed version always matches
     * the running code without requiring a database round-trip.
     *
     * @param bool $includeTag      Append the pre-release tag (e.g. "-dev")
     * @param bool $includeRealpatch Append the real-patch suffix in parentheses
     * @return string e.g. "8.0.1", "8.0.1-dev", "8.0.1 (1.0.1.2)"
     */
    public function getSoftwareVersion(bool $includeTag = true, bool $includeRealpatch = true): string
    {
        $globals = OEGlobalsBag::getInstance();
        $major     = $globals->getString('v_major', '0');
        $minor     = $globals->getString('v_minor', '0');
        $patch     = $globals->getString('v_patch', '0');
        $tag       = $globals->getString('v_tag', '');
        $realpatch = $globals->getString('v_realpatch', '');

        $string = "{$major}.{$minor}.{$patch}";
        if ($includeTag) {
            $string .= $tag;
        }
        if ($includeRealpatch && $realpatch !== '') {
            $string .= " ({$realpatch})";
        }
        return $string;
    }

    /**
     * Return the database schema version as a string.
     *
     * This is the version recorded in the `version` table — it reflects what
     * migrations have been applied, which may lag behind the running code
     * immediately after an upgrade.
     */
    public function getSchemaVersion(): string
    {
        $row = $this->fetch();
        return implode('.', [
            $row['v_major'] ?? '0',
            $row['v_minor'] ?? '0',
            $row['v_patch'] ?? '0',
        ]);
    }

    /**
     * @deprecated Use getSoftwareVersion() instead.
     * @param bool $includeTag
     * @param bool $includeRealpatch
     * @return string
     */
    public function asString(bool $includeTag = true, bool $includeRealpatch = true): string
    {
        return $this->getSoftwareVersion($includeTag, $includeRealpatch);
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
