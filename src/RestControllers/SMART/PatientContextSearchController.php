<?php

/**
 * PatientContextSearchController.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\SMART;

use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\UserService;
use Psr\Log\LoggerInterface;

class PatientContextSearchController
{
    /**
     * @var PatientService
     */
    private $service;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(PatientService $service, LoggerInterface $logger)
    {
        $this->service = $service;
        $this->logger = $logger;
    }

    /**
     * Retrieves a patient if the passed in user has access to that point.
     * @param $patientUUID
     * @param $userUUID
     * @return array
     * @throws AccessDeniedException
     */
    public function getPatientForUser($patientUUID, $userUUID)
    {
        if (empty($patientUUID)) {
            throw new \InvalidArgumentException("patient uuid cannot be empty");
        }
        $user = new UserService();
        $user = $user->getUserByUUID($userUUID);

        $this->checkUserAccessPatientData($user);
        $result = $this->service->getOne($patientUUID);
        return $result->getData();
    }

    /**
     * @param $searchParams
     * @param $userUUID
     * @return array
     * @throws AccessDeniedException
     */
    public function searchPatients($searchParams, $userUUID)
    {

        // our ACL's rely on a username which seems silly, but we'll convert from UUID to username here so we can
        // check the  ACL
        $user = new UserService();
        $user = $user->getUserByUUID($userUUID);
        // we need to make sure we only return patients for the passed in $userId
        $this->checkUserAccessPatientData($user);

        // get the Set intersection on allowed search params so that we only allow searching
        // on these specific parameters.
        $allowedSearch = ['fname' => '', 'mname' => '', 'lname' => ''];
        $serviceSearchParams = array_intersect_key($searchParams, $allowedSearch);
        $patients = $this->service->getAll($serviceSearchParams);

        $patientKeys = ['uuid', 'title', 'fname', 'mname', 'lname', 'email', 'DOB', 'sex'];
        $filteredPatientKeys = array_combine($patientKeys, $patientKeys);
        $returnedResults = [];
        foreach ($patients->getData() as $record) {
            $returnedResults[] = array_intersect_key($record, $filteredPatientKeys);
        }
        return $returnedResults;
    }

    /**
     * @param $user
     * @throws AccessDeniedException If the user fails to have permissions to patient data
     */
    private function checkUserAccessPatientData($user)
    {
        if (empty($user) || AclMain::aclCheckCore('patients', 'demo', $user['username']) !== true) {
            throw new AccessDeniedException('patients', 'demo', "Illegal access to patients requested");
        }
    }
}
