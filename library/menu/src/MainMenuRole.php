<?php
/**
 * MainMenuRole class.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Menu;

use OpenEMR\Services\UserService;

class MainMenuRole
{

    /**
     * Collect the MainMenuRole for logged in user.
     *
     * @return string Identifier for the MainMenuRole
     */
    public static function getMainMenuRole()
    {
        $userService = new UserService();
        $user = $userService->getCurrentlyLoggedInUser();
        $mainMenuRole = $user->getMainMenuRole();
        if (empty($mainMenuRole)) {
            $mainMenuRole = "standard";
        }

        return $mainMenuRole;
    }

    /**
     * Build the html select element to list the MainMenuRole options.
     *
     * @var string $selected Current MainMenuRole for current users.
     * @return string Html select element to list the MainMenuRole options.
     */
    public static function displayMainMenuRoleSelector($selected = "")
    {
        $output = "<select name='main_menu_role' id='main_menu_role' class='form-control'>";
        $output .= "<option value='standard' " . (($selected == "standard") ? "selected" : "") . ">" . xlt("Standard") . "</option>";
        $output .= "<option value='answering_service' " . (($selected == "answering_service") ? "selected" : "") . ">" . xlt("Answering Service") . "</option>";
        $output .= "<option value='front_office' " . (($selected == "front_office") ? "selected" : "") . ">" . xlt("Front Office") . "</option>";
        $customMenuDir = $GLOBALS['OE_SITE_DIR'] . "/documents/custom_menus";
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
}
