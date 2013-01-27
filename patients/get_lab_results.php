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
	require_once('../interface/globals.php');
        require_once('../library/options.inc.php');

	$selects =
    "po.procedure_order_id, po.date_ordered, pc.procedure_order_seq, " .
    "pt1.procedure_type_id AS order_type_id, pc.procedure_name, " .
    "pr.procedure_report_id, pr.date_report, pr.date_collected, pr.specimen_num, " .
    "pr.report_status, pr.review_status";

  $joins =
    "JOIN procedure_order_code AS pc ON pc.procedure_order_id = po.procedure_order_id " .
    "LEFT JOIN procedure_type AS pt1 ON pt1.lab_id = po.lab_id AND pt1.procedure_code = pc.procedure_code " .
    "LEFT JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id AND " .
    "pr.procedure_order_seq = pc.procedure_order_seq";

  $orderby =
    "po.date_ordered, po.procedure_order_id, " .
    "pc.procedure_order_seq, pr.procedure_report_id";

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
                                <th><?php echo htmlspecialchars( xl('Result Name'),ENT_NOQUOTES); ?></th>
  				<th><?php echo htmlspecialchars( xl('Abnormal'),ENT_NOQUOTES); ?></th>
  				<th><?php echo htmlspecialchars( xl('Value'),ENT_NOQUOTES); ?></th>
                                <th><?php echo htmlspecialchars( xl('Range'),ENT_NOQUOTES); ?></th>
                                <th><?php echo htmlspecialchars( xl('Units'),ENT_NOQUOTES); ?></th>
                                <th><?php echo htmlspecialchars( xl('Result Status'),ENT_NOQUOTES); ?></th>
                                <th><?php echo htmlspecialchars( xl('Report Status'),ENT_NOQUOTES); ?></th>
  			</tr>
  		<?php
  		$even=false;

  		while ($row = sqlFetchArray($res)) {
        $order_type_id  = empty($row['order_type_id'      ]) ? 0 : ($row['order_type_id' ] + 0);
        $report_id      = empty($row['procedure_report_id']) ? 0 : ($row['procedure_report_id'] + 0);

        $selects = "pt2.procedure_type, pt2.procedure_code, pt2.units AS pt2_units, " .
          "pt2.range AS pt2_range, pt2.procedure_type_id AS procedure_type_id, " .
          "pt2.name AS name, pt2.description, pt2.seq AS seq, " .
          "ps.procedure_result_id, ps.result_code AS result_code, ps.result_text, ps.abnormal, ps.result, " .
          "ps.range, ps.result_status, ps.facility, ps.comments, ps.units, ps.comments";

        // procedure_type_id for order:
        $pt2cond = "pt2.parent = $order_type_id AND " .
          "(pt2.procedure_type LIKE 'res%' OR pt2.procedure_type LIKE 'rec%')";

        // pr.procedure_report_id or 0 if none:
        $pscond = "ps.procedure_report_id = $report_id";

        $joincond = "ps.result_code = pt2.procedure_code";

        // This union emulates a full outer join. The idea is to pick up all
        // result types defined for this order type, as well as any actual
        // results that do not have a matching result type.
        $query = "(SELECT $selects FROM procedure_type AS pt2 " .
          "LEFT JOIN procedure_result AS ps ON $pscond AND $joincond " .
          "WHERE $pt2cond" .
          ") UNION (" .
          "SELECT $selects FROM procedure_result AS ps " .
          "LEFT JOIN procedure_type AS pt2 ON $pt2cond AND $joincond " .
          "WHERE $pscond) " .
          "ORDER BY seq, name, procedure_type_id, result_code";

        $rres = sqlStatement($query);
        while ($rrow = sqlFetchArray($rres)) {

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
                        echo "<td>".htmlspecialchars($rrow['name'],ENT_NOQUOTES)."</td>";
                        echo "<td>".generate_display_field(array('data_type'=>'1','list_id'=>'proc_res_abnormal'),$rrow['abnormal'])."</td>";
  			echo "<td>".htmlspecialchars($row['result'],ENT_NOQUOTES)."</td>";
                        echo "<td>".htmlspecialchars($rrow['pt2_range'],ENT_NOQUOTES)."</td>";
                        echo "<td>".generate_display_field(array('data_type'=>'1','list_id'=>'proc_unit'),$rrow['pt2_units'])."</td>";
                        echo "<td>".generate_display_field(array('data_type'=>'1','list_id'=>'proc_res_status'),$rrow['result_status'])."</td>";
                        echo "<td>".generate_display_field(array('data_type'=>'1','list_id'=>'proc_rep_status'),$row['report_status'])."</td>";
  			echo "</tr>";

      }

     }

		echo "</table>";
  	}
	else
	{
		echo htmlspecialchars( xl("No Results"),ENT_NOQUOTES);
	}
?>
