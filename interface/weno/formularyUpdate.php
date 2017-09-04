<?php
/**
 * weno admin.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
 require_once("../globals.php");

$zip = new ZipArchive;
$zip->open($GLOBALS['temporary_files_dir'].'/FormularyFiles.zip');
$zip->extractTo($GLOBALS['temporary_files_dir']);
$zip->close();

 $file = "./Weno Exchange LLC/BSureFormulary.txt";
 $lines = count(file($file));
 echo text($lines). "<br>";

 $i = 2;
do {
    $fileName = new SplFileObject($file);
    $fileName->seek($i);
    echo text($fileName)."<br>";   //return the first line from file
 
    $in = explode("|", $fileName);
 
    echo text($in[2])."<br>";
    echo text($in[5])."<br>";
    echo text($in[8])."<br>";
 
    $ndc = $in[2];
    $price = $in[5];
    $drugName = $in[8];
 
    $find = sqlQuery("SELECT ndc FROM erx_drug_paid WHERE ndc = ?", array($ndc));
    /**
     *  Insert drug if not found in the table
     */
    if (!empty($find['ndc'])) {
        sqlStatementNoLog("INSERT INTO `erx_drug_paid` SET `drug_label_name` = ?, `NDC` = ?, `price_per_unit` = ? ", array($drugName,$ndc,$price));
        echo xlt("Inserted")."<br> ";
    } else {
        /**
         *  If the drug is found update the price.
         */
        sqlStatementNoLog("UPDATE erx_drug_paid SET price_per_unit = ? WHERE ndc = ?", array($price, $ndc));
        echo xlt("Updated!")."<br>";
    }
    $i++;
} while ($i < $lines);
