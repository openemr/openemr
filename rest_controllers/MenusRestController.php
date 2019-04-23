<?php
/**
 * MenusRestController
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Eyal Wolanowski <eyal.wolanowski@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


namespace OpenEMR\RestControllers;

use OpenEMR\Services\MenusService;
use OpenEMR\RestControllers\RestControllerHelper;

class MenusRestController
{
    private $menusService;

    public function __construct()
    {
        $this->menusService = new MenusService();
    }

    public function getMenu($pid, $type)
    {

        switch ($type) {
            case "patient_file":
                $serviceResult = $this->menusService->getMenu($pid);
                $responseCode=200;
                break;
            default:
                $serviceResult = null;
                $responseCode=500;
        }


        return RestControllerHelper::responseHandler($serviceResult, null, $responseCode);
    }

}
