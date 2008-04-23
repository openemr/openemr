<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<?php
include_once("../../../interface/globals.php");
?>
<html>  
<head>
    <title>Overzicht zorgverzekering</title>

<style type="text/css">
body { color: #126E09; background-color: #DCFFBC; }
.tblins { margin: 10px; }
.tblins tr { background-color: #BFDDA3; }
.tblins td { padding: 3px; background-color: #E8FFC0; }
</style>

<style type="text/css" media="print">
body { color: #000; background-color: #fff; }
.tblins { margin: 10px; border:1px solid #000 }
.tblins tr { border:1px solid #000 }
.tblins td { padding: 3px; border:1px solid #000}
</style>

<script type="text/javascript">

function printpage() {
    window.print();  
}
</script>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>


<body>

<?php
$result = getPatientData($pid);
$dob    = $result['DOB'];

echo 'Overzicht zorgverzekering van: <strong>' .dutch_name($_SESSION['pid']). '</strong> (' .$_SESSION['pid'];
echo ' DOB: ' .$dob. ' )';
echo '&nbsp;&nbsp;<a href="javascript:printpage()">Print</a>';

$ins = get_insurers_nl($pid, 0);

if ( !$ins ) { echo '<br />No insurance records.'; exit(); }

echo '<br /><br /><table class="tblins"><tr><th>Zorgverzekeraar</th><th>Polisnummer</th><th>Startdatum</th></tr>';
foreach ( $ins as $in ) {
    $s = "<tr><td>{$in['name']}</td>";
    $s .= "<td>{$in['pin_policy']}</td>";
    $s .= "<td>{$in['pin_date']}</td>";
    $s .= '</tr>';
    echo $s;
}
echo '</table>';


?>

</body>
</html>