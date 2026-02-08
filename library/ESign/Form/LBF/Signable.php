<?php

/**
 * LBF Form implementation of SignableIF interface, which represents an
 * object that can be signed, locked and/or amended.
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

require_once $GLOBALS['srcdir'] . '/ESign/Form/Signable.php';
require_once $GLOBALS['srcdir'] . '/ESign/SignableIF.php';

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
        $row = sqlQuery($statement, [ $this->_formId ]);
        // Now we can look for the data in the lbf_data table.
        $data = [];
        if ($row) {
            $fres = sqlStatement("SELECT field_id, field_value FROM lbf_data WHERE form_id = ?", [ $row['form_id'] ]);
            while ($frow = sqlFetchArray($fres)) {
                $data[$frow['field_id']] = $frow['field_value'];
            }
        }

        return $data;
    }
}
