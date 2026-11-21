<?php

/**
 * PatientMenuRole class.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Eyal Wolanowski <eyal.wolanowski@gmail.com>
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Eyal Wolanowski <eyal.wolanowski@gmail.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Menu;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Menu\PatientMenuEvent;
use OpenEMR\Services\UserService;

class PatientMenuRole extends MenuRole
{
    /*
     * The event dispatcher we create in the constructor so we can let listeners know
     * when we're rendering the menu.
     */
    protected $dispatcher;

    /**
     * Constructor
     */
    public function __construct()
    {
        // This is where the magic happens to support special menu items.
        //   An empty menu_update_map array is created in MenuRole class
        //   constructor. Adding to this array will link special menu items
        //   to functions in this class.
        parent::__construct();
        $this->menu_update_map["Modules"] = "updateModulesDemographicsMenu";
        $this->dispatcher = OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher();
    }

    /**
     * Collect the Menu for logged in user.
     *
     * @return array representation of the Menu
     */
    public function getMenu()
    {
        // Collect the selected menu of user
        $patientMenuRole = $this->getMenuRole();

        // Validate that the menu role filename is a basename only (no path traversal)
        if ($patientMenuRole !== basename($patientMenuRole) || str_contains($patientMenuRole, '..')) {
            ServiceContainer::getLogger()->error("Invalid menu role filename rejected", ['filename' => $patientMenuRole]);
            die("\nInvalid menu role filename.");
        }

        // Load the selected menu
        if (str_ends_with($patientMenuRole, '.json')) {
            // load custom menu (includes .json in id)
            $menu_parsed = json_decode(file_get_contents(OEGlobalsBag::getInstance()->get('OE_SITE_DIR') . "/documents/custom_menus/patient_menus/" . $patientMenuRole));
        } else {
            // load a standardized menu (does not include .json in id)
            $menu_parsed = json_decode(file_get_contents(OEGlobalsBag::getInstance()->getKernel()->getProjectDir() . "/interface/main/tabs/menu/menus/patient_menus/" . $patientMenuRole . ".json"));
        }
        // if error, then die and report error
        if (!$menu_parsed) {
            die("\nJSON ERROR: " . json_last_error());
        }

        $menu_parsed = $this->setPatientMenuUrl($menu_parsed);

        // Parse the menu JSON and build the menu. Also, tell the EventDispatcher about the event
        // so that 3rd party modules may modify the menu items
        $menu_parsed = json_decode(json_encode($menu_parsed));
        $this->menuUpdateEntries($menu_parsed);
        $updatedPatientMenuEvent = $this->dispatcher->dispatch(new PatientMenuEvent($menu_parsed), PatientMenuEvent::MENU_UPDATE);

        $menu_restrictions = [];
        $tmp = $updatedPatientMenuEvent->getMenu();
        $this->menuApplyRestrictions($tmp, $menu_restrictions);
        $updatedPatientMenuRestrictions = $this->dispatcher->dispatch(new PatientMenuEvent($menu_restrictions), PatientMenuEvent::MENU_RESTRICT);

        return $updatedPatientMenuRestrictions->getMenu();
    }

    /**
     * Build the html select element to list the PatientMenuRole options.
     *
     * @return string Html select element to list the PatientMenuRole options.
     * @var string $selected Current PatientMenuRole for current users.
     */
    public function displayMenuRoleSelector($selected = ""): string
    {
        $output = "<select name='patient_menu_role' id='patient_menu_role' class='form-control'>";
        $output .= "<option value='standard' " . (($selected == "standard") ? "selected" : "") . ">" . xlt("Standard") . "</option>";

        $customMenuDir = OEGlobalsBag::getInstance()->get('OE_SITE_DIR') . "/documents/custom_menus/patient_menus/";
        if (file_exists($customMenuDir)) {
            $dHandle = opendir($customMenuDir);
            while (false !== ($menuCustom = readdir($dHandle))) {
                // Only process files that contain *.json
                if (str_ends_with($menuCustom, '.json')) {
                    $selectedTag = ($selected == $menuCustom) ? "selected" : "";
                    $output .= "<option value='" . attr($menuCustom) . "' " . $selectedTag . ">";
                    // Drop the .json suffix and translate the name. Custom
                    // menu filenames are dynamic, hence the @phpstan-ignore.
                    // @phpstan-ignore argument.type (custom menu filenames are dynamic)
                    $output .= xlt(substr($menuCustom, 0, -5));
                    $output .= "</option>";
                }
            }

            closedir($dHandle);
        }

        $output .= "</select>";
        return $output;
    }

    /**
     * Collect the patientMenuRole for logged in user.
     *
     * @return string Identifier for the patientMenuRole
     */
    private function getMenuRole()
    {
        $userService = new UserService();
        $user = $userService->getCurrentlyLoggedInUser();
        $patientMenuRole = $user['patient_menu_role'];
        if (empty($patientMenuRole)) {
            $patientMenuRole = "standard";
        }

        return $patientMenuRole;
    }

    /**
     * load demographics created by modules system
     *
     * @param $menu_list
     */
    protected function updateModulesDemographicsMenu(&$menu_list)
    {
        $module_query = sqlStatement("SELECT msh.*,ms.obj_name,ms.menu_name,ms.path,m.mod_ui_name,m.type FROM modules_hooks_settings AS msh
                                    LEFT OUTER JOIN modules_settings AS ms ON obj_name=enabled_hooks AND ms.mod_id=msh.mod_id
                                    LEFT OUTER JOIN modules AS m ON m.mod_id=ms.mod_id
                                    WHERE fld_type=3 AND mod_active=1 AND sql_run=1 AND attached_to='demographics' ORDER BY mod_id");

        if (sqlNumRows($module_query)) {
            $session = SessionWrapperFactory::getInstance()->getActiveSession();
            $authUserID = $session->get('authUserID');
            while ($hookrow = sqlFetchArray($module_query)) {
                if ($hookrow['type'] == 0) {
                    $modulePath = OEGlobalsBag::getInstance()->get('customModDir');
                    $added = "";
                } else {
                    $added = "index";
                    $modulePath = OEGlobalsBag::getInstance()->get('zendModDir');
                }

                if (AclMain::zhAclCheck($authUserID, $hookrow['obj_name']) ? "" : "1") {
                    continue;
                }

                $relative_link = "../../modules/" . $modulePath . "/public/" . $hookrow['path'];
                // Preserve the old `?:` semantics: '' AND '0' both fall through
                // to 'NoName'. (The previous code used `$hookrow['menu_name'] ?: 'NoName'`,
                // and `'0' ?: 'NoName'` returns 'NoName'.)
                $menuName = $hookrow['menu_name'] ?? null;
                $mod_nick_name = is_string($menuName) && $menuName !== '' && $menuName !== '0'
                    ? $menuName
                    : 'NoName';

                $subEntry = new \stdClass();
                $subEntry->requirement = 0;
                $subEntry->target = 'main';
                $subEntry->menu_id = $hookrow['mod_id'];
                // Assign the raw module menu name; MenuRole::menuUpdateEntries()
                // will translate and HTML-escape it via xlt() during the
                // recursive pass over children.
                $subEntry->label = $mod_nick_name;
                $subEntry->url = $relative_link;
                $subEntry->on_click = 'top.restoreSession()';
                $subEntry->pid = 'false';

                array_push($menu_list->children, $subEntry);
            }
        }
    }

    /**
     * displays a bootstrap4 horizontal nav bar
     *
     * @param string $endControls  Trusted, already-escaped HTML placed at the right-hand end of
     *                             the bar, e.g. OemrUI::tabRowControls()
     * @param string $activeMenuId menu_id of the tab for the current page, filled with the primary
     *                             color; a dropdown is highlighted when one of its items is
     */
    public function displayHorizNavBarMenu(string $endControls = '', string $activeMenuId = ''): void
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $sessionPid = $session->get('pid');
        $pid = is_int($sessionPid) || is_string($sessionPid) ? (string) $sessionPid : '';
        $menu_restrictions = $this->getMenu();
        $str_top = <<<EOT
        <!--navbar-light is needed for color override in other themes-->
        <nav class="navbar navbar-expand-md navbar-light bg-light">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#myNavbar" aria-controls="myNavbar" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="myNavbar">
                    <ul class="navbar-nav">
        EOT;
        echo $str_top . "\r\n";
        foreach ($menu_restrictions as $value) {
            if (!is_object($value)) {
                continue;
            }
            $fields = get_object_vars($value);
            $children = is_array($fields['children'] ?? null) ? array_filter($fields['children'], is_object(...)) : [];
            if ($children !== []) {
                // create dropdown if there are children (bootstrap3 horizontal nav bar with dropdown)
                $childIds = array_map(static fn(object $child): string => self::menuField($child, 'menu_id'), $children);
                $isActive = $activeMenuId !== '' && in_array($activeMenuId, $childIds, true);
                $label = self::menuField($value, 'label');
                $menuId = self::menuField($value, 'menu_id');
                $class = trim(self::menuField($value, 'class') . ($isActive ? ' bg-primary text-white rounded' : ' text-body'));
                $list = '<li class="dropdown' . ($isActive ? ' active' : '') . '"><a href="#"  id="' . attr($menuId !== '' ? $menuId : $label) . '" class="nav-link dropdown-toggle ' . attr($class) . '" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">' . text($label) . ' <span class="caret"></span></a>';
                $list .= '<ul class="dropdown-menu">';
                foreach ($children as $child) {
                    $list .= $this->navItem($child, $pid, $activeMenuId);
                }
                $list .= '</ul>';
            } else {
                $list = $this->navItem($value, $pid, $activeMenuId);
            }
            echo $list . "\r\n";
        }
        echo "                </ul>\r\n";
        echo $endControls;
        $str_bot = <<<EOB
            </div>
        </nav>
        EOB;
        echo $str_bot . "\r\n";
    }

    /**
     * One tab, or one item of a tab's dropdown. The current page's tab is filled with the primary color.
     */
    private function navItem(object $item, string $pid, string $activeMenuId): string
    {
        $menuId = self::menuField($item, 'menu_id');
        $url = self::menuField($item, 'url');
        // A menu item with pid "true" takes the patient id appended to its url.
        $link = self::menuField($item, 'pid') === 'true' ? $url . attr($pid) : $url;
        $isActive = $activeMenuId !== '' && $menuId === $activeMenuId;
        $itemClass = trim(self::menuField($item, 'class') . ($isActive ? ' active' : ''));
        $linkClass = $isActive ? 'nav-link bg-primary text-white rounded' : 'nav-link text-dark';
        $current = $isActive ? ' aria-current="page"' : '';

        // on_click comes from the menu definition, which is trusted, and is output as-is.
        return '<li class="nav-item ' . attr($itemClass) . '" id="' . attr($menuId) . '">'
            . '<a class="' . $linkClass . '"' . $current . ' href="' . attr($link) . '" onclick="' . self::menuField($item, 'on_click') . '"> ' . text(self::menuField($item, 'label')) . ' </a>'
            . '</li>';
    }

    /**
     * A string field of a menu item decoded from the menu JSON, or '' when it is missing.
     */
    private static function menuField(object $item, string $field): string
    {
        $value = get_object_vars($item)[$field] ?? '';

        return is_string($value) ? $value : '';
    }

    /**
     * make the url absolute to web root
     *
     * @param $rel_url
     *
     * @return string
     */
    private function getAbsoluteWebRoot($rel_url)
    {
        if ($rel_url && !strpos((string) $rel_url, "://")) {
            // Normalize URL if it starts with a forward or backward slash
            if (str_starts_with((string) $rel_url, '/') || str_starts_with((string) $rel_url, '\\')) {
                $rel_url = ltrim((string) $rel_url, '/\\');
            }
            return OEGlobalsBag::getInstance()->getKernel()->getWebRoot() . "/" . $rel_url;
        }
        return $rel_url;
    }

    /**
     * @param $menu_parsed
     * @return mixed
     */
    public function setPatientMenuUrl($menu_parsed)
    {
        //to make the url absolute to web root and to account for external urls i.e. those beginning with http or https
        foreach ($menu_parsed as $menu_obj) {
            if (property_exists($menu_obj, 'url')) {
                $menu_obj->url = $this->getAbsoluteWebRoot($menu_obj->url);
            }
            if (!empty($menu_obj->children)) {
                foreach ($menu_obj->children as $menu_obj) {
                    if (property_exists($menu_obj, 'url')) {
                        $menu_obj->url = $this->getAbsoluteWebRoot($menu_obj->url);
                    }
                }
            }
        }

        return $menu_parsed;
    }
}
