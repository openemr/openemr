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
 * @author  Alfonzo Perez  <aperez@hitechcompliance.net>
 * @link    http://www.open-emr.org
 */

$sanitize_all_escapes = true;   // SANITIZE ALL ESCAPES

$fake_register_globals = false;   // STOP FAKE REGISTER GLOBALS

require_once('../globals.php');

$state = filter_input(INPUT_POST, "form_state"); //stores the variable sent in the post 

$ref = $_SERVER["HTTP_REFERER"];     //stores the url the post came from to redirect back to 
echo $ref."?status=finished";
exit;
$sql = "SELECT MAX(id) FROM pharmacies";  // Find last record in the table
$getMaxId = sqlQuery($sql);    //load to variable


$id = ++$getMaxId['MAX(id)'];  // set start import ID to max id plus 1

   /*
   *  Opens the CSV file and reads each line 
   */
$file = fopen("pharmacyList3.csv","r");

   while(! feof($file))    //This loop continues till the end of the file is reached. 
   { 
      
      $line = fgets($file);          
      $entry = explode(",", $line);
      $tm = 1;
      //check entry 7 to match state  
     if($entry[7] == "PR"){                 //In the next iteration this needs to be gotten from the globals
         ++$i;              //loop count
    
           $phone = str_replace(" ", "-", $entry[10]);  //reformat the phone numbers and fax number
           $fax = str_replace(" ", "-", $entry[11]);
           

/*
*   check the name is in the table
*   if it is skip to the next name on the list
*/

  $sql = "SELECT id FROM pharmacies WHERE name = ?";
  $getNameId = sqlQuery($sql, array($entry[3]));

  if(empty($getNameId)){
      $sql = "INSERT INTO pharmacies (id, name, transmit_method, email, ncpdp, npi)VALUES(?,?,?,?,?,?)";
      $newInsert = array($id, $entry[3], 1, NULL, $entry[1], $entry[2]);
      sqlStatement($sql, $newInsert);

      //Insert Address into address table
      $fid = $id;        // Set the foreign_id to the id in the pharmacies table.
      $aid = ++$id;      // Set the address record to plus one 
    $asql = "INSERT INTO addresses  (`id`, `line1`, `line2`, `city`, `state`, `zip`, `plus_four`, `country`, `foreign_id`) VALUES 
      (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $addressInsert = array($aid, $entry[4], $entry[5], $entry[6], $entry[7], $entry[8], '','USA', $fid);
    sqlStatement($asql, $addressInsert);

    //Insert Phone and Fax number 
    $exPhone = explode("-", $phone);
    $exFax  = explode("-", $fax);

    $psql = "INSERT INTO phone_numbers(id, country_code, area_code, prefix, number, type, foreign_id)VALUES(?,?,?,?,?,?,?)";
    $phoneInsert = array($aid, 1, $exPhone[0], $exPhone[1], $exPhone[2], 2, $fid);
    sqlStatement($psql, $phoneInsert);
    ++$aid;
    $faxInsert = array($aid, 1, $exFax[0], $exFax[1], $exFax[2], 5, $fid);
    sqlStatement($psql, $faxInsert);


  }
  

        ++$id;
       ob_start();
         echo $id. "<br/>";
           //var_dump($entry);
       ob_end_clean();

     }
   }

 fclose($file);

 header("Location: ". $ref."?status=finished");