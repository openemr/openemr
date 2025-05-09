<?php

/*
 *   package   OpenEMR
 *   link      http://www.open-emr.org
 *   author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   Copyright (c)
 *   All rights reserved
 */

namespace Juggernaut\Module\Bamboo\Controllers;

use OpenEMR\Services\FacilityService;
use OpenEMR\Services\UserService;
class RequestData
{
    private FacilityService $facilityService;
    private UserService $userService;

    public function __construct()
    {
        $this->facilityService = new FacilityService();
        $this->userService = new UserService();
    }

    public function practiceData()
    {
        return $this->facilityService->getPrimaryBusinessEntity();
    }

    public function userData(): array
    {
        return $this->userService->getUser($_SESSION['authUserID']);
    }
}
