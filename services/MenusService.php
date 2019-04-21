<?php

/**
 * MenusService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Eyal Wolanowski <eyal.wolanowski@gmail.com>
 * @copyright Copyright (c) 2018 Eyal Wolanowski <eyal.wolanowski@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


namespace OpenEMR\Services;

use OpenEMR\Menu\PatientMenuRole;

class MenusService
{

  /**
   * Default constructor.
   */
    public function __construct()
    {
    }


    public function getMenu($data)
    {

        $menuPatient = new PatientMenuRole();
        $menu_restrictions=$menuPatient->getMenu();
        $pid = $_SESSION['pid'];
        $cat_id = 1;
        $option_id=1;

        $filtered_menu=array();

        foreach ($menu_restrictions as $key => $value) {
            if (!empty($value->children)) {

                $class = isset($value->class) ? $value->class : '';
                $filtered_menu[$cat_id]=array(
                    'id'=>attr($value->menu_id),
                    'class'=> attr($class),
                    'label'=>text($value->label)
                );

                foreach ($value->children as $children_key => $children_value) {


                    $link = ($children_value->pid != "true") ? $children_value->url : $children_value->url . attr($pid);
                    $class = isset($children_value->class) ? $children_value->class : '';

                    $filtered_menu[$option_id][$option_id]=array(
                        'id'=>attr($children_value->menu_id),
                        'class'=> attr($class),
                        'label'=>text($children_value->label),
                        'href'=>attr($link),
                        'onclick'=> $children_value->on_click
                    );

                    $option_id++;
                }

            } else {
                $link = ($value->pid != "true") ? $value->url : $value->url . attr($pid);
                $class = isset($value->class) ? $value->class : '';

                $filtered_menu[$cat_id]=array(
                    'id'=>attr($value->menu_id),
                    'class'=> attr($class),
                    'label'=>text($value->label),
                    'href'=>attr($link),
                    'onclick'=> $value->on_click
                );
            }

            $cat_id++;
        }

        return json_encode($filtered_menu);

    }


}
