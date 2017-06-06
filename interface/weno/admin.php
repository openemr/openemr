<?php

/**
 * admin for weno rx.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once('../globals.php');
require_once('transmitDataClass.php');
require_once("adminClass.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Core\Header;

$tables   = new adminProperties();
$exist  = $tables->dataBaseTableExist();

//var_dump($exist);
$finished = filter_input(INPUT_GET, 'status');
?>
<html>
<head>
     <title><?php print xlt("Weno Admin"); ?></title>
     <?php Header::setupHeader(); ?>

</head>


<body class="body_top">
<?php 

if($GLOBALS['weno_rx_enable'] != 1){ 
   print xlt("You must activate Weno first!"); 
   exit; 
 } else {
    print xlt("Weno Service is Enabled")."<br>";
 } 



if(empty($exist[0]) && empty($exist[1]) && empty($exist[2]) && empty($exist[3])){

	print xlt("All table are being installed. Please wait")."<br>";

	$go = $tables->createTables();

	print text($go);

    } else { 
    	print xlt("All tables are installed")."<br><br>"; 
    }
  
   $drugData = $tables->drugTableInfo();
if(!$drugData['ndc']){
   echo "<a href='drugPaidInsert.php' class='btn'>".xlt("Install Drugs Info")."</a> <br><br><br>".xlt("Be patient this may take a while");
} else {
	print xlt("Drugs inserted into table")."<br>";


}

?>

<h3><?php echo xlt("Select State to Import"); ?></h3>
<form method="post" action="import_pharmacies.php" >
<?php
echo generate_form_field(array('data_type'=>$GLOBALS['state_data_type'],'list_id'=>$GLOBALS['state_list'], 'field_id'=>'state'));

?><br><br>
<input type="submit" value="Import Pharmacies"><br>
<p><?php echo xlt("Be patient, this can take a while."); ?><br> <?php echo xlt("There are 69852 records to sort through"); ?></p>
</form>
<br><br>

<?php  if(!empty($finish)){echo $finish . xlt("with import");} ?>



</body>
</html>


