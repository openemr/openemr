<?php
/**
 * ProviderRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


namespace OpenEMR\RestControllers;

use OpenEMR\Services\ProviderService;
use OpenEMR\RestControllers\RestControllerHelper;

class ProviderRestController
{
    private $providerService;

    public function __construct()
    {
        $this->providerService = new ProviderService();
    }

    public function getOne($id)
    {
        $serviceResult = $this->providerService->getById($id);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

    public function getAll()
    {
        $serviceResult = $this->providerService->getAll();
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }
}
