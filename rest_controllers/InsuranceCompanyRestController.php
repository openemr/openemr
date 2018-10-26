<?php
/**
 * InsuranceCompanyRestController
 *
 * Copyright (C) 2018 Matthew Vita <matthewvita48@gmail.com>
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

namespace OpenEMR\RestControllers;

use OpenEMR\Services\InsuranceCompanyService;
use OpenEMR\Services\AddressService;
use OpenEMR\RestControllers\RestControllerHelper;

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

    public function getInsuranceTypes()
    {
        $serviceResult = $this->insuranceCompanyService->getInsuranceTypes();
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function post($data)
    {
        $insuranceCompanyValidationResult = $this->insuranceCompanyService->validate($data);
        $insuranceCompanyValidationHandlerResult = RestControllerHelper::validationHandler($insuranceCompanyValidationResult);
        if (is_array($insuranceCompanyValidationHandlerResult)) { return $insuranceCompanyValidationHandlerResult; }

        $addressValidationResult = $this->addressService->validate($data);
        $addressValidationHandlerResult = RestControllerHelper::validationHandler($addressValidationResult);
        if (is_array($addressValidationHandlerResult)) { return $addressValidationHandlerResult; }

        $serviceResult = $this->insuranceCompanyService->insert($data);
        return RestControllerHelper::responseHandler($serviceResult, array('id' => $serviceResult), 201);
    }

    public function put($iid, $data)
    {
        $insuranceCompanyValidationResult = $this->insuranceCompanyService->validate($data);
        $insuranceCompanyValidationHandlerResult = RestControllerHelper::validationHandler($insuranceCompanyValidationResult);
        if (is_array($insuranceCompanyValidationHandlerResult)) { return $insuranceCompanyValidationHandlerResult; }

        $addressValidationResult = $this->addressService->validate($data);
        $addressValidationHandlerResult = RestControllerHelper::validationHandler($addressValidationResult);
        if (is_array($addressValidationHandlerResult)) { return $addressValidationHandlerResult; }

        $serviceResult = $this->insuranceCompanyService->update($data, $iid);
        return RestControllerHelper::responseHandler($serviceResult, array('id' => $serviceResult), 201);
    }
}