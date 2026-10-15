<?php

/**
 * MainMenuRole class. is fired in the main.php file to load the main menu.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Menu;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\UserService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MainMenuRole extends MenuRole
{
    /**
     * Constructor
     */
    public function __construct(private readonly EventDispatcherInterface $dispatcher)
    {
        // This is where the magic happens to support special menu items.
        //   An empty menu_update_map array is created in MenuRole class
        //   constructor. Adding to this array will link special menu items
        //   to functions in this class.
        parent::__construct();
        $this->menu_update_map["Visit Forms"] = "updateVisitForms";
        $this->menu_update_map["Blank Forms"] = "updateBlankForms";
    }

    /**
     * Collect the Menu for logged in user.
     *
     * @return array<mixed> representation of the Menu
     */
    public function getMenu(): array
    {
        // Collect the selected menu of user
        $mainMenuRole = $this->getMenuRole();

        // Validate that the menu role filename is a basename only (no path traversal)
        if ($mainMenuRole !== basename($mainMenuRole) || str_contains($mainMenuRole, '..')) {
            ServiceContainer::getLogger()->error("Invalid menu role filename rejected", ['filename' => $mainMenuRole]);
            die("\nInvalid menu role filename.");
        }

        // Load the selected menu
        if (str_ends_with($mainMenuRole, '.json')) {
            // load custom menu (includes .json in id)
            $menu_json = file_get_contents(OEGlobalsBag::getInstance()->getString('OE_SITE_DIR') . "/documents/custom_menus/" . $mainMenuRole);
        } else {
            // load a standardized menu (does not include .json in id)
            $menu_json = file_get_contents(OEGlobalsBag::getInstance()->getKernel()->getProjectDir() . "/interface/main/tabs/menu/menus/" . $mainMenuRole . ".json");
        }
        $menu_parsed = $menu_json === false ? null : json_decode($menu_json);

        // if error, then die and report error
        if (!is_array($menu_parsed) || $menu_parsed === []) {
            die("\nJSON ERROR: " . json_last_error());
        }

        // The update and restriction helpers take their arrays by reference
        // and are untyped, so narrow what comes back.
        $this->menuUpdateEntries($menu_parsed);
        $updatedMenuEvent = $this->dispatcher->dispatch(new MenuEvent(is_array($menu_parsed) ? $menu_parsed : []), MenuEvent::MENU_UPDATE);

        $menu_restrictions = [];
        $tmp = $updatedMenuEvent->getMenu();
        $this->menuApplyRestrictions($tmp, $menu_restrictions);
        $updatedRestrictions = $this->dispatcher->dispatch(new MenuEvent(is_array($menu_restrictions) ? $menu_restrictions : []), MenuEvent::MENU_RESTRICT);

        return $updatedRestrictions->getMenu();
    }

    /**
     * Build the html select element to list the MainMenuRole options.
     *
     * @param string $selected Current MainMenuRole for current users.
     * @return string Html select element to list the MainMenuRole options.
     */
    public function displayMenuRoleSelector($selected = ""): string
    {
        $output = "<select name='main_menu_role' id='main_menu_role' class='form-control'>";
        $output .= "<option value='standard' " . (($selected == "standard") ? "selected" : "") . ">" . xlt("Standard") . "</option>";
        $output .= "<option value='answering_service' " . (($selected == "answering_service") ? "selected" : "") . ">" . xlt("Answering Service") . "</option>";
        $output .= "<option value='front_office' " . (($selected == "front_office") ? "selected" : "") . ">" . xlt("Front Office") . "</option>";
        $customMenuDir = OEGlobalsBag::getInstance()->getString('OE_SITE_DIR') . "/documents/custom_menus";
        $dHandle = file_exists($customMenuDir) ? opendir($customMenuDir) : false;
        if ($dHandle !== false) {
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
     * Collect the MainMenuRole for logged in user.
     *
     * @return string Identifier for the MainMenuRole
     */
    private function getMenuRole(): string
    {
        $userService = new UserService();
        $user = $userService->getCurrentlyLoggedInUser();
        $mainMenuRole = $user === false ? '' : $user['main_menu_role'];
        // '' and '0' both mean "no role set", as the empty() check this replaces did.
        if ($mainMenuRole === '' || $mainMenuRole === '0') {
            $mainMenuRole = "standard";
        }

        return $mainMenuRole;
    }

    /**
     * Fill Patient > Visit Forms with the active encounter forms, by category.
     */
    protected function updateVisitForms(\stdClass $menu_list): void
    {
        $menu_list->children = FormCategoryMenu::build(
            getFormsByCategory('1', false),
            2,
            static function (string $directory, array $row): \stdClass {
                $formEntry = new \stdClass();
                $formEntry->url = '/interface/patient_file/encounter/load_form.php?formname=' . urlencode($directory);
                $formEntry->requirement = 2;
                $formEntry->target = 'enc';
                // Plug in ACO attribute, if any, of this form.
                $aco = is_string($row['aco_spec'] ?? null) ? explode('|', $row['aco_spec']) : [];
                if (($aco[1] ?? '') !== '' && $aco[1] !== '0') {
                    $formEntry->acl_req = [$aco[0], $aco[1], 'write', 'addonly'];
                }
                return $formEntry;
            },
        );
    }

    /**
     * Add LBF entries to the Blank Forms lists, by category, after the core
     * items already there. Because these are blank forms there are no access
     * restrictions.
     */
    protected function updateBlankForms(\stdClass $menu_list): void
    {
        $existing = is_array($menu_list->children ?? null) ? $menu_list->children : [];
        $menu_list->children = [
            ...$existing,
            ...FormCategoryMenu::build(
                getFormsByCategory('1', true),
                0,
                static function (string $directory): \stdClass {
                    $formEntry = new \stdClass();
                    $formEntry->url = '/interface/forms/LBF/printable.php?isform=1&formname=' . urlencode($directory);
                    $formEntry->requirement = 0;
                    $formEntry->target = 'pop';
                    return $formEntry;
                },
            ),
        ];
    }
}
