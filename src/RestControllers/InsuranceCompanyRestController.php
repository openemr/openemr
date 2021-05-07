<?php

/**
 * InsuranceCompanyRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Services\AddressService;
use OpenEMR\Services\InsuranceCompanyService;

class InsuranceCompanyRestController
{
    private $insuranceCompanyService;
    private $addressService;

    public function __construct()
    {
        $this->insuranceCompanyService = new InsuranceCompanyService();
        $this->addressService = new AddressService();
    }

    public function getAll()
    {
        $serviceResult = $this->insuranceCompanyService->getAll();
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getOne($iid)
    {
        $serviceResult = $this->insuranceCompanyService->getOneById($iid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getInsuranceTypes()
    {
        $serviceResult = $this->insuranceCompanyService->getInsuranceTypes();
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function post($data)
    {
        $insuranceCompanyValidationResult = $this->insuranceCompanyService->validate($data);
        $insuranceCompanyValidationHandlerResult = RestControllerHelper::validationHandler($insuranceCompanyValidationResult);
        if (is_array($insuranceCompanyValidationHandlerResult)) {
            return $insuranceCompanyValidationHandlerResult;
        }

        $addressValidationResult = $this->addressService->validate($data);
        $addressValidationHandlerResult = RestControllerHelper::validationHandler($addressValidationResult);
        if (is_array($addressValidationHandlerResult)) {
            return $addressValidationHandlerResult;
        }

        $serviceResult = $this->insuranceCompanyService->insert($data);
        return RestControllerHelper::responseHandler($serviceResult, array('iid' => $serviceResult), 201);
    }

    public function put($iid, $data)
    {
        $insuranceCompanyValidationResult = $this->insuranceCompanyService->validate($data);
        $insuranceCompanyValidationHandlerResult = RestControllerHelper::validationHandler($insuranceCompanyValidationResult);
        if (is_array($insuranceCompanyValidationHandlerResult)) {
            return $insuranceCompanyValidationHandlerResult;
        }

        $addressValidationResult = $this->addressService->validate($data);
        $addressValidationHandlerResult = RestControllerHelper::validationHandler($addressValidationResult);
        if (is_array($addressValidationHandlerResult)) {
            return $addressValidationHandlerResult;
        }

        $serviceResult = $this->insuranceCompanyService->update($data, $iid);
        return RestControllerHelper::responseHandler($serviceResult, array('iid' => $iid), 200);
    }
}
