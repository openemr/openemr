<?php
/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\Service;

use OpenEMR\Admin\AdminEvents;
use OpenEMR\Admin\Event\MenuEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;


/**
 * Class AdminMenuBuilder.
 *
 * @package OpenEMR\Admin
 * @subpackage Service
 * @author Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2017 Robert Down <robertdown@live.com>
 */
class AdminMenuBuilder
{

    /** @var  EventDispatcher */
    public $dispatcher;

    public $event;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->dispatcher = $eventDispatcher;
        $this->event = new MenuEvent();
    }

    public function buildMenuFromGlobalsMetadataBridge($userMode)
    {
        global $GLOBALS_METADATA;
        global $USER_SPECIFIC_TABS;
        $menuList = [];
        foreach ($GLOBALS_METADATA as $name => $arr) {
            if (!$userMode || in_array($name, $USER_SPECIFIC_TABS)) {
                $id = strtolower(str_replace(' ', '_', $name));
                $text = xlt($name);
                $menuList["{$id}"] = [
                    'text' => $text,
                    'id' => $id,
                    'href' => "#{$id}",
                ];
            }
        }
        return $menuList;
    }

    /**
     * @param array $menu
     * @return array
     */
    public function generateMainMenu($menu)
    {
        $event = new MenuEvent($menu);
        /** @var MenuEvent $result */
        $result = $this->dispatcher->dispatch(AdminEvents::BUILD_MAIN_MENU, $event);
        $newMenu = $result->getMenu();

        // Sort the multidimensional array based on the text we are displaying
        $text = [];
        foreach ($newMenu as $key => $row) {
            $text[$key] = $row['text'];
        }
        array_multisort($text, $newMenu);
        $newMenu[0]['current'] = 'active';

        return $newMenu;
    }

    public function renderMenu($menu)
    {
        // @TODO remove this dependency
        global $GLOBALS;

        foreach ($menu as $key => $item) {
            if (array_key_exists('attributes', $item)) {
                $atts = "";
                foreach ($item['attributes'] as $prop => $value) {
                    $atts .= "{$prop}=\"{$value}\" ";
                }
                $menu["{$key}"]['attributes'] = $atts;
            }
        }
        $context['menuList'] = $menu;
        return $GLOBALS['twig']->render('admin/globalsMenu.html.twig', $context);
    }
}
