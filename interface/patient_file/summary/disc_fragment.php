<?php
/*******************************************************************************\
 * Copyright (C) Visolve (vicareplus_engg@visolve.com)                          *
 *                                                                              *
 * This program is free software; you can redistribute it and/or                *
 * modify it under the terms of the GNU General Public License                  *
 * as published by the Free Software Foundation; either version 2               *
 * of the License, or (at your option) any later version.                       *
 *                                                                              *
 * This program is distributed in the hope that it will be useful,              *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of               *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                *
 * GNU General Public License for more details.                                 *
 *                                                                              *
 * You should have received a copy of the GNU General Public License            *
 * along with this program; if not, write to the Free Software                  *
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.  *
 ********************************************************************************/

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../../globals.php");
require_once("$srcdir/sql.inc");

?>
<?php
/**
 * Retrieve the recent 'N' disclosures.
 * @param $pid   -  patient id.
 * @param $limit -  certain limit up to which the disclosures are to be displyed.
 */
function getDisclosureByDate($pid,$limit)
{
	$discQry = " SELECT el.id, el.event, el.recipient, el.description, el.date, CONCAT(u.fname, ' ', u.lname) as user_fullname FROM extended_log el ".
			   " LEFT JOIN users u ON u.username = el.user ".
		       " WHERE el.patient_id=? AND el.event IN (SELECT option_id FROM list_options WHERE list_id='disclosure_type') ORDER BY el.date DESC LIMIT 0,$limit";

	$r1=sqlStatement($discQry, array($pid) );
	$result2 = array();
	for ($iter = 0;$frow = sqlFetchArray($r1);$iter++)
		$result2[$iter] = $frow;
	return $result2;
}
?>
<div id='pnotes' style='margin-top: 3px; margin-left: 10px; margin-right: 10px'><!--outer div-->
<br>
<table width='100%'>
<tr style='border-bottom:2px solid #000;' class='text'>
	<td valign='top' class='text'><b><?php  echo xlt('Type'); ?></b></td>
	<td valign='top' class='text'><b><?php  echo xlt('Provider'); ?></b></td>
	<td valign='top' class='text'><b><?php  echo xlt('Summary'); ?></b></td>
</tr>
<?php
//display all the disclosures for the day, as well as others from previous dates, up to a certain number, $N
$N=3;
//$has_variable is set to 1 if there are disclosures recorded.
$has_disclosure=0;
//retrieve all the disclosures.
$result=getDisclosureByDate($pid,$N);
if ($result != null){
	$disclosure_count = 0;//number of disclosures so far displayed
	foreach ($result as $iter)
	{
		$has_disclosure = 1;
		$app_event=$iter{"event"};
		$event=split("-",$app_event);
		$description=nl2br(text($iter{"description"}));//for line breaks.
		//listing the disclosures 
		echo "<tr style='border-bottom:1px dashed' class='text'>";
			echo "<td valign='top' class='text'>";
			if($event[1]=='healthcareoperations'){ echo "<b>";echo xlt('health care operations');echo "</b>"; } else echo "<b>".text($event[1])."</b>";
			echo "</td>";
			echo "<td>".text($iter['user_fullname'])."</td>";
			echo "<td  valign='top'class='text'>";
			echo htmlspecialchars($iter{"date"}." (".xl('Recipient').":".$iter{"recipient"}.")",ENT_NOQUOTES);
	                echo " ".$description;
			echo "</td>";
		echo "</tr>";

	}
}
?>
</table>
<?php
if ( $has_disclosure == 0 ) //If there are no disclosures recorded
{ ?>
	<span class='text'> <?php echo htmlspecialchars(xl("There are no disclosures recorded for this patient."),ENT_NOQUOTES);
	echo " "; echo htmlspecialchars(xl("To record disclosures, please click"),ENT_NOQUOTES); echo " ";echo "<a href='disclosure_full.php'>"; echo htmlspecialchars(xl("here"),ENT_NOQUOTES);echo "</a>."; 
?>
	</span> 
<?php 
} else
{
?> 
	<br />
	<span class='text'> <?php  
	echo htmlspecialchars(xl('Displaying the following number of most recent disclosures:'),ENT_NOQUOTES);?><b><?php echo " ".htmlspecialchars($N,ENT_NOQUOTES);?></b><br>
	<a href='disclosure_full.php'><?php echo htmlspecialchars(xl('Click here to view them all.'),ENT_NOQUOTES);?></a>
	</span><?php
} ?>
<br />
<br />
</div>

