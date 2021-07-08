<?php

/**
 * OrganizationService -- TODO: this is almost a FHIR construct and perhaps should be moved up to the FHIR service layer
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
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;

class OrganizationService extends BaseService
{

    const ORGANIZATION_TYPE_FACILITY  = "facility";
    const ORGANIZATION_TYPE_INSURANCE = 'insurance';
    /**
     * @var \OpenEMR\Services\FacilityService
     */
    private $facilityService;

    /**
     * @var \OpenEMR\Services\InsuranceCompanyService
     */
    private $insuranceService;

    /**
     * @var BaseService[]
     */
    private $services;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->facilityService = new FacilityService();
        $this->insuranceService = new InsuranceCompanyService();
        $this->services = [$this->facilityService, $this->insuranceService, new ProcedureProviderService()];
    }


    public function getOne($uuid)
    {
        $searchParams = ['uuid' => new TokenSearchValue($uuid, null, true)];
        foreach ($this->services as $service) {
            $result = $service->search($searchParams);
            $this->processResultsForService($service);
            if ($result->hasErrors()) {
                return $result;
            } else if (!empty($result->getData())) {
                return $this->processResultsForService($result, $service);
            }
        }
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
            $facilityOrgs = $this->getFacilityOrg([$facilityResult]);
            return array_pop($facilityOrgs); // return only one record
        }
        return null;
    }

    private function getProcedureProviderOrg($records)
    {
        $providerRecords = array();
        foreach ($records as $index => $org) {
            $org['orgType'] = "procedureProvider";
            $org['facility_npi'] = $org['npi'];
            $org['active'] = $org['active'] == '1';
            array_push($providerRecords, $org);
        }
        return $providerRecords;
    }

    private function getFacilityOrg($facilityRecords)
    {
        $facilityOrgs = array();
        foreach ($facilityRecords as $index => $org) {
            if (isset($org['street'])) {
                $org['line1'] = $org['street'];
            }
            $org['orgType'] = self::ORGANIZATION_TYPE_FACILITY;
            $org['active'] = $org['service_location'] == '1';
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
            $org['orgType'] = self::ORGANIZATION_TYPE_INSURANCE;
            // TODO: @adunsulag check with code reviewers to make sure this is the right value for an insurance org
            // since the callers of this service are 'viewing' an organization which is a facade over insurance & facility
            // we need to make sure both records have the same column.
            $org['service_location'] = 0;
            $org['active'] = $org['inactive'] !== '1';
            array_push($insuranceOrgs, $org);
        }
        return $insuranceOrgs;
    }

    private function processResultsForService(ProcessingResult $result, BaseService $service)
    {
        $newResult = new ProcessingResult();
        if ($service instanceof FacilityService) {
            $newResult->setData($this->getFacilityOrg($result->getData()));
        } else if ($service instanceof InsuranceCompanyService) {
            $newResult->setData($this->getInsuranceOrg($result->getData()));
        } else if ($service instanceof ProcedureProviderService) {
            $newResult->setData($this->getProcedureProviderOrg($result->getData()));
        } else {
            $newResult->addInternalError("Unsupported organization type found");
        }
        return $newResult;
    }

    public function getPrimaryBusinessEntity()
    {
        return $this->facilityService->getPrimaryBusinessEntity();
    }

    public function search($search = array(), $isAndCondition = true)
    {
        $combinedResult = new ProcessingResult();
        foreach ($this->services as $service) {
            $result = $service->search($search, $isAndCondition);
            if ($result->hasErrors()) {
                return $result;
            } else {
                $transformedResult = $this->processResultsForService($result, $service);
                $combinedResult->addProcessingResult($transformedResult);
            }
        }
        return $combinedResult;
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
        if ($data['orgType'] == self::ORGANIZATION_TYPE_FACILITY) {
            $data = $this->prepareData($data);
            return $this->facilityService->insert($data);
        }
        if ($data['orgType'] == self::ORGANIZATION_TYPE_INSURANCE) {
            $data = $this->prepareData($data);
            return $this->insuranceService->insert($data);
        }
        // TODO: do we want to allow inserting of procedure provider?
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

        if ($data['orgType'] == self::ORGANIZATION_TYPE_FACILITY) {
            $data = $this->prepareData($data);
            return $this->facilityService->update($uuid, $data);
        }
        if ($data['orgType'] == self::ORGANIZATION_TYPE_INSURANCE) {
            $data = $this->prepareData($data);
            return $this->insuranceService->update($uuid, $data);
        }
    }

    private function prepareData($data)
    {
        //For now return the data -- make modification based on how the Organization data is structured
        unset($data['orgType']);
        return $data;
    }
}
