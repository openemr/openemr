<?php
/**
 * PrescriptionRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sharon Cohen
 * @copyright Copyright (c) 2019
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


namespace OpenEMR\RestControllers;

use OpenEMR\Services\PrescriptionService;
use OpenEMR\RestControllers\RestControllerHelper;

class PrescriptionRestController
{
    private $prescriptionService;

    public function __construct()
    {
        $this->prescriptionService = new PrescriptionService();
    }

    public function getAll($pid)
    {
        $serviceResult = $this->prescriptionService->getAll($pid);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }


}
