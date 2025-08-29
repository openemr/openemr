<?php

/**
 * Version Service Interface
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2017 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

interface VersionServiceInterface extends BaseServiceInterface
{
    /**
     * @return array the sole version entry in the database.
     */
    public function fetch(): array;

    /**
     * Return the compounded major, minor, patch and tag verions as a string
     *
     * @var $includeTag bool Include the tag
     * @var $includeRealpatch bool Include the realpatch
     * @returns string Dot separated major, minor, patch version string (tag at end, if included)
     */
    public function asString(bool $includeTag = true, bool $includeRealpatch = true): string;

    /**
     * Updates the sole version entry in the database. If the release contains
     * a patch file, also updates the real patch indicator.
     *
     * @param $version array the new version entry.
     * @return void.
     */
    public function update(array $version): void;

    /**
     * @param $version array
     * @return bool if the release contains a patch file or not.
     */
    public function canRealPatchBeApplied(array $version): bool;
}
