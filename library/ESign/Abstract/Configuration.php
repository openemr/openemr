<?php

/**
 * Abstract configuration class. We recommend subclassing this
 * class for your configuration to make the default routing
 * work properly.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Medical Information Integration, LLC
 * @copyright Copyright (c) 2013 OEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

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
