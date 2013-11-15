<?php

namespace ESign;

/**
 * Implementation of the SignableIF interface for the Encounter
 * module. 
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

require_once $GLOBALS['srcdir'].'/ESign/DbRow/Signable.php';
require_once $GLOBALS['srcdir'].'/ESign/SignableIF.php';
require_once $GLOBALS['srcdir'].'/ESign/Form/Factory.php';
require_once $GLOBALS['srcdir'].'/formdata.inc.php';

class Encounter_Signable extends DbRow_Signable implements SignableIF
{    
    private $_encounterId = null;
    
    public function __construct( $encounterId )
    {
        $this->_encounterId = $encounterId;
        parent::__construct( $encounterId, 'form_encounter' );
    }
    
    /**
     * Implementatinon of getData() for encounters. 
     * 
     * We get all forms under the encounter, and then get all the data
     * from the individual form tables.
     * 
     * @see \ESign\SignableIF::getData()
     */
    public function getData()
    {
        $encStatement = "SELECT F.id, F.date, F.encounter, F.form_name, F.form_id, F.pid, F.user, F.formdir FROM forms F ";
        $encStatement .= "WHERE F.encounter = ? ";
        $data = array();
        $res = sqlStatement( $encStatement, array( $this->_encounterId ) );
        while ( $encRow = sqlFetchArray( $res ) ) {
            $formFactory = new Form_Factory( $encRow['id'], $encRow['formdir'], $this->_encounterId );
            $signable = $formFactory->createSignable();
            $data[]= $signable->getData();
        }
        return $data;
    }
    
    public function isLocked()
    {
        $locked = false;
        if ( $GLOBALS['lock_esign_all'] ) {
            $locked = parent::isLocked();
        }
        
        return $locked;
    }
}
