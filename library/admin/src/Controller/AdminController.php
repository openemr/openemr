<?php
/**
 * Created by PhpStorm.
 * User: rdown
 * Date: 2017-07-12
 * Time: 22:02
 */

namespace OpenEMR\Admin\Controller;

require_once "../../../../interface/globals.php";
require_once "{$GLOBALS['srcdir']}/globals.inc.php";

use OpenEMR\Admin\Service\AdminMenuBuilder;
use OpenEMR\Core\Controller;


class AdminController extends Controller
{

    /** @var AdminMenuBuilder */
    public $menuBuilder;

    public function __construct()
    {
        parent::__construct();
        $this->menuBuilder = $this->container->get('admin.admin_menu_builder');

        $this->indexAction();
    }

    public function indexAction()
    {

        $this->menuBuilder->generateMainMenu();
    }
}

$test = new AdminController();
