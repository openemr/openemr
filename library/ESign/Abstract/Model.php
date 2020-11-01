<?php

namespace ESign;

/**
 * Any model can inherit this class to add some common functionality
 *
 * Copyright (C) 2013 OEMR 501c3 www.oemr.org
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
 * @author  Ken Chapple <ken@mi-squared.com>
 * @author  Medical Information Integration, LLC
 * @link    https://www.open-emr.org
 **/

abstract class Abstract_Model
{
    private $_args = array();

    public function __construct(array $args = null)
    {
        if ($args !== null) {
            $this->_args = $args;
        }
    }

    protected function pushArgs($force = false)
    {
        foreach ($this->_args as $key => $value) {
            if ($force) {
                $this->{$key} = $value;
            } else {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                } elseif (property_exists($this, "_" . $key)) {
                    $this->{"_" . $key} = $value;
                }
            }
        }
    }
}
