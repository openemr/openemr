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
 * Sherwin Gaddis <sherwingaddis@gmail.com>
 * @link    http://www.open-emr.org
 */
 
 require_once("../globals.php");
/*
*   check to see if RxNorm installed
*/ 
	$rxn = sqlQuery("SELECT table_name FROM information_schema.tables WHERE table_name = 'rxnconso';");
if($rxn == false){
	
	die("Could not find RxNorm Table! Please install.");
} 
/*
*   Grab medication list from prescriptions list
*   load into array
*/
 $medList = sqlStatement("SELECT drug FROM prescriptions WHERE active = 1 AND patient_id = ".$pid);
 $nameList = array();
while($name = sqlFetchArray($medList)){
	$drug = explode(" ", $name['drug']);
	$rXn = sqlQuery("SELECT rxcui FROM rxnconso WHERE str LIKE '%".$drug[0]."%'");
	$nameList[] = $rXn['rxcui'];
}
/*
*  make sure there are drugs to compare
*/
$n = count($nameList);

echo ($n < 2) ? "Need more than one drug." : false;
echo ($n < 2) ? exit : false ; 

/*
*  If there are drugs to compare, build the URL 
*
*/ 
 foreach($nameList as $number){
	 $seq .= $number."+";
 }
 
$data = file_get_contents("https://rxnav.nlm.nih.gov/REST/interaction/list.json?rxcuis=".$seq);

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
<span class="title"><?php xl('Drug - Drug Interaction','e'); ?></span>
<br><br>
<?php

/*
*  Display the drug interactions if any
*
*/
if(!empty($json['fullInteractionTypeGroup'][0]['fullInteractionType'])){
  foreach($json['fullInteractionTypeGroup'][0]['fullInteractionType'] as $item){
	
	print xl('Comment: ').$item['comment']."</br>";
	print xl('Drug1 Name: ').$item['minConcept'][0]['name']."</br>";
	print xl('Drug2 Name: ').$item['minConcept'][1]['name']."</br>";
	print xl('Severity: ').$item['interactionPair'][0]['severity']."</br>";
	print xl('Discription: ').$item['interactionPair'][0]['description']."</br></br>";
  }
}else{
	echo xl('No interactions found'); 
}
?>
</body>
</html>