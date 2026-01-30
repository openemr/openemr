<?php

/**
 * Contains all the methods for creation of ESign object
 * components for the Encounter module
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Medical Information Integration, LLC
 * @copyright Copyright (c) 2013 OEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace ESign;

require_once $GLOBALS['srcdir'] . '/ESign/FactoryIF.php';
require_once $GLOBALS['srcdir'] . '/ESign/Encounter/Configuration.php';
require_once $GLOBALS['srcdir'] . '/ESign/Encounter/Signable.php';
require_once $GLOBALS['srcdir'] . '/ESign/Encounter/Button.php';
require_once $GLOBALS['srcdir'] . '/ESign/Encounter/Log.php';

class Encounter_Factory implements FactoryIF
{
    public function __construct(protected $_encounterId)
    {
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
