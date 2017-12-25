<?php
/**
 * Morphine Calculator
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Some initial api-inputs

require_once("../../globals.php");
require_once("$srcdir/me_calculator.inc.php");
?>
<br>

<div  id='moreq' style=' display: inline-block; margin-top: 3px; margin-left: 10px; margin-right: 10px'>

<?php

$pid = $GLOBALS['pid'];
$res = fetchMEs($pid);
$enc = $GLOBALS['encounter'];
 
$date = date("Y-m-d H:m:s");

    if(empty($enc)){
        echo  xlt('Please select encounter to record ME stats') ."</br>";
    }else{
            if(!empty($res)){  
                
                echo "<b>".xlt('Last').":</b> " . $res['last'] . "</BR>" ;
                //echo "<b>Note: </b>" . $res['notes'] . "</BR></BR>";
            }else{
                echo xlt('No Previous entries recorded') . "</br></br>";
            }
    }

     $drugs = getMeds();
?>
<table>

<?php
     
     $total = array();
     while ($d = sqlFetchArray($drugs)) 
	 {
      $med = $d['drug'];
      $size = $d['size'];
      $quantity = $d['quantity'];
      $display = getMeCalculations($med, $size, $quantity );
      echo "<tr><td align='right'>".$display[0]."</td><td>".$display[1]."</td></tr>";
      $total[] = $display[1];
	  
     }
	 $i = count($total);
	 if(array_sum($total) != 0) 
	 {
      echo "<tr><td align='right'><b>".xlt('Total ME')." = </b>"."</td><td>". array_sum($total)."</td></tr>"; 
     }else{
		 print "<tr><td align='right'><b>". xlt('No Matching Meds found'). "</b></td><td></td></tr>";
	 }
     $sum =  array_sum($total);      
?>

</table>

</div>
<div id='moreq_graph' style='display: inline-block;'>
<?php print xlt("Nothing to graph") ?>
</div>
<br>
<?php 
//save total ME's
$savedDate = explode(" ", $res['datetime']);
$date = date("Y-m-d");
if($res['encounter'] != $GLOBALS['encounter'] && $savedDate[0] != $date && $sum != 0){

    $saveRes = saveMEinfo($pid, $enc, $sum);
	echo xl($saveRes);
}

$plots = getGraphData($pid);
if($sum > 80){
?>
<script language='JavaScript'>

  var g = new Dygraph(
    // containing div
    document.getElementById("moreq_graph"),
    // CSV or path to a CSV file.
    "Date,ME Level\n" <?php while($plot = sqlFetchArray($plots)){$date = explode(" ", $plot['datetime']); print ' + '; print "\"".$date[0].",".$plot['last']; print '\n'; print "\""; }?>	
	                    

  );
  </script>
<?php } ?>