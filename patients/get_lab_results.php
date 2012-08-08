<?php
 // Copyright (C) 2011 Cassian LUP <cassi.lup@gmail.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

        //SANITIZE ALL ESCAPES
        $sanitize_all_escapes=true;

        //STOP FAKE REGISTER GLOBALS
        $fake_register_globals=false;

        //continue session
        session_start();
        //

	//landing page definition -- where to go if something goes wrong
	$landingpage = "index.php?site=".$_SESSION['site_id'];
	//

        // kick out if patient not authenticated
        if ( isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite']) ) {
                $pid = $_SESSION['pid'];
        }
        else {
                session_destroy();
                header('Location: '.$landingpage.'&w');
                exit;
        }
        //

	$ignoreAuth=true;
	include_once('../interface/globals.php');

	$selects = "po.procedure_order_id, po.date_ordered, " .
  		"po.procedure_type_id AS order_type_id, pt1.name AS procedure_name, " .
  		"ptrc.name AS result_category_name, " .
  		"pt2.procedure_type AS result_type, " .
  		"pt2.procedure_type_id AS result_type_id, pt2.name AS result_name, " .
  		"pt2.units AS result_def_units, pt2.range AS result_def_range, " .
  		"pt2.description AS result_description, lo.title AS units_name, " .
  		"pr.procedure_report_id, pr.date_report, pr.date_collected, pr.specimen_num, pr.report_status, pr.review_status, " .
  		"ps.procedure_result_id, ps.abnormal, ps.result, ps.range, ps.result_status, " .
  		"ps.facility, ps.comments";
		
  	$joins = "LEFT JOIN procedure_type AS pt1 ON pt1.procedure_type_id = po.procedure_type_id ";
	$joins .=  "LEFT JOIN procedure_type AS ptrc ON ptrc.procedure_type_id = pt1.parent ";
	$joins .=  "AND ptrc.procedure_type LIKE 'grp%' " .
  		"LEFT JOIN procedure_type AS pt2 ON " .
  		"( ( ptrc.procedure_type_id IS NULL AND ( pt2.parent = po.procedure_type_id " .
  		"OR pt2.procedure_type_id = po.procedure_type_id ) ) OR ";
	$joins .= "( pt2.procedure_type_id IS NOT NULL AND pt2.parent = pt1.procedure_type_id ) " .
  		") AND ( pt2.procedure_type LIKE 'res%' OR pt2.procedure_type LIKE 'rec%' ) " .
  		"LEFT JOIN list_options AS lo ON list_id = 'proc_unit' AND option_id = pt2.units " .
  		"LEFT JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id " .
  		"LEFT JOIN procedure_result AS ps ON ps.procedure_report_id = pr.procedure_report_id " .
  		"AND ps.procedure_type_id = pt2.procedure_type_id";
  		
  	$orderby ="po.date_ordered, po.procedure_order_id, pr.procedure_report_id, " .
  		"ptrc.seq, ptrc.name, ptrc.procedure_type_id, " .
  		"pt2.seq, pt2.name, pt2.procedure_type_id";
  	
  	$where = "1 = 1";
  	  	
  	$res = sqlStatement("SELECT $selects " .
  		"FROM procedure_order AS po $joins " .
  		"WHERE po.patient_id = ? AND $where " .
  		"ORDER BY $orderby", array($pid));
  		
  	if(sqlNumRows($res)>0)
  	{
  		?>
  		<table class="class1">
  			<tr class="header">
  				<th><?php echo htmlspecialchars( xl('Order Date'),ENT_NOQUOTES); ?></th>
  				<th><?php echo htmlspecialchars( xl('Order Name'),ENT_NOQUOTES); ?></th>
  				<th><?php echo htmlspecialchars( xl('Report Status'),ENT_NOQUOTES); ?></th>
  				<th><?php echo htmlspecialchars( xl('Results Group'),ENT_NOQUOTES); ?></th>
  				<th><?php echo htmlspecialchars( xl('Abnormal'),ENT_NOQUOTES); ?></th>
  				<th><?php echo htmlspecialchars( xl('Value'),ENT_NOQUOTES); ?></th>
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
  			$date=explode('-',$row['date_ordered']);
  			echo "<tr class='".$class."'>";
  			echo "<td>".htmlspecialchars($date[1]."/".$date[2]."/".$date[0],ENT_NOQUOTES)."</td>";
  			echo "<td>".htmlspecialchars($row['procedure_name'],ENT_NOQUOTES)."</td>";
  			echo "<td>".htmlspecialchars($row['report_status'],ENT_NOQUOTES)."</td>";
  			echo "<td>".htmlspecialchars($row['result_status'],ENT_NOQUOTES)."</td>";
  			echo "<td>".htmlspecialchars($row['abnormal'],ENT_NOQUOTES)."</td>";
  			echo "<td>".htmlspecialchars($row['result'],ENT_NOQUOTES)."</td>";
  			echo "</tr>";
  		}
		echo "</table>";
  	}
	else
	{
		echo htmlspecialchars( xl("No Results"),ENT_NOQUOTES);
	}
?>
