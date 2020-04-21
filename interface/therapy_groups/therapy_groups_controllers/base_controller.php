<?php

/**
 * interface/therapy_groups/therapy_groups_controllers/base_controller.php contains the base controller for therapy groups.
 *
 * This is the base controller from which all therapy group controllers inherit.
 *
 * Copyright (C) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * Copyright (C) 2016 Amiel Elboim <amielel@matrix.co.il>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author  Amiel Elboim <amielel@matrix.co.il>
 * @link    http://www.open-emr.org
 */

class BaseController
{

    const VIEW_FOLDER = 'therapy_groups_views';
    const MODEL_FOLDER = 'therapy_groups_models';


    /**
     * @param $template view name
     * @param array $data variables for injection into view
     */
    protected function loadView($template, $data = array())
    {

        $template = dirname(__FILE__) . '/../' . self::VIEW_FOLDER . '/' . $template . '.php';

        extract($data);

        ob_start();
        require($template);
        echo ob_get_clean();
        exit();
    }


    protected function loadModel($name)
    {
        if (!isset($this->$name)) {
            require(dirname(__FILE__) . '/../' . self::MODEL_FOLDER . '/' . strtolower($name) . '_model.php');
            $this->$name = new $name();
        }

        return $this->$name;
    }
}
