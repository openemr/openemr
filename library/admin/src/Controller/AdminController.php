<?php
/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\Controller;

use OpenEMR\Admin\Service\AdminMenuBuilder;
use OpenEMR\Core\Controller;

/**
 * Class AdminController.
 *
 * Main entry point for the admin module
 *
 * @package OpenEMR\Admin
 * @subpackage Controller
 * @author Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2017 Robert Down <robertdown@live.com>
 */
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
