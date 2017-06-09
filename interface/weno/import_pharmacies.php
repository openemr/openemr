<?php
/**
 * weno rx pharmacy import.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author  Alfonzo Perez  <aperez@hitechcompliance.net>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once('../globals.php');


$state = filter_input(INPUT_POST, "form_state"); //stores the variable sent in the post 

$ref = $_SERVER["HTTP_REFERER"];     //stores the url the post came from to redirect back to 

$sql = "SELECT MAX(id) FROM pharmacies";  // Find last record in the table
$getMaxId = sqlQuery($sql);    //load to variable


$id = ++$getMaxId['MAX(id)'];  // set start import ID to max id plus 1

   /*
   *  Opens the CSV file and reads each line 
   */
$file = fopen("pharmacyList.csv","r");

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


       } //data insert if not present
     } //loop conditional
   } //end of loop
  
 fclose($file);

 header("Location: ". $ref."?status=finished");

 ?>
 <i class="fa fa-refresh fa-spin fa-3x fa-fw"></i>
<span class="sr-only"><?php echo xlt("Loading... Please wait"); ?></span>