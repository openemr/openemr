<?php

/**
 * PatientMenuRole class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    MD Support<mdsupport@users.sourceforge.net>
 * @author    Eyal Wolanowski <eyal.wolanowski@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Eyal Wolanowski <eyal.wolanowski@gmail.com>
 * @copyright Copyright (c) 2022 MD Support<mdsupport@users.sourceforge.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Menu;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\UserService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use OpenEMR\Menu\PatientMenuEvent;

class PatientMenuRole extends MenuRole
{
    /**
     * mdsupport - Add option to include patient record values in menu.
     * Use ptdata key to specify list of values to be included in html.
     * Add other data in future as neeeded.
     * @var array - Patient Record as associative array
     */
    private $recPt = [];

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
        $this->dispatcher = $GLOBALS['kernel']->getEventDispatcher();
 
        $this->recPt = sqlQuery(
            'SELECT *,
                 IF(lname <> "", CONCAT_WS(", ", lname, fname), fname) lfname,
                 IF(fname <> "", CONCAT_WS(" ", fname, lname), lname) flname
            FROM patient_data
            WHERE pid=?',
            $_SESSION['pid']
        );
        // Deceased => Inactive
        if (!empty($this->recPt['deceased_date'])) {
            $this->recPt['inactive_days'] = $this->inactiveDays($this->recPt['deceased_date']);
        }
    }

    /**
     * Legacy code - Using inactive in place of deceased
     * 
     * @param  $inactive_ymd
     * @return string
     */
    private function inactiveDays($inactive_ymd)
    {
        $dtFrom = new \DateTime($inactive_ymd);
        $dtNow = new \DateTime();
        $dtDiff = $dtFrom->diff($dtNow);
        $inactive_days = $dtDiff->days;
        if ($inactive_days == 0) {
            $num_of_days = xl("Today");
        } elseif ($inactive_days == 1) {
            $num_of_days =  $inactive_days . " " . xl("day ago");
        } elseif ($inactive_days > 1 && $inactive_days < 90) {
            $num_of_days =  $inactive_days . " " . xl("days ago");
        } elseif ($inactive_days >= 90 && $inactive_days < 731) {
            $num_of_days =  "~" . round($inactive_days / 30) . " " . xl("months ago");  // function intdiv available only in php7
        } elseif ($inactive_days >= 731) {
            $num_of_days =  xl("More than") . " " . round($inactive_days / 365) . " " . xl("years ago");
        }
        
        if (strlen($inactive_ymd ?? '') > 10 && $GLOBALS['date_display_format'] < 1) {
            $inactive_ymd = substr($inactive_ymd, 0, 10);
        } else {
            $inactive_ymd = oeFormatShortDate($inactive_ymd ?? '');
        }
        
        return xlt("Inactive since") . " " . text($inactive_ymd) . " (" . text($num_of_days) . ")";
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

        // Load the selected menu
        if (preg_match("/.json$/", $patientMenuRole)) {
            // load custom menu (includes .json in id)
            $menu_parsed = json_decode(file_get_contents($GLOBALS['OE_SITE_DIR'] . "/documents/custom_menus/patient_menus/" . $patientMenuRole));
        } else {
            // load a standardized menu (does not include .json in id)
            $menu_parsed = json_decode(file_get_contents($GLOBALS['fileroot'] . "/interface/main/tabs/menu/menus/patient_menus/" . $patientMenuRole . ".json"));
        }
        // if error, then die and report error
        if (!$menu_parsed) {
            die("\nJSON ERROR: " . json_last_error());
        }
        //to make the url absolute to web root and to account for external urls i.e. those beginning with http or https
        foreach ($menu_parsed as $menu_obj) {
            if (property_exists($menu_obj, 'url')) {
                $menu_obj -> url = $this->getAbsoluteWebRoot($menu_obj -> url);
            }
            if (!empty($menu_obj->children)) {
                foreach ($menu_obj->children as $menu_obj) {
                    if (property_exists($menu_obj, 'url')) {
                        $menu_obj -> url = $this->getAbsoluteWebRoot($menu_obj -> url);
                    }
                }
            }
        }

        // Parse the menu JSON and build the menu. Also, tell the EventDispatcher about the event
        // so that 3rd party modules may modify the menu items
        $menu_parsed = json_decode(json_encode($menu_parsed));
        $this->menuUpdateEntries($menu_parsed);
        $updatedPatientMenuEvent = $this->dispatcher->dispatch(PatientMenuEvent::MENU_UPDATE, new PatientMenuEvent($menu_parsed));

        $menu_restrictions = array();
        $tmp = $updatedPatientMenuEvent->getMenu();
        $this->menuApplyRestrictions($tmp, $menu_restrictions);
        $updatedPatientMenuRestrictions = $this->dispatcher->dispatch(PatientMenuEvent::MENU_RESTRICT, new PatientMenuEvent($menu_restrictions));

        return $updatedPatientMenuRestrictions->getMenu();
    }

    /**
     * Build the html select element to list the PatientMenuRole options.
     *
     * @var string $selected Current PatientMenuRole for current users.
     * @return string Html select element to list the PatientMenuRole options.
     */
    public function displayMenuRoleSelector($selected = "")
    {
        $output = "<select name='patient_menu_role' id='patient_menu_role' class='form-control'>";
        $output .= "<option value='standard' " . (($selected == "standard") ? "selected" : "") . ">" . xlt("Standard") . "</option>";

        $customMenuDir = $GLOBALS['OE_SITE_DIR'] . "/documents/custom_menus/patient_menus/";
        if (file_exists($customMenuDir)) {
            $dHandle = opendir($customMenuDir);
            while (false !== ($menuCustom = readdir($dHandle))) {
                // Only process files that contain *.json
                if (preg_match("/.json$/", $menuCustom)) {
                    $selectedTag = ($selected == $menuCustom) ? "selected" : "";
                    $output .= "<option value='" . attr($menuCustom) . "' " . $selectedTag . ">";
                    // Drop the .json and translate the name
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
     * @param $menu_list
     */
    protected function updateModulesDemographicsMenu(&$menu_list)
    {
        $module_query = sqlStatement("SELECT msh.*,ms.obj_name,ms.menu_name,ms.path,m.mod_ui_name,m.type FROM modules_hooks_settings AS msh
                                    LEFT OUTER JOIN modules_settings AS ms ON obj_name=enabled_hooks AND ms.mod_id=msh.mod_id
                                    LEFT OUTER JOIN modules AS m ON m.mod_id=ms.mod_id
                                    WHERE fld_type=3 AND mod_active=1 AND sql_run=1 AND attached_to='demographics' ORDER BY mod_id");

        if (sqlNumRows($module_query)) {
            while ($hookrow = sqlFetchArray($module_query)) {
                if ($hookrow['type'] == 0) {
                    $modulePath = $GLOBALS['customModDir'];
                    $added = "";
                } else {
                    $added = "index";
                    $modulePath = $GLOBALS['zendModDir'];
                }

                if (AclMain::zhAclCheck($_SESSION['authUserID'], $hookrow['obj_name']) ?  "" : "1") {
                    continue;
                }

                $relative_link = "../../modules/" . $modulePath . "/" . $hookrow['path'];
                $mod_nick_name = $hookrow['menu_name'] ? $hookrow['menu_name'] : 'NoName';

                $subEntry = new \stdClass();
                $subEntry->requirement = 0;
                $subEntry->target = 'main';
                $subEntry->menu_id = $hookrow['mod_id'];
                $subEntry->label = xlt($mod_nick_name);
                $subEntry->url = $relative_link;
                $subEntry->on_click = 'top.restoreSession()';
                $subEntry->pid = 'false';

                array_push($menu_list->children, $subEntry);
            }
        }
    }

    /**
     * Builds nav-link for linkable object.
     * 
     * @param object $menuNode
     * @return string - li tag
     */
    private function navLink($menuNode)
    {
        // Build patient field data json encoded string
        if (isset($menuNode->ptdata)) {
            $aPtData = explode(',', $menuNode->ptdata);
            $ptdata = [];
            foreach ($aPtData as $ptfld) {
                $ptfld = trim($ptfld);
                $ptdata[$ptfld] = $this->recPt[$ptfld];
            }
            $ptdata = sprintf("data-ptdata='%s'", json_encode($ptdata));
        } else {
            $ptdata = '';
        }
        // csrf
        if (isset($menuNode->csrf)) {
            $csrf = sprintf(
                '%s=%s',
                $menuNode->csrf,
                CsrfUtils::collectCsrfToken()
            );
        } else {
            $csrf = '';
        }
        // TBD - Implment DevObj routing
        $htm = sprintf(
            '<li id="%s" class="nav-item %s" %s>
                <a class="nav-link" href="%s%s%s" onclick="%s">%s</a>
             </li>',
            attr($menuNode->menu_id),
            (isset($menuNode->class) ? attr($menuNode->class) : ''),
            $ptdata,
            attr($menuNode->url),
            ($menuNode->pid == "true" ? attr($this->recPt['pid']) : ''),
            $csrf,
            (isset($menuNode->on_click) ? $menuNode->on_click : 'top.restoreSession()'),
            text($menuNode->label)
        );
        return $htm;
    }
    /**
     * Displays a bootstrap4 horizontal nav bar
     */
    public function displayHorizNavBarMenu()
    {
        $menu_restrictions = $this->getMenu();

        // mdsupport - Do not echo in a class. Always return html back to caller for max flexibility.
        $htm = '';

        // Build Nav items
        foreach ($menu_restrictions as $key => $value) {
            if (!empty($value->children)) {
                $children = '';
                foreach ($value->children as $child) {
                    $children .= $this->navLink($child);
                }
                $htm .= sprintf(
                    '<li class="dropdown">
                        <a href="#" id="%s" class="nav-link dropdown-toggle text-body %s" 
                                data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> 
                            %s  
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            %s
                        </ul>
                     </li>
                    ',
                    attr($value->menu_id),
                    (isset($value->class) ? attr($value->class) : ''),
                    text($value->label),
                    $children,
                );
            } else {
                $htm .= $this->navLink($value);
            }
        }

        // Put nav shell around nav items
        $htm = sprintf(
            '<!--navbar-light is needed for color override in other themes-->
            <nav class="navbar navbar-expand-md navbar-light bg-light">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ptMenuNavbar"
                        aria-controls="ptMenuNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="ptMenuNavbar">
                    <ul class="navbar-nav">
                        %s
                    </ul>
                </div>
            </nav>
            ',
            $htm,
        );
        return $htm;
    }

    /**
     * make the url absolute to web root
     * @param $rel_url
     *
     * @return string
     */
    private function getAbsoluteWebRoot($rel_url)
    {
        if ($rel_url && !strpos($rel_url, "://")) {
            return $GLOBALS['webroot'] . "/" . $rel_url;
        }
        return $rel_url;
    }
}
