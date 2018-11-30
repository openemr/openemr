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
