<?php

/**
 * VersionRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers;

use OpenEMR\Services\VersionService;
use OpenEMR\RestControllers\RestControllerHelper;

class VersionRestController
{
    private $versionService;

    public function __construct()
    {
        $this->versionService = new VersionService();
    }

    public function getOne()
    {
        $serviceResult = $this->versionService->fetch();
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }
}
