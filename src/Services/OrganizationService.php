<?php

/**
 * InsuranceService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Vishnu Yarmaneni <vardhanvishnu@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Validators\ProcessingResult;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\InsuranceCompanyService;

class OrganizationService extends BaseService
{

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
            if (isset($org['zip'])) {
                $org['postal_code'] = $org['zip'];
            }
            if (isset($org['country'])) {
                $org['country_code'] = $org['country'];
            }
            $org['orgType'] = "insurance";
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

    public function getAll($search = array(), $isAndCondition = true)
    {
        
        $facilityResult = $this->facilityService->getAll($search = array(), $isAndCondition = true);
        $insuranceResult = $this->insuranceService->getAll($search = array(), $isAndCondition = true);
        return $this->processResults($facilityResult, $insuranceResult);
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
