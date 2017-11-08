<?php

/** Copyright (C) 2016 Sherwin Gaddis <sherwingaddis@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author Sherwin Gaddis <sherwingaddis@gmail.com>
 * @link    http://www.open-emr.org
 */



require_once("../../globals.php");

session_start();

if(isset($_REQUEST['submit'])){

    $res = sqlStatement("SELECT presc_id,provider_id,list_id,list_name FROM prescription_fav_list  WHERE list_name like ?",array($_REQUEST['submit']));
    while ($row = sqlFetchArray($res)) {
        //$rows[$row['line_id']] = $row;

        $res2 = sqlStatement("SELECT patient_id,provider_id,encounter,date_added,active,drug,drug_id,size,form,quantity,dosage,route,substitute,note,`interval`,refills,per_refill FROM prescriptions  WHERE id = ? ",array($row['presc_id']));


        $row2 = sqlFetchArray($res2);


        $res3 = sqlStatement("SELECT id,drug FROM prescriptions  WHERE patient_id = ? AND drug LIKE ? LIMIT 1",array($pid,$row2['drug']));


        if($row3 = sqlFetchArray($res3) ){
            sqlQuery("UPDATE prescriptions SET provider_id = ?, date_modified = ?,active = 1 WHERE patient_id = ? AND id = ? ", array($_SESSION['authUserID'],date('Y-m-d'),$pid,$row3['id']));

        }else{


        sqlInsert("INSERT INTO prescriptions(".
            "patient_id,provider_id,encounter,date_added,active,drug,drug_id,size,form,quantity,dosage,route,substitute,note,`interval`,refills,per_refill".
            ") VALUES (".
            "'".add_escape_custom($pid)."',".
            "'".add_escape_custom($_SESSION['authUserID'])."',".
            "'".add_escape_custom($_SESSION['encounter'])."',".
            "'".add_escape_custom(date("Y-m-d"))."',".
            "'".add_escape_custom(1)."',".
            "'".add_escape_custom($row2['drug'])."',".
            "'".add_escape_custom(0)."',".
            "'".add_escape_custom($row2['size'])."',".
            "'".add_escape_custom($row2['form'])."',".
            "'".add_escape_custom($row2['quantity'])."',".
            "'".add_escape_custom($row2['dosage'])."',".
            "'".add_escape_custom($row2['route'])."',".
            "'".add_escape_custom($row2['substitute'])."',".
            "'".add_escape_custom($row2['note'])."',".
            "'".add_escape_custom($row2['interval'])."',".
            "'".add_escape_custom($row2['refills'])."',".
            "'".add_escape_custom($row2['per_refill'])."')");
        }



    }



}
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">

<span class="title"><?php echo xlt('Prescriptions'); ?></span>
<table>
<tr height="20px">
<td>
    <a href="<?php echo $GLOBALS['webroot']?>/controller.php?prescription&list&id=<?php echo attr($pid); ?>"  target='RxRight' class="css_button" onclick="top.restoreSession()">
    <span><?php echo xlt('List');?></span></a>
    <a href="<?php echo $GLOBALS['webroot']?>/controller.php?prescription&edit&id=&pid=<?php echo attr($pid); ?>"  target='RxRight' class="css_button" onclick="top.restoreSession()">
    <span><?php echo xlt('Add');?></span></a>
</td>
</tr>
<tr>
<td>
<?php if ($GLOBALS['rx_show_drug-drug']) { ?>
    <a href="<?php echo $GLOBALS['webroot']?>/interface/weno/drug-drug.php"  target='RxRight' class="css_button" onclick="top.restoreSession()">
    <span><?php echo xlt('Drug-Drug');?></span></a>
<?php } ?>
</td>
</tr>
</table>
    
<div>
    <form id="add_to_patient" >
    <table border="0" cellpadding="0" width="100%">
        <tbody>
    <?php
    $res = sqlStatement("SELECT presc_id,provider_id,list_id,list_name FROM prescription_fav_list  WHERE provider_id like ? GROUP BY list_name",array($_SESSION['authUserID']));
    while ($row = sqlFetchArray($res)) {
        //$rows[$row['line_id']] = $row;

        echo "<tr>";
        echo "<td> ".($row['list_name'])." </td>";
        echo "<td> <button name='submit' onclick='top.restoreSession();' value='".$row['list_name']."'> add to patient</button> </td>";
        echo "</tr>";

    }

    ?> </form>
        </tbody></table>
</div>
    
</body>
<?php
if(isset($_REQUEST['submit'])){
echo "<script type=\"text/javascript\">
document.getElementById(\"alist\").click();
</script>";
}
?>
</html>
