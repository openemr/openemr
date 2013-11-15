<?php
namespace ESign;

/**
 *   ESign object consists of the all the essential parts.
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

include_once $GLOBALS['srcdir'].'/sql.inc';

class ESign
{
    private $_configuration = null;
    private $_signable = null;
    private $_button = null;
    private $_log = null;
  
    function __construct( ConfigurationIF $configuration, SignableIF $signable, ButtonIF $button, LogIF $log )
    {
        $this->_configuration = $configuration;
        $this->_signable = $signable;
        $this->_button = $button;
        $this->_log = $log;
    }
    
    /**
     * Check if the signable object is locked from futher editing
     * 
     * @return boolean
     */
    public function isLocked()
    {
        return $this->_signable->isLocked();
    }
    
    public function isButtonViewable()
    {
        return $this->_button->isViewable();
    }
    
    public function isLogViewable()
    {
        $viewable = false;
        // If we have signatures, always show the log, otherwise
        // check the log object
        if ( count( $this->_signable->getSignatures() ) > 0 ) {
            $viewable = true;
        } else {
            $viewable = $this->_log->isViewable();
        }
        
        return $viewable;
    }
    
    public function renderLog()
    {
        $this->_log->render( $this->_signable );
    }
    
    public function buttonHtml()
    {
        return $this->_button->getHtml();
    }

    public function getSignatures()
    {
        return $this->_signable->getSignatures();
    }
}
