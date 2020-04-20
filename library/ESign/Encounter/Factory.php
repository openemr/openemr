<?php

/**
 * Contains all the methods for creation of ESign object
 * components for the Encounter module
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
 * @link    http://www.open-emr.org
 **/

namespace ESign;

require_once $GLOBALS['srcdir'] . '/ESign/FactoryIF.php';
require_once $GLOBALS['srcdir'] . '/ESign/Encounter/Configuration.php';
require_once $GLOBALS['srcdir'] . '/ESign/Encounter/Signable.php';
require_once $GLOBALS['srcdir'] . '/ESign/Encounter/Button.php';
require_once $GLOBALS['srcdir'] . '/ESign/Encounter/Log.php';

class Encounter_Factory implements FactoryIF
{
    protected $_encounterId = null;

    public function __construct($encounterId)
    {
        $this->_encounterId = $encounterId;
    }

    public function createConfiguration()
    {
        return new Encounter_Configuration();
    }

    public function createSignable()
    {
        return new Encounter_Signable($this->_encounterId);
    }

    public function createButton()
    {
        return new Encounter_Button($this->_encounterId);
    }

    public function createLog()
    {
        return new Encounter_Log($this->_encounterId);
    }
}
