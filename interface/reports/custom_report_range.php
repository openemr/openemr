<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once(dirname(__file__)."/../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/report.inc");
require_once("$srcdir/classes/Document.class.php");
require_once("$srcdir/classes/Note.class.php");
require_once("$srcdir/formatting.inc.php");

$startdate = $enddate = "";
if(empty($_POST['start']) || empty($_POST['end'])) {
    // set some default dates
    $startdate = date('Y-m-d', (time() - 30*24*60*60));
    $enddate = date('Y-m-d', time());
}
else {
    // set dates
    $startdate = $_POST['start'];
    $enddate = $_POST['end'];
}
?>
<html>

<head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<style>

@media print {
	.title {
		visibility: hidden;
	}
    .pagebreak {
        page-break-after: always;
        border: none;
        visibility: hidden;
    }

	#superbill_description {
		visibility: hidden;
	}

	#report_parameters {
		visibility: hidden;
	}
    #superbill_results {
       margin-top: -30px;
    }
}

@media screen {
	.title {
		visibility: visible;
	}
	#superbill_description {
		visibility: visible;
	}
    .pagebreak {
        width: 100%;
        border: 2px dashed black;
    }
	#report_parameters {
		visibility: visible;
	}
}
#superbill_description {
   margin: 10px;
}
#superbill_startingdate {
    margin: 10px;
}
#superbill_endingdate {
    margin: 10px;
}

#superbill_patientdata {
}
#superbill_patientdata h1 {
    font-weight: bold;
    font-size: 1.2em;
    margin: 0px;
    padding: 5px;
    width: 100%;
    background-color: #eee;
    border: 1px solid black;
}
#superbill_insurancedata {
    margin-top: 10px;
}
#superbill_insurancedata h1 {
    font-weight: bold;
    font-size: 1.2em;
    margin: 0px;
    padding: 5px;
    width: 100%;
    background-color: #eee;
    border: 1px solid black;
}
#superbill_insurancedata h2 {
    font-weight: bold;
    font-size: 1.0em;
    margin: 0px;
    padding: 0px;
    width: 100%;
    background-color: #eee;
}
#superbill_billingdata {
    margin-top: 10px;
}
#superbill_billingdata h1 {
    font-weight: bold;
    font-size: 1.2em;
    margin: 0px;
    padding: 5px;
    width: 100%;
    background-color: #eee;
    border: 1px solid black;
}
#superbill_signature {
}
#superbill_logo {
}
</style>

</head>

<body class="body_top">

<span class='title'><?php xl('Reports','e'); ?> - <?php xl('Superbill','e'); ?></span>

<div id="superbill_description" class='text'>
<?php xl('Superbills, sometimes referred to as Encounter Forms or Routing Slips, are an essential part of most medical practices.','e'); ?>
</div>

<div id="report_parameters">

<form method="post" id='theform' action="custom_report_range.php">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<table>
 <tr>
  <td width='450px'>
	<div style='float:left'>

	<table class='text'>
		<tr>
			<td class='label'>
			   <?php xl('Start Date','e'); ?>:
			</td>
			<td>
			   <input type='text' name='start' id="form_from_date" size='10' value='<?php echo $startdate ?>'
				onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
			   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php xl('Click here to choose a date','e'); ?>'>
			</td>
			<td class='label'>
			   <?php xl('End Date','e'); ?>:
			</td>
			<td>
			   <input type='text' name='end' id="form_to_date" size='10' value='<?php echo $enddate ?>'
				onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
			   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php xl('Click here to choose a date','e'); ?>'>
			</td>
		</tr>
	</table>

	</div>

  </td>
  <td align='left' valign='middle' height="100%">
	<table style='border-left:1px solid; width:100%; height:100%' >
		<tr>
			<td>
				<div style='margin-left:15px'>
					<a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
					<span>
						<?php xl('Submit','e'); ?>
					</span>
					</a>

					<?php if ($_POST['form_refresh']) { ?>
					<a href='#' class='css_button' onclick='window.print()'>
						<span>
							<?php xl('Print','e'); ?>
						</span>
					</a>
					<?php } ?>
				</div>
			</td>
		</tr>
	</table>
  </td>
 </tr>
</table>
</div> <!-- end of parameters -->

</form>

<div id="superbill_results">

