<?php

/**
 * Abstract configuration class. We recommend subclassing this
 * class for your configuration to make the default routing
 * work properly.
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

namespace ESign;

require_once $GLOBALS['srcdir'] . '/ESign/ConfigurationIF.php';

abstract class Abstract_Configuration implements ConfigurationIF
{
    public function getLogViewMethod()
    {
        return "esign_log_view";
    }

    public function getFormViewMethod()
    {
        return "esign_form_view";
    }

    public function getFormSubmitMethod()
    {
        return "esign_form_submit";
    }

    public function getBaseUrl()
    {
        return $GLOBALS['webroot'] . "/interface/esign/index.php";
    }

    public function getLogViewAction()
    {
        return $this->getBaseUrl() . "?module=" . $this->getModule() . "&method=" . $this->getLogViewMethod();
    }

    public function getFormViewAction()
    {
        return $this->getBaseUrl() . "?module=" . $this->getModule() . "&method=" . $this->getFormViewMethod();
    }

    public function getFormSubmitAction()
    {
        return $this->getBaseUrl() . "?module=" . $this->getModule() . "&method=" . $this->getFormSubmitMethod();
    }
}
