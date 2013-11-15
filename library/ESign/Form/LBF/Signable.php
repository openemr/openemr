<?php

namespace ESign;

/**
 * LBF Form implementation of SignableIF interface, which represents an
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

require_once $GLOBALS['srcdir'].'/ESign/Form/Signable.php';
require_once $GLOBALS['srcdir'].'/ESign/SignableIF.php';
require_once $GLOBALS['srcdir'].'/formdata.inc.php';

class Form_LBF_Signable extends Form_Signable implements SignableIF
{
    /**
     * Get the data in an array for this form.
     * 
     * get the lbf form key, and all the entries associates with that key
     * 
     * @see \ESign\SignableIF::getData()
     */
    public function getData()
    {
        // First we have to get the form_id from the forms tagle because that's our key to the lbf_data table
        $statement = "SELECT form_id FROM forms WHERE id = ?";
        $row = sqlQuery( $statement, array( $this->_formId ) );
        // Now we can look for the data in the lbf_data table.
        $data = array();
        if ( $row ) {
            $fres = sqlStatement( "SELECT field_id, field_value FROM lbf_data WHERE form_id = ?", array( $row['form_id'] ) );
            while ( $frow = sqlFetchArray( $fres ) ) {
                $data[$frow['field_id']] = $frow['field_value'];
            }
        }
        
        return $data;
    }
}
