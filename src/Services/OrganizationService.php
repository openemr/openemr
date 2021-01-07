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
        $processingResult = new ProcessingResult();
        $facilityResult = $this->facilityService->getOne($uuid);
        $facilityOrgs = $this->getFacilityOrg($facilityResult->getData());
        $insuranceResult = $this->insuranceService->getOne($uuid);
        $insuranceOrgs = $this->getInsuranceOrg($insuranceResult->getData());
        $processingResult->setData(array_merge($facilityOrgs, $insuranceOrgs));
        return $processingResult;
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

    public function getAll($search = array(), $isAndCondition = true)
    {
        $processingResult = new ProcessingResult();
        $facilityResult = $this->facilityService->getAll($search = array(), $isAndCondition = true);
        $facilityOrgs = $this->getFacilityOrg($facilityResult->getData());
        $insuranceResult = $this->insuranceService->getAll($search = array(), $isAndCondition = true);
        $insuranceOrgs = $this->getInsuranceOrg($insuranceResult->getData());
        $processingResult->setData(array_merge($facilityOrgs, $insuranceOrgs));
        return $processingResult;
    }
}
