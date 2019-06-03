<?php

/**
 * BillingRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


namespace OpenEMR\RestControllers;

use OpenEMR\Services\BillingService;
use OpenEMR\RestControllers\RestControllerHelper;

class BillingRestController
{
    private $billingService;

    public function __construct()
    {
        $this->billingService = new BillingService();
    }

    public function getBalances($pid)
    {
        $serviceResult = $this->billingService->getBalances($pid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

}
