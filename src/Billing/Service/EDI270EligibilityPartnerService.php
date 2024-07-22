<?php

/**
 * Code moved from the EDI270 class to the EDI270EligibilityService class
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2024 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing\Service;

class EDI270EligibilityPartnerService
{
    // return array of X12 partners
    // if id return just that id
    public static function getX12Partner($id = 0)
    {
        global $X12info;
        $id = (int)$id;
        $returnval = [];

        if ((int)$id > 0) {
            $returnval = sqlQuery("select * from x12_partners WHERE id = ?", array($id));
            $X12info = $returnval;
        } else {
            $rez = sqlStatement("select * from x12_partners");
            for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
                $returnval[$iter] = $row;
            }
        }

        return $returnval;
    }
    // return array of provider usernames
    public static function getUsernames()
    {
        $rez = sqlStatement("select distinct username, lname, fname,id from users " .
            "where authorized = 1 and username != ''");
        for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
            $returnval[$iter] = $row;
        }

        return $returnval;
    }
}
