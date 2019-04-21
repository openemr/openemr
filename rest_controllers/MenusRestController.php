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

    public function getMenu($data)
    {
        $serviceResult = $this->menusService->getMenu($data);
        return RestControllerHelper::responseHandler($serviceResult, null, 200);
    }

}
