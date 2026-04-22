<?php

/**
 * Version Service Interface
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

interface VersionServiceInterface extends BaseServiceInterface
{
    /**
     * @return array{v_major: int, v_minor: int, v_patch: int, v_realpatch: int, v_tag: string, v_database: int, v_acl: int}
     */
    public function fetch(): array;

    public function getSoftwareVersion(): SoftwareVersion;

    public function getSchemaVersion(): SchemaVersion;

    /**
     * Update the sole version entry in the database. If the release contains
     * a patch file, also update the real patch indicator.
     */
    public function update(array $version): void;

    /**
     * @return bool whether the release contains a patch file
     */
    public function canRealPatchBeApplied(array $version): bool;
}
