<?php
 // Copyright (C) 2011 Cassian LUP <cassi.lup@gmail.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

        require_once("verify_session.php");

	$sql = "SELECT * FROM lists WHERE pid = ? AND type = 'medication' ORDER BY begdate";
	
	$res = sqlStatement($sql, array($pid) );

	if(sqlNumRows($res)>0)
  	{
  		?>
  		<table class="class1">
  			<tr class="header">
  				<th><?php echo htmlspecialchars( xl('Drug'),ENT_NOQUOTES); ?></th>
  				<th><?php echo htmlspecialchars( xl('Start Date'),ENT_NOQUOTES); ?></th>
  				<th><?php echo htmlspecialchars( xl('End Date'),ENT_NOQUOTES); ?></th>
  				<th><?php echo htmlspecialchars( xl('Referrer'),ENT_NOQUOTES); ?></th>
  			</tr>
  		<?php
  		$even=false;
  		while ($row = sqlFetchArray($res)) {
  			if ($even) {
  				$class="class1_even";
  				$even=false;
  			} else {
  				$class="class1_odd";
  				$even=true;
  			}
  			echo "<tr class='".htmlspecialchars($class,ENT_QUOTES)."'>";
  			echo "<td>".htmlspecialchars($row['title'],ENT_NOQUOTES)."</td>";
  			echo "<td>".htmlspecialchars($row['begdate'],ENT_NOQUOTES)."</td>";
  			echo "<td>".htmlspecialchars($row['enddate'],ENT_NOQUOTES)."</td>";
  			echo "<td>".htmlspecialchars($row['referredby'],ENT_NOQUOTES)."</td>";
  			echo "</tr>";
  		}
		echo "</table>";
  	}
	else
	{
		echo htmlspecialchars( xl("No Results"),ENT_NOQUOTES);
	}
?>
