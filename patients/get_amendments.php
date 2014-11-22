<?php
/**
 * Patient Portal Amendments
 *
 * Copyright (C) 2014 Ensoftek
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Hema Bandaru <hemab@drcloudemr.com>
 * @link    http://www.open-emr.org
 */
require_once("verify_session.php");

$query = "SELECT a.*,lo.title AS AmendmentBy,lo1.title AS AmendmentStatus FROM amendments a 
	INNER JOIN list_options lo ON a.amendment_by = lo.option_id AND lo.list_id='amendment_from'
	LEFT JOIN list_options lo1 ON a.amendment_status = lo1.option_id AND lo1.list_id='amendment_status' 
	WHERE a.pid = ? ORDER BY amendment_date DESC";
$res = sqlStatement($query, array($pid) );
if ( sqlNumRows($res) > 0 ) { ?>

	<table class="class1">
		<tr class="header">
			<th><?php echo xlt('Date'); ?></th>
			<th><?php echo xlt('Requested By'); ?></th>
			<th><?php echo xlt('Description'); ?></th>
			<th><?php echo xlt('Status'); ?></th>
		</tr>
	<?php
  		$even = false;
  		while ($row = sqlFetchArray($res)) {
  			if ( $even ) {
  				$class = "class1_even";
  				$even = false;
  			} else {
  				$class="class1_odd";
  				$even=true;
			}
			echo "<tr class='".$class."'>";
			echo "<td>".text($row['amendment_date'])."</td>";
			echo "<td>".text($row['AmendmentBy'])."</td>";
			echo "<td>".text($row['amendment_desc'])."</td>";
			echo "<td>".text($row['AmendmentStatus'])."</td>";
			echo "</tr>";
  		}
		echo "</table>";
  	}
	else
	{
		echo xlt("No Results");
	}
?>
