<?php
include_once(dirname(__file__)."/../globals.php");

include_once("$srcdir/forms.inc");
include_once("$srcdir/billing.inc");
include_once("$srcdir/pnotes.inc");
include_once("$srcdir/patient.inc");
include_once("$srcdir/report.inc");
include_once("$srcdir/classes/Document.class.php");
include_once("$srcdir/classes/Note.class.php");

$startdate = $enddate = "";
if(empty($_POST['start']) || empty($_POST['end'])) {
    // set some default dates
    $startdate = date('Ymd', (time() - 30*24*60*60));
    $enddate = date('Ymd', time());
}

?>
<html>

<head>
<?php html_header_show();?>
    
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<style>
@media print {
    .pagebreak {
        page-break-after: always;
        border: none;
        visibility: hidden;
    }
}

@media screen {
    .pagebreak {
        width: 100%;
        border: 2px dashed black;
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
    
<?php if(empty($_POST['start']) || empty($_POST['end'])) { ?>
<form method="post" action="custom_report_range.php">
<div id="superbill_description">
<?php xl('Superbills, sometimes referred to as Encounter Forms or Routing Slips, are an essential part of most medical practices.','e'); ?>
</div>
<div id="superbill_startingdate">
<?php xl('Start Date','e'); ?>: <input type="text" name="start" id="start" value="<?php echo $startdate; ?>" size="10"/>
<img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'>
(<?php xl('format','e'); ?>=YYYYMMDD)
</div>
<div id="superbill_endingdate">
<?php xl('End Date','e'); ?>: <input type="text" name="end" id="end" value="<?php echo $enddate; ?>" size="10"/>
<img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'>
(<?php xl('format','e'); ?>=YYYYMMDD)
</div>
<input type="submit" value=<?php xl('Submit','e','\'','\''); ?> />
</form>

<?php
} else {        
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
                        "date between '$start' and '$end' " .
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
                    echo "<td class='text' style='font-size: 0.8em'>" . date("Y-m-d",$bdate)."<BR>".date("h:i a", $bdate) . "</td>";
                    echo "<td class='text'>" . $b['provider_name'] . "</td>";
                    echo "<td class='text'>";
                    echo $b['code_type'] . ":\t" . $b['code'] . "&nbsp;". $b['modifier'] . "&nbsp;&nbsp;&nbsp;" . $b['code_text'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                    echo "</td>\n";
                    echo "<td class='text'>";
                    echo $b['fee'];
                    echo "</td>\n";
                    echo "</tr>\n";
                    $total += $b['fee'];
                    if ($b['code_type'] == "COPAY") {
                        $copays += $b['fee'];
                    }
                            
                }
            //} 
            echo "<tr><td>&nbsp;</td></tr>";
            echo "<tr><td class='bold' colspan=3 style='text-align:right'>".xl('Sub-Total')."</td><td class='text'>" . sprintf("%0.2f",$total + abs($copays)) . "</td></tr>";
            echo "<tr><td class='bold' colspan=3 style='text-align:right'>".xl('Paid')."</td><td class='text'>" . sprintf("%0.2f",abs($copays)) . "</td></tr>";
            echo "<tr><td class='bold' colspan=3 style='text-align:right'>".xl('Total')."</td><td class='text'>" . sprintf("%0.2f",$total) . "</td></tr>";
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
    
    
    
    
    
    
    
    
    </body>

<?php if(empty($_POST['start']) || empty($_POST['end'])) : ?>
<!-- stuff for the popup calendar -->
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="Javascript">
 Calendar.setup({inputField:"start", ifFormat:"%Y%m%d", button:"img_from_date"});
 Calendar.setup({inputField:"end", ifFormat:"%Y%m%d", button:"img_to_date"});
</script>
<?php endif; ?>
</html>
