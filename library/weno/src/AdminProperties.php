<?php
/**
 * AdminProperties class.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Rx\Weno;

class AdminProperties
{

    public function __construct()
    {}



    public function addNarcotics()
    {

  /*
  * Import the narcotics into the table database from SQL file
  *
  */
        $sqlNarc = file_get_contents('../../contrib/weno/narc.sql');

        sqlInsert($sqlNarc);

          return "Narcotic drugs imported<br>";
    }//end of create tables

    public function drugTableInfo()
    {
         $sql = "SELECT ndc FROM erx_drug_paid ORDER BY drugid LIMIT 1";
         return sqlQuery($sql);
    }

    public function pharmacies()
    {
        $sql = "SELECT Store_Name FROM erx_pharmacies ORDER BY id LIMIT 1";
        return sqlQuery($sql);
    }
}
