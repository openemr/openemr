<?php

namespace ESign;

/**
 * Form implementation of SignableIF interface, which represents an
 * object that can be signed, locked and/or amended.
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
require_once $GLOBALS['srcdir'].'/formdata.inc.php';

class Form_Signable extends DbRow_Signable implements SignableIF
{
    protected $_encounterId = null;
    protected $_formId = null;
    protected $_formDir = null;
    
    public function __construct( $formId, $formDir, $encounterId )
    {
        $this->_formId = $formId;
        $this->_formDir = $formDir;
        $this->_encounterId = $encounterId;
        parent::__construct( $formId, 'forms' );
    }
    
    protected function getLastLockHash()
    {
        $hash = null;
        if ( $this->isLocked() ) {
            // Check to see if there was an explicit lock hash
            $hash = parent::getLastLockHash();
            
            // If there was no explicit lock hash, then we must have been locked because
            // our encounter was locked, so get our last hash
            if ( $hash === null ) {
                $statement = "SELECT E.tid, E.table, E.hash FROM esign_signatures E ";
                $statement .= "WHERE E.tid = ? AND E.table = ? ";
                $statement .= "ORDER BY E.datetime DESC LIMIT 1";
                $row = sqlQuery( $statement, array( $this->_tableId, $this->_tableName ) );
                $hash = null;
                if ( $row && isset($row['hash']) ) {
                    $hash = $row['hash'];
                }
            }
        }
        
        return $hash;
    }
    
    /**
     * Check to see if this table is locked (read-only)
     * 
     * A form is locked if it has a lock entry in the esign_signatures
     * table, or if it's encounter is locked.
     * 
     * @see \ESign\DbRow_Signable::isLocked()
     */
    public function isLocked()
    {
        // Initialize to false and check individual form
        $locked = false;
        if ( $GLOBALS['lock_esign_individual'] ) {
            $locked = parent::isLocked();
        }
        
        // Check the "parent" encounter if signing is allowed at encounter level
        if ( !$locked && $GLOBALS['lock_esign_all'] ) {
            $statement = "SELECT E.is_lock FROM esign_signatures E ";
            $statement .= "WHERE E.tid = ? AND E.table = ? AND E.is_lock = ? ";
            $statement .= "ORDER BY E.datetime DESC LIMIT 1";
            $row = sqlQuery( $statement, array( $this->_encounterId, 'form_encounter', SignatureIF::ESIGN_LOCK ) );
            if ( $row && $row['is_lock'] == SignatureIF::ESIGN_LOCK ) {
                $locked = true;
            }
        }
    
        return $locked;
    }
    
    /**
     * Get the data in an array for this form.
     * 
     * First, we check the forms table to get the row id in the
     * specific table. Then we get the row of data from the specific
     * form_* table.
     * 
     * @see \ESign\SignableIF::getData()
     */
    public function getData()
    {
        // We assume that the formdir is the same as the table suffix, 
        // but this may not always be the case. TODO In the future, 
        // create a list in the list_options for formdir => table mapping
        $table = "form_".$this->_formDir;
        if ( $this->_formDir == 'newpatient' ) {
            $table = "form_encounter";
        }
        
        // Get row from forms table
        $statement = "SELECT F.id, F.date, F.encounter, F.form_name, F.form_id, F.pid, F.user, F.formdir FROM forms F ";
        $statement .= "WHERE F.id = ? LIMIT 1";
        $row = sqlQuery( $statement, array( $this->_formId ) );
        
        // Get form-specific data
        $statement = "SELECT * FROM ".escape_table_name( $table )." ";
        $statement .= "WHERE id = ? LIMIT 1";
        $formRow = sqlQuery( $statement, array( $row['form_id']) );
        
        return $formRow;
    }
}
