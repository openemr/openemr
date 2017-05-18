<?php


/** Copyright (C) 2016 Sherwin Gaddis <sherwingaddis@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @link    http://www.open-emr.org
 */

class adminProperties {

	public function __construct(){}

	/*
	*   Check to see if the database tables exist
	*/

	public function dataBaseTableExist(){
        
        $dbase = $GLOBALS['dbase'];  //incase there are multiple installs on the same server

        $tables = array("erx_pharmacies","erx_drug_paid","erx_rx_log","erx_narcotics");

		$sql = "SELECT table_name FROM information_schema.tables WHERE table_name = ? AND table_schema = ? ";
        
         $missing = array();
        foreach($tables as $table){
            $isMissing = sqlQuery($sql, array($table,$dbase));
            $missing[] = $isMissing['table_name'] ;
        }

        return $missing;
	}



    public function createTables(){


    	sqlStatement(
              "CREATE TABLE IF NOT EXISTS `erx_drug_paid` (
                            `drugid` int(11) NOT NULL AUTO_INCREMENT,
                            `drug_label_name` varchar(45) NOT NULL,
                            `ahfs_descr` varchar(45) NOT NULL,
                            `ndc` bigint(12) NOT NULL,
                            `price_per_unit` decimal(5,2) NOT NULL,
                            `avg_price` decimal(6,2) NOT NULL,
                            `avg_price_paid` int(6) NOT NULL,
                            `avg_savings` decimal(6,2) NOT NULL,
                            `avg_percent` decimal(6,2) NOT NULL,
                             PRIMARY KEY (`drugid`)
                             ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=26121" 

    		);


    	sqlStatement(
           "CREATE TABLE IF NOT EXISTS `erx_pharmacies` (
                        `id` int(5) NOT NULL AUTO_INCREMENT,
                        `last_updated` varchar(21) DEFAULT NULL,
                        `store_name` varchar(35) DEFAULT NULL,
                        `ncpdp` int(10) DEFAULT NULL,
                        `active` varchar(3) DEFAULT NULL,
                        `npi` varchar(10) DEFAULT NULL,
                        `pharmacy_phone` varchar(15) DEFAULT NULL,
                        `pharmacy_fax` varchar(15) DEFAULT NULL,
                        `address_line_1` varchar(35) DEFAULT NULL,
                        `address_line_2` varchar(16) DEFAULT NULL,
                        `city` varchar(16) DEFAULT NULL,
                        `state` varchar(2) DEFAULT NULL,
                        `zipcode` int(5) DEFAULT NULL,
                        `retail` varchar(4) DEFAULT NULL,
                        `specialty` varchar(5) DEFAULT NULL,
                        `long_term_care` varchar(5) DEFAULT NULL,
                        `Mail_Order` varchar(5) DEFAULT NULL,
                        `Mail_State_Codes` varchar(3) DEFAULT NULL,
                        `Mail_Address_Line_1` varchar(35) DEFAULT NULL,
                        `Mail_Address_Line_2` varchar(16) DEFAULT NULL,
                        `Mail_City` varchar(10) DEFAULT NULL,
                        `Mail_State` varchar(2) DEFAULT NULL,
                        `Mail_Zip_Code` int(9) DEFAULT NULL,
                        `Mail_Phone` int(10) DEFAULT NULL,
                        `Mail_Fax` int(10) DEFAULT NULL,
                        `EPCS_Permitted` varchar(5) DEFAULT NULL,
                        `Accept_NewRx` varchar(4) DEFAULT NULL,
                        `Accept_Refillresponse` varchar(5) DEFAULT NULL,
                        `Accept_RxChangeresponse` varchar(5) DEFAULT NULL,
                        `Accept_Verify` varchar(5) DEFAULT NULL,
                        `Accept_Cancelrx` varchar(5) DEFAULT NULL,
                        `Accept_RxHistoryrequest` varchar(5) DEFAULT NULL,
                        `Accept_RxHistoryresponse` varchar(5) DEFAULT NULL,
                        `Accept_Census` varchar(5) DEFAULT NULL,
                        `Accept_Resupply` varchar(5) DEFAULT NULL,
                        PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11150"
    		);

          sqlStatement(
            "CREATE TABLE IF NOT EXISTS `erx_rx_log` (
                         `id` int(20) NOT NULL AUTO_INCREMENT,
                         `prescription_id` int(6) NOT NULL,
                         `date` varchar(25) NOT NULL,
                         `time` varchar(15) NOT NULL,
                         `code` int(6) NOT NULL,
                         `status` text NOT NULL,
                         `message_id` varchar(100) DEFAULT NULL,
                         `read` int(1) DEFAULT NULL,
                         PRIMARY KEY (`id`)
                          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=133" 
          	);

          sqlStatement(
              "CREATE TABLE IF NOT EXISTS `erx_narcotics` (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `drug` varchar(255) NOT NULL,
                            `dea_number` varchar(5) NOT NULL,
                            `csa_sch` varchar(2) NOT NULL,
                            `narc` varchar(2) NOT NULL,
                            `other_names` varchar(255) NOT NULL,
                             PRIMARY KEY (`id`)
                           ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=570"
          	);


/*          
          * Import the narcotics into the table database from SQL file
          *
*/
          $sqlNarc = file_get_contents('narc.sql');

sqlInsert($sqlNarc);

          return "Tables Created!<br>";

        }//end of create tables

public function drugTableInfo(){
     $sql = "SELECT ndc FROM erx_drug_paid ORDER BY drugid LIMIT 1";
     
     return sqlQuery($sql);

}

public function pharmacies(){
  $sql = "SELECT Store_Name FROM erx_pharmacies ORDER BY id LIMIT 1";
  return sqlQuery($sql);
}

}//End of class