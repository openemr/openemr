<?php
/**
 * weno rx formulary update.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

 require_once("../globals.php");
 
 echo xlt("Trying")."<BR>";

$zip = new ZipArchive;
$zip->open('FormularyFiles.zip');
$zip->extractTo('./');
$zip->close();

 $file = "./Weno Exchange LLC/BSureFormulary.txt";
 $lines = count(file($file));
 echo text($lines). "<br>";
  
 $i = 2;

 do{
 $fileName = new SplFileObject($file);
 $fileName->seek($i);
 
 $in = explode("|", $fileName);

 $ndc = $in[2];
 $price = $in[5];
 $drugName = $in[8];

	 sqlStatement("INSERT INTO `erx_drug_paid` SET `drug_label_name` = ?, `NDC` = ?, `price_per_unit` = ? ", array($drugName,$ndc,$price));
	 echo text("Inserted")."". text($drugName) ."<br> "; 	

 $i++;
 } while ($i < $lines);
 
 