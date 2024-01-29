<?php

/**
 * EmployerRestController handles the API rest requests to the employer data for a patient
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\Services\EmployerService;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;

class EmployerRestController
{
    private $employerService;

    public function __construct()
    {
        $this->employerService = new EmployerService();
    }

    public function getAll($searchParams)
    {
        if (isset($searchParams['id'])) {
            $searchParams['id'] = new TokenSearchField('id', new TokenSearchValue($searchParams['id']), false);
        }
        if (isset($searchParams['puuid'])) {
            $searchParams['puuid'] = new TokenSearchField('puuid', $searchParams['puuid'], true);
        }
        if (isset($searchParams['pid'])) {
            $searchParams['pid'] = new TokenSearchField('pid', $searchParams['pid'], true);
        }
        $serviceResult = $this->employerService->search($searchParams);
        return RestControllerHelper::handleProcessingResult($serviceResult, null, 200);
    }
}
