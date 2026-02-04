<?php

/**
 * Contains all the methods for creation of ESign object
 * components for the Form module
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
require_once $GLOBALS['srcdir'] . '/ESign/Form/Configuration.php';
require_once $GLOBALS['srcdir'] . '/ESign/Form/Signable.php';
require_once $GLOBALS['srcdir'] . '/ESign/Form/LBF/Signable.php';
require_once $GLOBALS['srcdir'] . '/ESign/Form/Button.php';
require_once $GLOBALS['srcdir'] . '/ESign/Form/Log.php';

class Form_Factory implements FactoryIF
{
    public function __construct(protected $_formId, protected $_formDir, protected $_encounterId)
    {
    }

    public function createConfiguration()
    {
        return new Form_Configuration();
    }

    public function createSignable()
    {
        $signable = null;
        if (str_starts_with((string) $this->_formDir, 'LBF')) {
            $signable = new Form_LBF_Signable($this->_formId, $this->_formDir, $this->_encounterId);
        } else {
            $signable = new Form_Signable($this->_formId, $this->_formDir, $this->_encounterId);
        }

        return $signable;
    }

    public function createButton()
    {
        return new Form_Button($this->_formId, $this->_formDir, $this->_encounterId);
    }

    public function createLog()
    {
        return new Form_Log($this->_formId, $this->_formDir, $this->_encounterId);
    }
}
