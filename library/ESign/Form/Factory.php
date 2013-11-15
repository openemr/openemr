<?php

namespace ESign;

/**
 * Contains all the methods for creation of ESign object
 * components for the Form module
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

require_once $GLOBALS['srcdir'].'/ESign/FactoryIF.php';
require_once $GLOBALS['srcdir'].'/ESign/Form/Configuration.php';
require_once $GLOBALS['srcdir'].'/ESign/Form/Signable.php';
require_once $GLOBALS['srcdir'].'/ESign/Form/LBF/Signable.php';
require_once $GLOBALS['srcdir'].'/ESign/Form/Button.php';
require_once $GLOBALS['srcdir'].'/ESign/Form/Log.php';

class Form_Factory implements FactoryIF
{
    protected $_formId = null;
    protected $_formDir = null;
    protected $_encounterId = null;
    
    public function __construct( $formId, $formDir, $encounterId )
    {
        $this->_formId = $formId;
        $this->_formDir = $formDir;
        $this->_encounterId = $encounterId;
    }
    
    public function createConfiguration()
    {
        return new Form_Configuration();
    }
    
    public function createSignable()
    {
        $signable = null;
        if ( strpos( $this->_formDir, 'LBF' ) === 0 ) {
            $signable = new Form_LBF_Signable( $this->_formId, $this->_formDir, $this->_encounterId );
        } else {
            $signable = new Form_Signable( $this->_formId, $this->_formDir, $this->_encounterId );
        }
        
        return $signable;
    }
    
    public function createButton()
    {
        return new Form_Button( $this->_formId, $this->_formDir, $this->_encounterId );
    }

    public function createLog()
    {
        return new Form_Log( $this->_formId, $this->_formDir, $this->_encounterId );
    }
}
