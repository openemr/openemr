<?php

/**
 * InsuranceService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Vishnu Yarmaneni <vardhanvishnu@gmail.com>
 * @copyright Copyright (c) 2021 Vishnu Yarmaneni <vardhanvishnu@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\InsuranceCompanyService;
use OpenEMR\Validators\ProcessingResult;

class OrganizationService extends BaseService
{

    /**
     * @var \OpenEMR\Services\FacilityService
     */
    private $facilityService;

    /**
     * @var \OpenEMR\Services\InsuranceCompanyService
     */
    private $insuranceService;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->facilityService = new FacilityService();
        $this->insuranceService = new InsuranceCompanyService();
    }


    public function getOne($uuid)
    {
        $facilityResult = $this->facilityService->getOne($uuid);
        $insuranceResult = $this->insuranceService->getOne($uuid);
        return $this->processResults($facilityResult, $insuranceResult);
    }

    /**
     * Retrieves an organization representing a facility given the facility id.  If the organization cannot be found
     * it returns null.
     * @param $facilityId  The id of the facility to search on.
     * @return array|null
     */
    public function getFacilityOrganizationById($facilityId)
    {
        $facilityResult = $this->facilityService->getById($facilityId);
        if (!empty($facilityResult)) {
            $facilityOrgs = $this->getFacilityOrg($facilityResult);
            return array_pop($facilityOrgs); // return only one record
        }
        return null;
    }

    private function getFacilityOrg($facilityRecords)
    {
        $facilityOrgs = array();
        foreach ($facilityRecords as $index => $org) {
            $address = array();
            if (isset($org['street'])) {
                $org['line1'] = $org['street'];
            }
            $org['orgType'] = "facility";
            array_push($facilityOrgs, $org);
        }
        return $facilityOrgs;
    }

    private function getInsuranceOrg($insuranceRecords)
    {
        $insuranceOrgs = array();
        foreach ($insuranceRecords as $index => $org) {
            if (isset($org['uuid'])) {
                $org['uuid'] = UuidRegistry::uuidToString($org['uuid']);
            }
            if (isset($org['zip'])) {
                $org['postal_code'] = $org['zip'];
            }
            if (isset($org['country'])) {
                $org['country_code'] = $org['country'];
            }
            $org['orgType'] = "insurance";
            // TODO: @adunsulag check with code reviewers to make sure this is the right value for an insurance org
            // since the callers of this service are 'viewing' an organization which is a facade over insurance & facility
            // we need to make sure both records have the same column.
            $org['service_location'] = 0;
            array_push($insuranceOrgs, $org);
        }
        return $insuranceOrgs;
    }

    private function processResults($facilityResult, $insuranceResult)
    {
        $processingResult = new ProcessingResult();
        $facilityOrgs = $this->getFacilityOrg($facilityResult->getData());
        $insuranceOrgs = $this->getInsuranceOrg($insuranceResult->getData());
        $OrgRecords = array_merge($facilityOrgs, $insuranceOrgs);
        if (count($OrgRecords) > 0) {
            $processingResult->setData($OrgRecords);
        } else {
            $processingResult->setValidationMessages(array_merge($insuranceResult->getValidationMessages(), $facilityResult->getValidationMessages()));
            $processingResult->setInternalErrors(array_merge($insuranceResult->getInternalErrors(), $facilityResult->getInternalErrors()));
        }


        return $processingResult;
    }

    public function getPrimaryBusinessEntity()
    {
        return $this->facilityService->getPrimaryBusinessEntity();
    }

    public function search($search = array(), $isAndCondition = true)
    {
        $facilityResult = $this->facilityService->search($search, $isAndCondition);
        $insuranceResult = $this->insuranceService->search($search, $isAndCondition);
        return $this->processResults($facilityResult, $insuranceResult);
    }

    public function getAll($search = array(), $isAndCondition = true)
    {
        return $this->search($search, $isAndCondition);
    }

    /**
     * Inserts a new Organization Based on Type of Organization record.
     *
     * @param $data The facility fields (array) to insert.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function insert($data)
    {
        if ($data['orgType'] == 'facility') {
            $data = $this->prepareFacilitydata($data);
            return $this->facilityService->insert($data);
        }
        if ($data['orgType'] == 'insurance') {
            $data = $this->prepareInsurancedata($data);
            return $this->insuranceService->insert($data);
        }
    }

    /**
     * Updates an existing Organization record based on type of Organization.
     *
     * @param $uuid - The uuid identifier in string format used for update.
     * @param $data - The updated Organization data fields
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function update($uuid, $data)
    {
        if (empty($data)) {
            $processingResult = new ProcessingResult();
            $processingResult->setValidationMessages("Invalid Data");
            return $processingResult;
        }

        if ($data['orgType'] == 'facility') {
            $data = $this->prepareFacilitydata($data);
            return $this->facilityService->update($uuid, $data);
        }
        if ($data['orgType'] == 'insurance') {
            $data = $this->prepareInsurancedata($data);
            return $this->insuranceService->update($uuid, $data);
        }
    }

    private function prepareFacilitydata($data)
    {
        //For now return the data -- make modification based on how the Organization data is structured
        unset($data['orgType']);
        return $data;
    }

    private function prepareInsurancedata($data)
    {
    //For now return the data -- make modification based on how the Organization data is structured
        unset($data['orgType']);
        return $data;
    }
}