<?php
if( !(empty($_POST['start']) || empty($_POST['end']))) {
    $sql = "select * from facility where billing_location = 1";
    $db = $GLOBALS['adodb']['db'];
    $results = $db->Execute($sql);
    $facility = array();
    if (!$results->EOF) {
        $facility = $results->fields;
?>
<p>
<h2><?php $facility['name']?></h2>
<?php $facility['street']?><br>
<?php $facility['city']?>, <?php $facility['state']?> <?php $facility['postal_code']?><br>

</p>
<?php
    }

    $res = sqlStatement("select * from forms where " .
                        "form_name = 'New Patient Encounter' and " .
                        "date between '$startdate' and '$enddate' " .
                        "order by date DESC");
    while($result = sqlFetchArray($res)) {
        if ($result{"form_name"} == "New Patient Encounter") {
            $newpatient[] = $result{"form_id"}.":".$result{"encounter"};
            $pids[] = $result{"pid"};
        }
    }
    $N = 6;

    function postToGet($newpatient, $pids) {
        $getstring="";
        $serialnewpatient = serialize($newpatient);
        $serialpids = serialize($pids);
        $getstring = "newpatient=".urlencode($serialnewpatient)."&pids=".urlencode($serialpids);

        return $getstring;
    }

    $iCounter = 0;
    if(empty($newpatient)){ $newpatient = array(); }
    foreach($newpatient as $patient){
        /*
        $inclookupres = sqlStatement("select distinct formdir from forms where pid='".$pids[$iCounter]."'");
        while($result = sqlFetchArray($inclookupres)) {
            include_once("{$GLOBALS['incdir']}/forms/" . $result{"formdir"} . "/report.php");
        }
        */

        print "<div id='superbill_patientdata'>";
        print "<h1>".xl('Patient Data').":</h1>";
        printRecDataOne($patient_data_array, getRecPatientData ($pids[$iCounter]), $N);
        print "</div>";

        print "<div id='superbill_insurancedata'>";
        print "<h1>".xl('Insurance Data').":</h1>";
        print "<h2>".xl('Primary').":</h2>";
        printRecDataOne($insurance_data_array, getRecInsuranceData ($pids[$iCounter],"primary"), $N);
        print "<h2>".xl('Secondary').":</h2>";
        printRecDataOne($insurance_data_array, getRecInsuranceData ($pids[$iCounter],"secondary"), $N);
        print "<h2>".xl('Tertiary').":</h2>";
        printRecDataOne($insurance_data_array, getRecInsuranceData ($pids[$iCounter],"tertiary"), $N);
        print "</div>";

        print "<div id='superbill_billingdata'>";
        print "<h1>".xl('Billing Information').":</h1>";
        if (count($patient) > 0) {
            $billings = array();
            echo "<table width='100%'>";
            echo "<tr>";
            echo "<td class='bold' width='10%'>".xl('Date')."</td>";
            echo "<td class='bold' width='20%'>".xl('Provider')."</td>";
            echo "<td class='bold' width='40%'>".xl('Code')."</td>";
            echo "<td class='bold' width='10%'>".xl('Fee')."</td></tr>\n";
            $total = 0.00;
            $copays = 0.00;
            //foreach ($patient as $be) {

                $ta = split(":",$patient);
                $billing = getPatientBillingEncounter($pids[$iCounter],$ta[1]);

                $billings[] = $billing;
                foreach ($billing as $b) {
                    // grab the date to reformat it in the output
                    $bdate = strtotime($b['date']);

                    echo "<tr>\n";
                    echo "<td class='text' style='font-size: 0.8em'>" . oeFormatShortDate(date("Y-m-d",$bdate)) . "<BR>" . date("h:i a", $bdate) . "</td>";
                    echo "<td class='text'>" . $b['provider_name'] . "</td>";
                    echo "<td class='text'>";
                    echo $b['code_type'] . ":\t" . $b['code'] . "&nbsp;". $b['modifier'] . "&nbsp;&nbsp;&nbsp;" . $b['code_text'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                    echo "</td>\n";
                    echo "<td class='text'>";
                    echo oeFormatMoney($b['fee']);
                    echo "</td>\n";
                    echo "</tr>\n";
                    $total += $b['fee'];
                    if ($b['code_type'] == "COPAY") {
                        $copays += $b['fee'];
                    }
                }
            //}
            echo "<tr><td>&nbsp;</td></tr>";
            echo "<tr><td class='bold' colspan=3 style='text-align:right'>".xl('Sub-Total')."</td><td class='text'>" . oeFormatMoney($total + abs($copays)) . "</td></tr>";
            echo "<tr><td class='bold' colspan=3 style='text-align:right'>".xl('Paid')."</td><td class='text'>" . oeFormatMoney(abs($copays)) . "</td></tr>";
            echo "<tr><td class='bold' colspan=3 style='text-align:right'>".xl('Total')."</td><td class='text'>" . oeFormatMoney($total) . "</td></tr>";
            echo "</table>";
            echo "<pre>";
            //print_r($billings);
            echo "</pre>";
        }
        echo "</div>";

        ++$iCounter;
        print "<br/><br/>".xl('Physician Signature').":  _______________________________________________";
        print "<hr class='pagebreak' />";
    }
}
    ?>
</div>

    </body>

<!-- stuff for the popup calendar -->
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>

<script language="Javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>
</html>
