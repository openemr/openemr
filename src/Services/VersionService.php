<?php

/**
 * VersionService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2017 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

class VersionService extends BaseService
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
     * Return the compounded major, minor, patch and tag verions as a string
     *
     * @var $includeTag bool Include the tag
     * @var $includeRealpatch bool Include the realpatch
     * @returns string Dot separated major, minor, patch version string (tag at end, if included)
     */
    public function asString(bool $includeTag = true, bool $includeRealpatch = true): string
    {
        $v = $this->fetch();
        $string = "{$v['v_major']}.{$v['v_minor']}.{$v['v_patch']}";
        $string = ($includeTag == true) ? $string . "{$v['v_tag']}" : $string;
        if ($includeRealpatch && (!empty($v['v_realpatch']))) {
            $string .= " (" . $v['v_realpatch'] . ")";
        }
        return $string;
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
