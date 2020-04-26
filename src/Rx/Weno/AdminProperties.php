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
    {
    }

    public function addNarcotics()
    {

  /*
  * Import the narcotics into the table database from SQL file
  *
  */
        $sqlNarc = file_get_contents('../../contrib/weno/narc.sql');

        // Settings to drastically speed up import with InnoDB
        sqlStatementNoLog("SET autocommit=0");
        sqlStatementNoLog("START TRANSACTION");

        sqlStatementNoLog($sqlNarc);

        // Settings to drastically speed up import with InnoDB
        sqlStatementNoLog("COMMIT");
        sqlStatementNoLog("SET autocommit=1");

        return xlt("Narcotic drugs imported");
    }

    public function pharmacies()
    {
        $sql = "SELECT Store_Name FROM erx_pharmacies ORDER BY id LIMIT 1";
        return sqlQuery($sql);
    }
}
