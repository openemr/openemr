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

$sanitize_all_escapes = true;		// SANITIZE ALL ESCAPES

$fake_register_globals = false;		// STOP FAKE REGISTER GLOBALS

require_once('../globals.php');
require_once('transmitDataClass.php');
require_once("adminClass.php");

$isActive = new transmitData();
$tables   = new adminProperties();

$active = $isActive->active();
$exist  = $tables->dataBaseTableExist();

//var_dump($exist);

?>
<html>
<head>
     <title>Weno Admin</title>
    <?php html_header_show(); ?>
    <link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">

</head>


<body class="body_top">
<?php 

if($active['gl_value'] != 1){ print "You must activate Weno first!"; exit; } else {print "Weno Service is Enabled<br>";} 



if(empty($exist[0]) && empty($exist[1]) && empty($exist[2]) && empty($exist[3])){

	print "All table are being installed. Please wait<br>";

	$go = $tables->createTables();

	print $go;

    } else { 
    	print "All tables are installed<br><br>"; 
    }
  
   $drugData = $tables->drugTableInfo();
if(!$drugData['ndc']){
   echo "<button><a href='drugPaidInsert.php'>Install Drugs Info</a></button> <br><br><br>Be patient this may take a while";
} else {
	print "Drugs inserted into table<br>";


}
//future use...
//if(!$tables->pharmacies()){
?>
<!--
<h3>Select State to Import</h3>
<form method="post" action="pharmacyInsert.php">
<select name="state">
	<option value="AL">AL</option>
	<option value="AK">AK</option>
	<option value="AZ">AZ</option>
	<option value="AR">AR</option>
	<option value="CA">CA</option>
	<option value="CO">CO</option>
	<option value="CT">CT</option>
	<option value="DE">DE</option>
	<option value="DC">DC</option>
	<option value="FL">FL</option>
	<option value="GA">GA</option>
	<option value="HI">HI</option>
	<option value="ID">ID</option>
	<option value="IL">IL</option>
	<option value="IN">IN</option>
	<option value="IA">IA</option>
	<option value="KS">KS</option>
	<option value="KY">KY</option>
	<option value="LA">LA</option>
	<option value="ME">ME</option>
	<option value="MD">MD</option>
	<option value="MA">MA</option>
	<option value="MI">MI</option>
	<option value="MN">MN</option>
	<option value="MS">MS</option>
	<option value="MO">MO</option>
	<option value="MT">MT</option>
	<option value="NE">NE</option>
	<option value="NV">NV</option>
	<option value="NH">NH</option>
	<option value="NJ">NJ</option>
	<option value="NM">NM</option>
	<option value="NY">NY</option>
	<option value="NC">NC</option>
	<option value="ND">ND</option>
	<option value="OH">OH</option>
	<option value="OK">OK</option>
	<option value="OR">OR</option>
	<option value="PA">PA</option>
	<option value="PR">PR</option>
	<option value="RI">RI</option>
	<option value="SC">SC</option>
	<option value="SD">SD</option>
	<option value="TN">TN</option>
	<option value="TX">TX</option>
	<option value="UT">UT</option>
	<option value="VT">VT</option>
	<option value="VA">VA</option>
	<option value="WA">WA</option>
	<option value="WV">WV</option>
	<option value="WI">WI</option>
	<option value="WY">WY</option>
</select><br><br>
<input type="submit" value="Import Pharmacies"><br>
<p>Be patient, this can take a while.<br> There are 69852 records to sort through</p>
</form>
-->

<?php /*} else {
     print "Pharmacies have been installed";
	}
}
*/
?>


</body>
</html>


