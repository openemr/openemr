<?php

/**
 * ProductRegistrationRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\Services\ProductRegistrationService;
use OpenEMR\RestControllers\RestControllerHelper;

class ProductRegistrationRestController
{
    private $productRegistrationService;

    public function __construct()
    {
        $this->productRegistrationService = new ProductRegistrationService();
    }

    public function getOne()
    {
        $serviceResult = $this->productRegistrationService->getProductStatus();
        $serviceResult = $serviceResult['statusAsString'];
        return RestControllerHelper::responseHandler($serviceResult, array("status" => $serviceResult), 200);
    }
}
