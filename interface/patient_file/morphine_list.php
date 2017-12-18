<?php
/**
 * Morphine List
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("morphine_helper.php");
use OpenEMR\Core\Header;

$showList = getList();

if(!empty($_POST['drugname'])){
	
	$id = filter_input(INPUT_POST, 'id');
	$drugname = trim(filter_input(INPUT_POST, 'drugname'));
	$multiplier = trim(filter_input(INPUT_POST, 'multiplier'));
	$days = filter_input(INPUT_POST, 'days');
    saveEntry($id, $drugname, $multiplier, $days);
	echo "<meta http-equiv='refresh' content='0'>";
}

if(!empty($_GET['update']))
{
	$id = filter_input(INPUT_GET, 'update');
	$updateData = updateDrug($id);
	
}

if(!empty($_GET['delete']))
{
	$id = filter_input(INPUT_GET, 'delete');
	deleteEntry($id);
	echo "<meta http-equiv='refresh' content='0'>";
}


?>

<html>
<head>

<title><?php echo xlt('Morphine List Manager'); ?></title>
<?php Header::setupHeader(); ?>

</head>
<body class="body_top">
<div class="container">
<h1><?php echo xlt('Morphine List'); ?></h1>
<p>
<?php echo xlt('Please enter the main word of the drug only. For instance') ?>, <b> 
<?php echo xlt('Embeda') ?></b> <?php echo xlt('can be prescribed as') ?> <b>
<?php echo xlt('Embeda ER') ?></b>. 
<?php echo xlt('Only enter') ?> <b><?php echo xlt('Embeda') ?></b>.<br> 
<?php echo xlt('The system will look for all variation before and after the word Embeda') ?></p>
<p><?php echo xlt('The Days field is defaulted to 30') ?>. </p>
<h3><?php echo xlt('Add to list:'); ?></h3>
<form method="POST" action="morphine_list.php" class="form-inline"  >
  <div class="form-group">
    <input type="hidden" name="id" value="<?php if($_GET['update']) echo $updateData['id']; ?>">
    <label for="drug"><?php echo xlt('Drug Name') ?>:</label>
    <input type="text" class="form-control" name="drugname" id="drugname" 
    value="<?php if($_GET['update']) echo trim($updateData['drugname']); ?>">
  </div>
  <div class="form-group">
    <label for="drug"><?php echo xlt('Multiplier') ?>:</label>
    <input type="text" class="form-control" name="multiplier" id="multiplier" 
    value="<?php if($_GET['update']) echo $updateData['multiplier']; ?>">
  </div>
  <div class="form-group">
    <label for="drug">Days:</label>
    <input type="text" class="form-control" name="days" id="days" 
    value="<?php echo ($_GET['update'])? $updateData['days'] : '30'; ?>">
  </div>
    <button type="submit" class="btn"><?php echo xlt('Update') ?></button>
</form>

<h3><?php echo xlt('List:'); ?></h3>

<div class="table-responsive">
  <table class="table">
    <thead>
      <tr>
        <th>#
        </th>
        <th><?php echo xlt('Drug Name') ?>
        </th>
        <th><?php echo xlt('Multiplier') ?>
        </th>
        <th><?php echo xlt('Days') ?>
        </th>
        <th> 
        </th>
        <th> 
        </th>
      </tr>
    </thead>
    <tbody>
    
  <?php  

  foreach($showList as $item){
  	echo "<tr><td></td><td>". $item['drugname'] . "</td>";
  	echo "<td>". $item['multiplier'] . "</td>";
  	echo "<td>". $item['days'] . "</td>
  	      <td><a href='morphine_list.php?delete=".$item['id']."' class='btn btn-default' title='".xlt('This button will delete the entry no question or prompts')."'>".xlt('Delete')."</a></td>
  	      <td><a href='morphine_list.php?update=".$item['id']."' class='btn btn-default' title='".xlt('Click here to change the entry')."'>".xlt('Update')."</a></td></tr>";

  }
 ?>
    </tbody>
  </table>
</div>
</div> <!-- end of container div -->
</body>
</html>