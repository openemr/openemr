<?php
/**
 * VersionService
 *
 * Copyright (C) 2017 Matthew Vita <matthewvita48@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @link    http://www.open-emr.org
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\Connector;
use OpenEMR\Common\Logging\Logger;
use OpenEMR\Entities\Version;

class VersionService
{
    /**
     * Logger used primarily for logging events that are of interest to
     * developers.
     */
    private $logger;

    /**
     * The version repository to be used for db CRUD operations.
     */
    private $repository;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->logger = new Logger("\OpenEMR\Services\VersionService");
        $database = Connector::Instance();
        $entityManager = $database->entityManager;
        $this->repository = $entityManager->getRepository('\OpenEMR\Entities\Version');
    }

    /**
     * Before potentially making any updates to the system, we need to ensure
     * the version table exists.
     *
     * @return bool
     */
    public function doesTableExist()
    {
        return $this->repository->doesTableExist();
    }

    /**
     * @return the sole version entry in the database.
     */
    public function fetch()
    {
        $version = $this->repository->findFirst();

        if (empty($version)) {
            $this->logger->error("No version found");
            return null;
        }

        return $version;
    }

    /**
     * Updates the sole version entry in the database. If the release contains
     * a patch file, also updates the real patch indicator.
     *
     * @param $version the new version entry.
     * @return true/false for if the update went through.
     */
    public function update(Version $version)
    {
        $this->logger->debug("Updating version entry");
        if (!$this->canRealPatchBeApplied($version)) {
            $version->setRealPatch(0);
        }

        return $this->repository->update($version);
    }

    /**
     * @return bool if the release contains a patch file or not.
     */
    public function canRealPatchBeApplied(Version $version)
    {
        $this->logger->debug("Determining if a real patch can be applied");
        //Collected below function call to a variable, since unable to directly include
        // function calls within empty() in php versions < 5.5 .
        $version_getrealpatch = $version->getRealPatch();
        return !empty($version_getrealpatch) && ($version->getRealPatch() != "") && ($version->getRealPatch() > 0);
    }
}
