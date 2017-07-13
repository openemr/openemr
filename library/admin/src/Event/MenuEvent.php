<?php
/**
 * Created by PhpStorm.
 * User: rdown
 * Date: 2017-07-13
 * Time: 00:15
 */

namespace OpenEMR\Admin\Event;


use Symfony\Component\EventDispatcher\Event;

class MenuEvent extends Event
{

    /** @var array The menu list */
    private $menu;

    public function __construct($menu = [])
    {
        $this->menu = $menu;
    }

    public function getMenu()
    {
        return $this->menu;
    }

    public function addMenuItem($name, $link)
    {
        $item = ['name' => $name, 'link' => $link];
        $this->menu[] = $item;
    }
}