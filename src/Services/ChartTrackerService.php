<?php
/**
 * Chart tracker service.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Victor Kofia <victor.kofia@gmail.com>
 * @copyright Copyright (c) 2017 Victor Kofia <victor.kofia@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


namespace OpenEMR\Services;

use OpenEMR\Common\Database\Connector;
use OpenEMR\Common\Logging\Logger;

class ChartTrackerService
{

    /**
     * Logger used primarily for logging events that are of interest to
     * developers.
     */
    private $logger;

    /**
     * The chart tracker repository to be used for db CRUD operations.
     */
    private $repository;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->logger = new Logger("\OpenEMR\Services\ChartTrackerService");
        $database = Connector::Instance();
        $entityManager = $database->entityManager;
        $this->repository = $entityManager->getRepository('\OpenEMR\Entities\ChartTracker');
    }

    /**
     * Add chart tracker table entry.
     *
     * @param array (pid, date, userid, location).
     * @return the pid.
     */
    public function trackPatientLocation($patientLocation)
    {
        $patientLocation->setPid(add_escape_custom($patientLocation->getPid()));
        $patientLocation->setUserId(add_escape_custom($patientLocation->getUserId()));
        $patientLocation->setLocation(add_escape_custom($patientLocation->getLocation()));
        $this->logger->debug('Attempting to track patient location');
        $this->repository->save($patientLocation);
    }
}
