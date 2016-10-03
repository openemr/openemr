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
 * @author Sherwin Gaddis <sherwingaddis@gmail.com>
 * @link    http://www.open-emr.org
 */

$fake_register_globals=false;
$sanitize_all_escapes=true;
require_once("../globals.php");

/*
*   check to see if RxNorm installed
*/
$rxn = sqlQuery("SELECT table_name FROM information_schema.tables WHERE table_name = 'RXNCONSO' OR table_name = 'rxnconso'");
if($rxn == false){
    die(xlt("Could not find RxNorm Table! Please install."));
}
/*
*   Grab medication list from prescriptions list
*   load into array
*/
$medList = sqlStatement("SELECT drug FROM prescriptions WHERE active = 1 AND patient_id = ?", array($pid));
$nameList = array();
while($name = sqlFetchArray($medList)){
    $drug = explode(" ", $name['drug']);
    $rXn = sqlQuery("SELECT `rxcui` FROM `" . mitigateSqlTableUpperCase('RXNCONSO') . "` WHERE `str` LIKE ?", array("%" . $drug[0] . "%"));
    $nameList[] = $rXn['rxcui'];
}
/*
*  make sure there are drugs to compare
*/
if (count($nameList) < 2) {
    echo xlt("Need more than one drug.");
    exit;
}

/*
*  If there are drugs to compare, collect the data
*
*/
$rxcui_list = implode("+",$nameList);
$data = file_get_contents("https://rxnav.nlm.nih.gov/REST/interaction/list.json?rxcuis=".$rxcui_list);

/*
*   Content from NLM returned
*
*/
$json = json_decode($data, true);

?>
<html>
<head>
    <?php html_header_show();?>
    <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">
    <span class="title"><?php echo xlt('Drug - Drug Interaction'); ?></span>
    <br><br>
    <?php

    /*
    *  Display the drug interactions if any
    *
    */
    if(!empty($json['fullInteractionTypeGroup'][0]['fullInteractionType'])){
        foreach($json['fullInteractionTypeGroup'][0]['fullInteractionType'] as $item){
            print xlt('Comment').":".text($item['comment'])."</br>";
            print xlt('Drug1 Name{{Drug1 Interaction}}').":".text($item['minConcept'][0]['name'])."</br>";
            print xlt('Drug2 Name{{Drug2 Interaction}}').":".text($item['minConcept'][1]['name'])."</br>";
            print xlt('Severity').":". text($item['interactionPair'][0]['severity'])."</br>";
            print xlt('Description').":". text($item['interactionPair'][0]['description'])."</br></br>";
        }
    } else {
        echo xlt('No interactions found');
    }
    ?>
</body>
</html>