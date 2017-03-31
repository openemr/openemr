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
//This was a side project that was to be way to lookup drugs from the gov.
?>



<form name="druglookup" method="post" action="">
<input type="text" name="drug" size="15" value="<?php echo $drug; ?>">
<input type="submit" value="Submit">

</form>
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(!empty($_POST['drug'])){
     $drug = $_POST['drug'];
	 $xml = new SimpleXMLElement(file_get_contents("https://rxnav.nlm.nih.gov/REST/Prescribe/drugs?name=".$drug));


echo "<pre>";
//print_r($xml);
echo "</pre>";
echo xlt("Search term: "). text($xml->drugGroup->name) ."<br><br>";

if(empty($xml->drugGroup->conceptGroup[1]->conceptProperties[0])){
  echo xlt("no results for this seach");
  exit;
}

if(!empty($xml->drugGroup->conceptGroup[1]->conceptProperties[0])){
	$d=1;
}
if(!empty($xml->drugGroup->conceptGroup[2]->conceptProperties[0])){
	$d=2;
}


$i=0;

do{
     echo text($xml->drugGroup->conceptGroup[$d]->conceptProperties[$i]->rxcui)."<br>";
     echo text($xml->drugGroup->conceptGroup[$d]->conceptProperties[$i]->name)."<br>";
     echo text($xml->drugGroup->conceptGroup[$d]->conceptProperties[$i]->synonym)."<br>";
     echo text($xml->drugGroup->conceptGroup[$d]->conceptProperties[$i]->umlscui)."<br><br>";
$i++;
}while(!empty($xml->drugGroup->conceptGroup[$d]->conceptProperties[$i]->rxcui));

if(!empty($xml->drugGroup->conceptGroup[3]->conceptProperties[0])){
$d=3;
if(!empty($xml->drugGroup->conceptGroup[$d]->conceptProperties[0]->rxcui)){
    $i=0;

  do{
      echo text($xml->drugGroup->conceptGroup[$d]->conceptProperties[$i]->rxcui)."<br>";
      echo text($xml->drugGroup->conceptGroup[$d]->conceptProperties[$i]->name)."<br>";
      echo text($xml->drugGroup->conceptGroup[$d]->conceptProperties[$i]->synonym)."<br>";
      echo text($xml->drugGroup->conceptGroup[$d]->conceptProperties[$i]->umlscui)."<br>";
    $i++;
  }while(!empty($xml->drugGroup->conceptGroup[$d]->conceptProperties[$i]->rxcui));	
  }else{
	echo xlt("End of Data") ;

  }
 }
} 
?>

