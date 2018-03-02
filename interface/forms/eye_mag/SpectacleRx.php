<?php

/**
 * forms/eye_mag/SpectacleRx.php
 *
 * Functions for printing a glasses prescription
 *
 * Copyright (C) 2016 Raymond Magauran <magauran@MedFetch.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Ray Magauran <magauran@MedFetch.com>
 * @link http://www.open-emr.org
 */




require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/lists.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/report.inc");

use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

$form_name = "Eye Form";
$form_folder = "eye_mag";
require_once("php/".$form_folder."_functions.php");

$query = "SELECT * FROM patient_data where pid=?";
$pat_data =  sqlQuery($query, array($data['pid']));

$providerID  =  getProviderIdOfEncounter($encounter);
$providerNAME = getProviderName($providerID);
$query = "SELECT * FROM users where id = ?";
$prov_data =  sqlQuery($query, array($providerID));

$practice_data = $facilityService->getPrimaryBusinessEntity();

if (!$_REQUEST['pid']) {
    $_REQUEST['pid'] = $_REQUEST['id'];
}

$query = "SELECT * FROM patient_data where pid=?";
$pat_data =  sqlQuery($query, array($_REQUEST['pid']));

if ($_REQUEST['mode'] =="update") {  //store any changed fields in dispense table
    $table_name = "form_eye_mag_dispense";
    $query = "show columns from ".$table_name;
    $dispense_fields = sqlStatement($query);
    $fields = array();

    if (sqlNumRows($dispense_fields) > 0) {
        while ($row = sqlFetchArray($dispense_fields)) {
      //exclude critical columns/fields, define below as needed
            if ($row['Field'] == 'id' ||
            $row['Field'] == 'pid' ||
            $row['Field'] == 'user' ||
            $row['Field'] == 'groupname' ||
            $row['Field'] == 'authorized' ||
            $row['Field'] == 'activity'
            ) {
                continue;
            }

            if (isset($_POST[$row['Field']])) {
                $fields[$row['Field']] = $_POST[$row['Field']];
            }
        }

        $fields['RXTYPE']=$RXTYPE;

        $insert_this_id = formUpdate($table_name, $fields, $_POST['id'], $_SESSION['userauthorized']);
    }

    exit;
} elseif ($_REQUEST['mode'] =="remove") {
    $query ="DELETE FROM form_eye_mag_dispense where id=?";
    sqlStatement($query, array($_REQUEST['delete_id']));
    echo xlt('Prescription successfully removed.');
    exit;
} elseif ($_REQUEST['RXTYPE']) {  //store any changed fields
    $query ="UPDATE form_eye_mag_dispense set RXTYPE=? where id=?";
    sqlStatement($query, array($_REQUEST['RXTYPE'],$_REQUEST['id']));
    exit;
}


formHeader("OpenEMR Eye: ".$prov_data[facility]);

if ($_REQUEST['REFTYPE']) {
    $REFTYPE = $_REQUEST['REFTYPE'];
    if ($REFTYPE == "AR") {
        $RXTYPE = "Bifocal";
    }

    if ($REFTYPE == "MR") {
        $RXTYPE = "Bifocal";
    }

    if ($REFTYPE == "CTL") {
        $RXTYPE = "Bifocal";
    }

    $id = $_REQUEST['id'];
    $table_name = "form_eye_mag";
    if (!$_REQUEST['encounter']) {
        $encounter = $_SESSION['encounter'];
    } else {
        $encounter = $_REQUEST['encounter'];
    }

    $query = "SELECT * FROM form_eye_mag JOIN forms on forms.form_id = form_eye_mag.id
    where form_eye_mag.pid =? and forms.encounter=? and forms.deleted !='1'";

    $data =  sqlQuery($query, array($id,$encounter));

    if ($REFTYPE =="W") {
        //we have rx_number 1-5 to process...
        $query = "select * from form_eye_mag_wearing where ENCOUNTER=? and FORM_ID=? and PID=? and RX_NUMBER=?";
        $wear = sqlStatement($query, array($encounter,$_REQUEST['form_id'],$_REQUEST['pid'],$_REQUEST['rx_number']));
        $wearing = sqlFetchArray($wear);
        $ODSPH = $wearing['ODSPH'];
        $ODAXIS = $wearing['ODAXIS'];
        $ODCYL = $wearing['ODCYL'];
        $OSSPH = $wearing['OSSPH'];
        $OSCYL = $wearing['OSCYL'];
        $OSAXIS = $wearing['OSAXIS'];
        $COMMENTS = $wearing['COMMENTS'];
        $ODMIDADD = $wearing['ODMIDADD'];
        $ODADD2 = $wearing['ODADD'];
        $OSMIDADD = $wearing['OSMIDADD'];
        $OSADD2 = $wearing['OSADD'];
        @extract($wearing);
        if ($wearing['RX_TYPE']=='0') {
            $Single="checked='checked'";
            $RXTYPE="Single";
        } elseif ($wearing['RX_TYPE']=='1') {
            $Bifocal ="checked='checked'";
            $RXTYPE="Bifocal";
        } elseif ($wearing['RX_TYPE']=='2') {
            $Trifocal ="checked='checked'";
            $RXTYPE="Trifocal";
        } elseif ($wearing['RX_TYPE']=='3') {
            $Progressive ="checked='checked'";
            $RXTYPE="Progressive";
        }

        //do LT and Lens materials
    } elseif ($REFTYPE =="AR") {
            $ODSPH = $data['ARODSPH'];
            $ODAXIS = $data['ARODAXIS'];
            $ODCYL = $data['ARODCYL'];
            $ODPRISM = $data['ARODPRISM'];
            $OSSPH = $data['AROSSPH'];
            $OSCYL = $data['AROSCYL'];
            $OSAXIS = $data['AROSAXIS'];
            $OSPRISM = $data['AROSPRISM'];
            $COMMENTS = $data['CRCOMMENTS'];
            $ODADD2 = $data['ARODADD'];
            $OSADD2 = $data['AROSADD'];
            $Bifocal ="checked='checked'";
    } elseif ($REFTYPE =="MR") {
            $ODSPH = $data['MRODSPH'];
            $ODAXIS = $data['MRODAXIS'];
            $ODCYL = $data['MRODCYL'];
            $ODPRISM = $data['MRODPRISM'];
            $OSSPH = $data['MROSSPH'];
            $OSCYL = $data['MROSCYL'];
            $OSAXIS = $data['MROSAXIS'];
            $OSPRISM = $data['MROSPRISM'];
            $COMMENTS = $data['CR_COMMENTS'];
            $ODADD2 = $data['MRODADD'];
            $OSADD2 = $data['MROSADD'];
            $Bifocal ="checked='checked'";
    } elseif ($REFTYPE =="CR") {
            $ODSPH = $data['CRODSPH'];
            $ODAXIS = $data['CRODAXIS'];
            $ODCYL = $data['CRODCYL'];
            $ODPRISM = $data['CRODPRISM'];
            $OSSPH = $data['CROSSPH'];
            $OSCYL = $data['CROSCYL'];
            $OSAXIS = $data['CROSAXIS'];
            $OSPRISM = $data['CROSPRISM'];
            $COMMENTS = $data['CRCOMMENTS'];
    } elseif ($REFTYPE=="CTL") {
            $ODSPH = $data['CTLODSPH'];
            $ODAXIS = $data['CTLODAXIS'];
            $ODCYL = $data['CTLODCYL'];
            $ODPRISM = $data['CTLODPRISM'];

            $OSSPH = $data['CTLOSSPH'];
            $OSCYL = $data['CTLOSCYL'];
            $OSAXIS = $data['CTLOSAXIS'];
            $OSPRISM = $data['CTLOSPRISM'];

            $ODBC = $data['CTLODBC'];
            $ODDIAM = $data['CTLODDIAM'];
            $ODADD = $data['CTLODADD'];
            $ODVA = $data['CTLODVA'];

            $OSBC = $data['CTLOSBC'];
            $OSDIAM = $data['CTLOSDIAM'];
            $OSADD = $data['CTLOSADD'];
            $OSVA = $data['CTLOSVA'];

            $COMMENTS = $data['CTL_COMMENTS'];

            $CTLMANUFACTUREROD  = getListItemTitle('CTLManufacturer', $data['CTLMANUFACTUREROD']);
            $CTLMANUFACTUREROS  = getListItemTitle('CTLManufacturer', $data['CTLMANUFACTUREROS']);
            $CTLSUPPLIEROD      = getListItemTitle('CTLManufacturer', $data['CTLSUPPLIEROD']);
            $CTLSUPPLIEROS      = getListItemTitle('CTLManufacturer', $data['CTLSUPPLIEROS']);
            $CTLBRANDOD         = getListItemTitle('CTLManufacturer', $data['CTLBRANDOD']);
            $CTLBRANDOS         = getListItemTitle('CTLManufacturer', $data['CTLBRANDOS']);
    }

    //Since we selected the Print Icon, we must be dispensing this - add to dispensed table now
    $table_name = "form_eye_mag_dispense";
    $query = "show columns from ".$table_name;
    $dispense_fields = sqlStatement($query);
    $fields = array();

    if (sqlNumRows($dispense_fields) > 0) {
        while ($row = sqlFetchArray($dispense_fields)) {
      //exclude critical columns/fields, define below as needed
            if ($row['Field'] == 'id' ||
            $row['Field'] == 'pid' ||
            $row['Field'] == 'user' ||
            $row['Field'] == 'groupname' ||
            $row['Field'] == 'authorized' ||
            $row['Field'] == 'activity' ||
            $row['Field'] == 'RXTYPE' ||
            $row['Field'] == 'REFDATE'
            ) {
                continue;
            }

            if (isset(${$row['Field']})) {
                $fields[$row['Field']] = $$row['Field'];
            }
        }

        $fields['RXTYPE']=$RXTYPE;
        $fields['REFDATE'] = $data['date'];

        $insert_this_id = formSubmit($table_name, $fields, $form_id, $_SESSION['userauthorized']);
    }
}

if ($_REQUEST['dispensed']) {
    $query = "SELECT * from form_eye_mag_dispense where pid =? ORDER BY date DESC";
    $dispensed = sqlStatement($query, array($_REQUEST['pid']));
    ?><html>
        <title><?php echo xlt('Rx Dispensed History'); ?></title>
        <head>
            <script src="<?php echo $GLOBALS['assets_static_relative'] ?>/jquery-min-1-10-2/index.js"></script>
            <script src="<?php echo $GLOBALS['assets_static_relative'] ?>/bootstrap-3-3-4/dist/js/bootstrap.min.js"></script>
            <script src="<?php echo $GLOBALS['assets_static_relative'] ?>/qtip2-2-2-1/jquery.qtip.min.js"></script>
            <script type="text/javascript" src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js"></script>

            <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/bootstrap-3-3-4/dist/css/bootstrap.min.css">
            <link rel="stylesheet" href="<?php echo $GLOBALS['css_header']; ?>" type="text/css">
            <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/jquery-ui-1-10-4/themes/ui-lightness/jquery-ui.min.css">
            <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/pure-0-5-0/pure-min.css">
            <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/qtip2-2-2-1/jquery.qtip.min.css" />
            <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/font-awesome-4-6-3/css/font-awesome.min.css">
            <link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/css/style.css" type="text/css">

            <style>
                .refraction {
                    top:1in;
                    float:left;
                    min-height:1.0in;
                    border: 1.00pt solid #000000;
                    padding: 5;
                    border-radius: 8px;
                    margin: 5 auto;
                }
                .refraction td {
                    text-align:center;
                    font-size:8pt;
                    padding:2;
                    text-align: text-middle;
                    text-decoration: none;
                }
                table {
                    font-size: 1.0em;
                    padding: 2px;
                    color: black;
                    vertical-align: text-top;
                }

                input[type=text] {
                    text-align: right;
                    width:50px;
                    background-color: white;
                }
                .refraction  b{
                    text-decoration:bold;
                }
                .refraction td.right {
                    text-align: right;
                    text-decoration: none;
                    vertical-align: text-top;
                }
                .refraction td.left {
                    text-align: left;
                    vertical-align: top;
                }

                .right {
                    text-align:right;
                    vertical-align: text-top;
                }
                .left {
                    text-align:left;
                    vertical-align: top;
                }
                .title {
                    font-size: 0.9em;
                    font-weight:normal;
                }
            </style>
            <script language="JavaScript">
                <?php       require_once("$srcdir/restoreSession.php");  ?>

                function delete_me(delete_id){
                    top.restoreSession();
                    var url = "../../forms/eye_mag/SpectacleRx.php";
                    $.ajax({
                       type     : 'POST',
                       url      : url,
                       data     : {
                            mode        : 'remove',
                            delete_id   : delete_id,
                            dispensed   : '1'
                        } // our data object
                       }).done(function(o) {
                        $('#RXID_'+delete_id).hide();
                        alert(o);
                    });
                }

            </script>
        </head>
            <?php echo report_header($pid, "web"); ?>
            <div class="row">
                <div class="col-sm-8 offset-sm-2" style="margin:5;text-align:center;">
                    <table>
                        <tr>
                            <td colspan="2"><h4 class="underline"><?php echo xlt('Rx History'); ?></h4></td>
                        </tr>
                        <?php
                        if (sqlNumRows($dispensed) == 0) {
                            echo "<tr><td colspan='2' style='font-size:1.2em;text-align:middle;padding:25px;'>".xlt('There are no Glasses or Contact Lens Presciptions on file for this patient')."</td></tr>";
                        }
                        ?>
                    </table>
                    <?php
                    while ($row = sqlFetchArray($dispensed)) {
                        $i++;
                        $Single ='';
                        $Bifocal='';
                        $Trifocal='';
                        $Progressive='';
                        if ($row['RXTYPE'] == "Single") {
                            $Single = "checked='checked'";
                        }

                        if ($row['RXTYPE'] == "Bifocal") {
                            $Bifocal = "checked='checked'";
                        }

                        if ($row['RXTYPE'] == "Trifocal") {
                            $Trifocal = "checked='checked'";
                        }

                        if ($row['RXTYPE'] == "Progressive") {
                            $Progressive = "checked='checked'";
                        }

                        $row['REFDATE'] = oeFormatShortDate($row['REFDATE']);
                        $row['date'] = oeFormatShortDate(date('Y-m-d', strtotime($row['date'])));
                        if ($REFTYPE == "CTL") {
                            $expir = date("Y-m-d", strtotime("+1 years", strtotime($row['REFDATE_OK'])));
                        } else {
                            $expir = date("Y-m-d", strtotime("+6 months", strtotime($row['REFDATE_OK'])));
                        }
                        $expir_date = oeFormatShortDate($expir);

                        ?>
                        <div id="RXID_<?php echo attr($row['id']); ?>" style="position:relative;text-align:center;width:80%;margin: 10 auto;">
                            <i class="pull-right fa fa-close"
                                onclick="delete_me('<?php echo attr(addslashes($row['id'])); ?>');"
                                title="<?php echo xla('Remove this Prescription from the list of RXs dispensed'); ?>"></i>
                            <table style="margin:2px auto;">
                                <tr>
                                    <td class="right bold" style="width:250px;"><b><?php echo xlt('RX Date'); ?>: </b></td>
                                    <td>&nbsp;&nbsp;<?php echo text($row['date']); ?></td>
                                </tr>
                                <tr>
                                    <td class="right bold"><b><?php echo xlt('Visit Date'); ?>: </b></td>
                                    <td>&nbsp;&nbsp;<?php echo text($row['REFDATE']); ?></td>
                                </tr>
                                <tr>
                                    <td class="right bold"><b><?php echo xlt('Expiration Date'); ?>: </b></td>
                                    <td>&nbsp;&nbsp;<?php
                                            echo text($expir_date);
                                        ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="right bold"><?php echo xlt('Refraction Method'); ?>: </td>
                                    <td>&nbsp;&nbsp;<?php
                                    if ($row['REFTYPE'] == "W") {
                                        echo xlt('Duplicate Rx -- unchanged from current Rx{{The refraction did not change, New Rx=old Rx}}');
                                    } else if ($row['REFTYPE'] == "CR") {
                                        echo xlt('Cycloplegic (Wet) Refraction');
                                    } else if ($row['REFTYPE'] == "MR") {
                                        echo xlt('Manifest (Dry) Refraction');
                                    } else if ($row['REFTYPE'] == "AR") {
                                        echo xlt('Auto-Refraction');
                                    } else if ($row['REFTYPE'] == "CTL") {
                                        echo xlt('Contact Lens');
                                    }  ?>
                                        <input type="hidden" name="REFTYPE" value="<?php echo attr($row['REFTYPE']); ?>" />
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"> <?php
                                    if ($row['REFTYPE'] != "CTL") { ?>
                                            <table id="SpectacleRx" name="SpectacleRx" class="refraction" style="top:0px;">
                                                <tr style="font-style:bold;">
                                                    <td></td>
                                                    <td></td>
                                                    <td class="center"><?php echo xlt('Sph{{Sphere}}'); ?></td>
                                                    <td class="center"><?php echo xlt('Cyl{{Cylinder}}'); ?></td>
                                                    <td class="center"><?php echo xlt('Axis{{Axis in a glasses prescription}}'); ?></td>
                                                    <td rowspan="5" class="right bold underline" colspan="2" style="min-width:200px;font-weight:bold;">
                                                        <?php echo xlt('Rx Type'); ?><br /><br />
                                                        <?php echo xlt('Single'); ?>
                                                            <input type="radio" disabled <?php echo text($Single); ?>><br />
                                                        <?php echo xlt('Bifocal'); ?>
                                                            <input type="radio" disabled <?php echo text($Bifocal); ?>><br />
                                                        <?php echo xlt('Trifocal'); ?>
                                                            <input type="radio" disabled <?php echo text($Trifocal); ?>><br />
                                                        <?php echo xlt('Prog.{{Progressive lenses}}'); ?>
                                                            <input type="radio" disabled <?php echo text($Progressive); ?>><br />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td rowspan="2" style="text-align:right;font-weight:bold;"><?php echo xlt('Distance'); ?></td>
                                                    <td><b><?php echo xlt('OD{{right eye}}'); ?></b></td>
                                                    <td><?php echo text($row['ODSPH']); ?></td>
                                                    <td><?php echo text($row['ODCYL']); ?></td>
                                                    <td><?php echo text($row['ODAXIS']); ?></td>
                                                    <td><?php echo text($row['ODPRISM']); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><b><?php echo xlt('OS{{left eye}}'); ?></b></td>
                                                    <td><?php echo text($row['OSSPH']); ?></td>
                                                    <td><?php echo text($row['OSCYL']); ?></td>
                                                    <td><?php echo text($row['OSAXIS']); ?></td>
                                                    <td><?php echo text($row['OSPRISM']); ?></td>
                                                </tr>
                                                <tr class="NEAR">
                                                    <td rowspan=2 nowrap><span style="text-decoration:none;"><?php echo xlt('ADD'); ?>:<br /><?php echo xlt("Mid{{Middle segment in a trifocal glasses prescription}}"); ?>/<?php echo xlt("Near"); ?></span></td>
                                                    <td><b><?php echo xlt('OD{{right eye}}'); ?></b></td>
                                                    <td class="WMid"><?php echo text($row['ODMIDADD']); ?></td>
                                                    <td class="WAdd2"><?php echo text($row['ODADD2']); ?></td>
                                                </tr>
                                                <tr class="NEAR">
                                                    <td><b><?php echo xlt('OS{{left eye}}'); ?></b></td>
                                                    <td class="WMid"><?php echo text($row['OSMIDADD']); ?></td>
                                                    <td class="WAdd2"><?php echo text($row['OSADD2']); ?></td>
                                                </tr>
                                                <tr style="">
                                                    <td colspan="2" class="up" style="text-align:right;vertical-align:top;top:0px;font-weight:bold;"><?php echo xlt('Comments'); ?>:
                                                    </td>
                                                    <td colspan="4" class="up" style="text-align:left;vertical-align:middle;top:0px;">
                                                        <textarea style="width:100%;height:2.1em;" id="COMMENTS" disabled name="COMMENTS"><?php echo text($row['COMMENTS']); ?></textarea>
                                                    </td>
                                                </tr>
                                            </table>
                                            <?php
                                    } else { ?>
                                        <table id="CTLRx" name="CTLRx" class="refraction">
                                            <tr>
                                                <td colspan="4" class="bold underline left"><?php echo xlt('Right Lens'); ?></u></td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="left"><?php echo text($row['CTLBRANDOD']); ?></td>
                                            </tr>
                                            <tr class="bold" style="text-decoration:underline;">
                                                <td></td>
                                                <td><?php echo xlt('Sph{{Sphere}}'); ?></td>
                                                <td><?php echo xlt('Cyl{{Cylinder}}'); ?></td>
                                                <td><?php echo xlt('Axis{{Axis in a glasses prescription}}'); ?></td>
                                                <td><?php echo xlt('BC{{Base Curve}}'); ?></td>
                                                <td><?php echo xlt('Diam{{Diameter}}'); ?></td>
                                                <td><?php echo xlt('ADD'); ?></td>
                                                <td><td>
                                                <td><?php echo xlt('Supplier'); ?></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td><?php echo text($row['ODSPH']); ?></td>
                                                <td><?php echo text($row['ODCYL']); ?></td>
                                                <td><?php echo text($row['ODAXIS']); ?></td>
                                                <td><?php echo text($row['ODBC']); ?></td>
                                                <td><?php echo text($row['ODDIAM']); ?></td>
                                                <td><?php echo text($row['ODADD']); ?></td>
                                                <td colspan="3" class="right"><?php echo text($row['CTLSUPPLIEROD']); ?></td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="bold underline left"><u><?php echo xlt('Left Lens'); ?></u>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="left"><?php echo text($row['CTLBRANDOS']); ?></td>
                                            </tr>
                                            <tr class="bold" style="text-decoration:underline;">
                                                <td></td>
                                                <td><?php echo xlt('Sph{{Sphere}}'); ?></td>
                                                <td><?php echo xlt('Cyl{{Cylinder}}'); ?></td>
                                                <td><?php echo xlt('Axis{{Axis in a glasses prescription}}'); ?></td>
                                                <td><?php echo xlt('BC{{Base Curve}}'); ?></td>
                                                <td><?php echo xlt('Diam{{Diameter}}'); ?></td>
                                                <td><?php echo xlt('ADD'); ?></td>
                                                <td><td>
                                                <td><?php echo xlt('Supplier'); ?></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td><?php echo text($row['OSSPH']); ?></td>
                                                <td><?php echo text($row['OSCYL']); ?></td>
                                                <td><?php echo text($row['OSAXIS']); ?></td>
                                                <td><?php echo text($row['OSBC']); ?></td>
                                                <td><?php echo text($row['OSDIAM']); ?></td>
                                                <td><?php echo text($row['OSADD']); ?></td>
                                                <td colspan="3" class="right"><?php echo text($row['CTLSUPPLIEROS']); ?></td>

                                            </tr>
                                        </table>

                                        <?php
                                    } ?>
                                    </td>
                                </tr>
                            </table>
                        <hr>

                        </div>
                    <?php
                    } ?>
                </div>
            </div>
        </body>
    </html>
    <?php
    exit;
}

   ob_start();
    ?>
    <html>
        <head>
            <script src="<?php echo $GLOBALS['assets_static_relative'] ?>/jquery-min-1-10-2/index.js"></script>
            <script src="<?php echo $GLOBALS['assets_static_relative'] ?>/bootstrap-3-3-4/dist/js/bootstrap.min.js"></script>
            <script src="<?php echo $GLOBALS['assets_static_relative'] ?>/qtip2-2-2-1/jquery.qtip.min.js"></script>

            <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/bootstrap-3-3-4/dist/css/bootstrap.min.css">
            <link rel="stylesheet" href="<?php echo $GLOBALS['css_header']; ?>" type="text/css">
            <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/jquery-ui-1-10-4/themes/ui-lightness/jquery-ui.min.css">
            <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/pure-0-5-0/pure-min.css">
            <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/qtip2-2-2-1/jquery.qtip.min.css" />
            <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/font-awesome-4-6-3/css/font-awesome.min.css">
            <link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/css/style.css" type="text/css">

            <style>
                .title {
                  font-size:1em;
                  position:absolute;
                  right:10px;
                  top:30px;
                  font-size: 1em;
                }
                .refraction {
                    top:1in;
                    float:left;
                    min-height:1.0in;
                    border: 1.00pt solid #000000;
                    padding: 5;
                    box-shadow: 10px 10px 5px #888888;
                    border-radius: 8px;
                    margin: 5 auto 10 10;
                    width:5.0in;
                }
                .refraction td {
                    text-align:center;
                    font-size:8pt;
                    padding:5;
                    width:0.35in;
                    vertical-align: text-middle;
                    text-decoration: none;
                }
                table {
                    font-size: 1.0em;
                    padding: 2px;
                    color: black;
                    vertical-align: text-top;
                }

                input[type=text] {
                    text-align: center;
                    width:60px;
                }
                .refraction  b{
                    text-decoration:bold;
                }
                .refraction td.right {
                    text-align: right;
                    text-decoration: none;
                    width:0.7in;
                    vertical-align: text-top;
                }
                .refraction td.left {
                    vertical-align: text-top;
                    text-align:left;
                }

                .right {
                    text-align:right;
                    vertical-align: text-top;
                    xwidth:10%;
                }
                .left {
                    vertical-align: text-top;
                    text-align:left;
                }
                .title {
                    font-size: 0.9em;
                    font-weight:normal;
                }
                .bold {
                    font-weight:600;
                }
                input {
                    width:60px;
                }
                input[type="radio"] {
                    width:15px;
                }
            </style>
                 <!-- jQuery library -->

            <script language="JavaScript">
                <?php require_once("$srcdir/restoreSession.php"); ?>
                function pick_rxType(rxtype,id) {
                    var url = "../../forms/eye_mag/SpectacleRx.php";
                    var formData = {
                        'RXTYPE'     : rxtype,
                        'id'         : id
                    };
                    top.restoreSession();
                    $.ajax({
                   type         : 'POST',
                   url          : url,
                   data         : formData
                   });
                   if (rxtype == 'Trifocal') {
                    $("[name$='MIDADD']").show();
                    $("[name$='ADD2']").show();
                    } else if  (rxtype == 'Bifocal') {
                    $("[name$='MIDADD']").hide().val('');
                    $("[name$='ADD2']").show();
                    } else if  (rxtype == 'Progressive') {
                    $("[name$='MIDADD']").hide().val('');
                    $("[name$='ADD2']").show();
                    } else if (rxtype =="Single") {
                    $("[name$='MIDADD']").hide().val('');
                    $("[name$='ADD2']").hide().val('');
                   }
                }
                function submit_form(){
                    var url = "../../forms/eye_mag/SpectacleRx.php?mode=update";
                    formData = $("form#Spectacle").serialize();
                    top.restoreSession();
                    $.ajax({
                       type     : 'POST',
                       url      : url,
                       data     : formData
                       });
                }
                //add sph and cyl, flip cyl sign, rotate axis 90.
                function reverse_cylinder() {
                    var Rsph  = $('#ODSPH').val();
                    var Rcyl  = $('#ODCYL').val();
                    var Raxis = $('#ODAXIS').val();
                    var Lsph  = $('#OSSPH').val();
                    var Lcyl  = $('#OSCYL').val();
                    var Laxis = $('#OSAXIS').val();
                    if (Rsph=='' && Rcyl =='' && Lsph=='' && lcyl =='') return;
                    if ((!Rcyl.match(/SPH/i)) && (Rcyl >'')) {
                        if (Rsph.match(/plano/i)) Rsph ='0';
                        Rsph = Number(Rsph);
                        Rcyl = Number(Rcyl);
                        Rnewsph = Rsph + Rcyl;
                        if (Rnewsph ==0) Rnewsph ="PLANO";
                        Rnewcyl = Rcyl * -1;
                        if (Rnewcyl > 0) Rnewcyl = "+"+Rnewcyl;
                        if (parseInt(Raxis) < 90) {
                            Rnewaxis = parseInt(Raxis) + 90;
                        } else {
                            Rnewaxis = parseInt(Raxis) - 90;
                        }
                        if (Rnewcyl=='0') Rnewcyl = "SPH";
                        if (Rnewsph =='0') {
                            Rnewsph ="PLANO";
                            if (Rnewcyl =="SPH") Rnewcyl = '';
                        }
                        $("#ODSPH").val(Rnewsph);
                        $("#ODCYL").val(Rnewcyl);
                        $("#ODAXIS").val(Rnewaxis);
                        $('#ODAXIS').trigger('blur');
                        $('#ODSPH').trigger('blur');
                        $('#ODCYL').trigger('blur');
                    }
                     if ((!Lcyl.match(/SPH/i)) && (Lcyl >'')) {
                        if (!Lsph.match(/\d/)) Lsph ='0';
                        Lsph = Number(Lsph);
                        Lcyl = Number(Lcyl);
                        Lnewsph = Lsph + Lcyl;
                        Lnewcyl = Lcyl * -1;
                        if (Lnewcyl > 0) Lnewcyl = "+"+ Lnewcyl;
                        if (parseInt(Laxis) < 90) {
                            Lnewaxis = parseInt(Laxis) + 90;
                        } else {
                            Lnewaxis = parseInt(Laxis) - 90;
                        }

                        if (Lnewcyl=='0') Lnewcyl = "SPH";
                        if (Lnewsph =='0') {
                            Lnewsph ="PLANO";
                            if (Lnewcyl =="SPH") Lnewcyl = '';
                        }

                        $("#OSSPH").val(Lnewsph);
                        $("#OSCYL").val(Lnewcyl);
                        $("#OSAXIS").val(Lnewaxis);
                        $('#OSAXIS').trigger('blur');
                        $('#OSSPH').trigger('blur');
                        $('#OSCYL').trigger('blur');
                    }
                }
            </script>
        </head>
        <body>
            <?php echo report_header($pid, "web");
            $visit= getEncounterDateByEncounter($encounter);
            $visit_date = $visit['date'];
            ?>
            <br /><br />
            <form method="post" action="<?php echo $rootdir;?>/forms/<?php echo text($form_folder); ?>/SpectacleRx.php?mode=update" id="Spectacle" class="eye_mag pure-form" name="Spectacle" style="text-align:center;">
              <!-- start container for the main body of the form -->
                <input type="hidden" name="REFDATE" id="REFDATE" value="<?php echo attr($data['date']); ?>">
                <input type="hidden" name="RXTYPE" id="RXTYPE" value="<?php echo attr($RXTYPE); ?>">
                <input type="hidden" name="REFTYPE" value="<?php echo attr($REFTYPE); ?>" />
                <input type="hidden" name="pid" id="pid" value="<?php echo attr($pid); ?>">
                <input type="hidden" name="id" id="id" value="<?php echo attr($insert_this_id); ?>">
                <div style="margin:5;text-align:center;display:inline-block;">

                    <table style="min-width:615px;">
                        <tr>
                            <td>
                                <?php

                                if ($REFTYPE !="CTL") { ?>
                                    <table id="SpectacleRx" name="SpectacleRx" class="refraction bordershadow" style="min-width:610px;top:0px;">
                                        <tr style="font-weight:bold;text-align:center;">
                                            <td><i name="reverse"  id="reverse" class="fa fa-gamepad fa-2x"></i></td>
                                            <td></td>
                                            <td><?php echo xlt('Sph{{Sphere}}'); ?></td>
                                            <td><?php echo xlt('Cyl{{Cylinder}}'); ?></td>
                                            <td><?php echo xlt('Axis{{Axis of a glasses prescription}}'); ?></td>
                                            <td rowspan="5" class="right" colspan="3" style="min-width:200px;font-weight:bold;">
                                                <b style="font-weight:bold;text-decoration:underline;"><?php echo xlt('Rx Type'); ?></b><br /><br />
                                                <b id="SingleVision_span" name="SingleVision_span"><?php echo xlt('Single'); ?>
                                                    <input type="radio" onclick="pick_rxType('Single','<?php echo attr(addslashes($insert_this_id)); ?>');" value="Single" id="RXTYPE" name="RXTYPE" class="input-helper--radio input-helper--radio" <?php echo attr($Single); ?>></b><br />
                                                <b id="Bifocal_span" name="Bifocal_span"><?php echo xlt('Bifocal'); ?>
                                                    <input type="radio" onclick="pick_rxType('Bifocal','<?php echo attr(addslashes($insert_this_id)); ?>');" value="Bifocal" id="RXTYPE" name="RXTYPE" <?php echo attr($Bifocal); ?>></b><br />
                                                <b id="Trifocal_span" name="Trifocal_span"><?php echo xlt('Trifocal'); ?>
                                                    <input type="radio" onclick="pick_rxType('Trifocal','<?php echo attr(addslashes($insert_this_id)); ?>');" value="Trifocal" id="RXTYPE" name="RXTYPE" <?php echo attr($Trifocal); ?>></b><br />
                                                <b id="Progressive_span"><?php echo xlt('Prog.{{Progressive lenses}}'); ?>
                                                    <input type="radio" onclick="pick_rxType('Progressive','<?php echo attr(addslashes($insert_this_id)); ?>');" value="Progressive" id="RXTYPE" name="RXTYPE" <?php echo attr($Progressive); ?>></b><br />
                                            </td>
                                        </tr>
                                        <tr class="center">
                                            <td rowspan="2"  style="text-align:right;font-weight:bold;"><?php echo xlt('Distance'); ?></td>
                                            <td style="text-align:right;font-weight:bold;"><?php echo xlt('OD{{right eye}}'); ?></td>
                                            <td><input type=text id="ODSPH" name="ODSPH" value="<?php echo attr($ODSPH); ?>"></td>
                                            <td><input type=text id="ODCYL" name="ODCYL" value="<?php echo attr($ODCYL); ?>"></td>
                                            <td><input type=text id="ODAXIS" name="ODAXIS" value="<?php echo attr($ODAXIS); ?>"></td>
                                        </tr>
                                        <tr class="center">
                                            <td name="W_wide" style="text-align:right;font-weight:bold;"><?php echo xlt('OS{{left eye}}'); ?></td>
                                            <td><input type=text id="OSSPH" name="OSSPH" value="<?php echo attr($OSSPH); ?>"></td>
                                            <td><input type=text id="OSCYL" name="OSCYL" value="<?php echo attr($OSCYL); ?>"></td>
                                            <td><input type=text id="OSAXIS" name="OSAXIS" value="<?php echo attr($OSAXIS); ?>"></td>
                                        </tr>
                                        <tr class="NEAR center">
                                            <td rowspan=2 nowrap style="text-decoration:none;text-align:right;font-weight:bold;"><?php echo xlt('ADD'); ?>:<br /><?php echo xlt("Mid{{Middle segment in a trifocal glasses prescription}}"); ?>/<?php echo xlt("Near"); ?></td>
                                            <td style="text-align:right;font-weight:bold;"><?php echo xlt('OD{{right eye}}'); ?></td>
                                            <td name="COLADD1"><input type="text" id="ODMIDADD" name="ODMIDADD" value="<?php echo attr($ODMIDADD); ?>"></td>
                                            <td class="WAdd2"><input type="text" id="ODADD2" name="ODADD2" value="<?php echo attr($ODADD2); ?>"></td>
                                        </tr>
                                        <tr class="NEAR center">
                                            <td style="text-align:right;font-weight:bold;"><?php echo xlt('OS{{left eye}}'); ?></td>
                                            <td name="COLADD1"><input type="text" id="OSMIDADD" name="OSMIDADD" value="<?php echo attr($OSMIDADD); ?>"></td>
                                            <td class="WAdd2"><input type="text" id="OSADD2" name="OSADD2" value="<?php echo attr($OSADD2); ?>"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="text-align:right;font-weight:bold;"><?php echo xlt('Comments'); ?>:</td>
                                            <td colspan="4">
                                                <textarea style="width:100%;height:5em;" id="COMMENTS" name="COMMENTS"><?php echo text($COMMENTS); ?></textarea>
                                            </td>
                                        </tr>
                                        <!-- start Dispense data -->
                                        <tr class="header closeButton">
                                            <td colspan="9" class="right">
                                                <span><?php
                                                if ($ODHPD||$ODHBASE||$ODVPD||$ODVBASE||$ODSLABOFF||$ODVERTEXDIST||
                                                            $OSHPD||$OSHBASE||$OSVPD||$OSVBASE||$OSSLABOFF||$OSVERTEXDIST||
                                                            $ODMPDD||$ODMPDN||$OSMPDD||$OSMPDN||$BPDD||$BPDN||
                                                            $LENS_MATERIAL||$LENS_TREATMENTS) {
                                                          $detailed = '1';
                                                    ?><i class="fa fa-minus-square-o"></i><?php
                                                } else {
                                                    $detailed ='0';
                                                    ?><i class="fa fa-plus-square-o"></i><?php
                                                }
                                                        ?>
                                                </span>

                                            </td>
                                        </tr>
                                        <tr><td colspan="9" class="right"><xhr /></td></tr>
                                        <tr class="dispense_data" style="font-weight:bold;text-align:center;">
                                            <td name="W_wide" colspan="2"></td>
                                            <td name="W_wide" title="<?php echo xla('Horizontal Prism Power'); ?>"><?php echo xlt('Horiz Prism{{abbreviation for Horizontal Prism Power}}'); ?></td>
                                            <td name="W_wide" title="<?php echo xla('Horizontal Prism Base'); ?>"><?php echo xlt('Horiz Base{{abbreviation for Horizontal Prism Base}}'); ?></td>
                                            <td name="W_wide" title="<?php echo xla('Vertical Prism Power'); ?>"><?php echo xlt('Vert Prism{{abbreviation for Vertical Prism Power}}'); ?></td>
                                            <td name="W_wide" title="<?php echo xla('Vertical Prism Base'); ?>"><?php echo xlt('Vert Base{{abbreviation for Vertical Prism Base}}'); ?></td>
                                            <td name="W_wide" title="<?php echo xla('Slab Off'); ?>"><?php echo xlt('Slab Off'); ?></td>
                                            <td name="W_wide" title="<?php echo xla('Vertex Distance'); ?>"><?php echo xlt('Vert Distance{{abbreviation for Vertex Distance}}'); ?></td>
                                         </tr>
                                        <tr class="dispense_data">
                                            <td name="W_wide" style="text-align:right;font-weight:bold;" colspan="2"><?php echo xlt('OD{{right eye}}'); ?></td>
                                            <td name="W_wide"><input type="text" class="prism" id="ODHPD" name="ODHPD" value="<?php echo attr($ODHPD); ?>"></td>
                                            <td name="W_wide"><input type="text" class="prism" id="ODHBASE" name="ODHBASE" value="<?php echo attr($ODHBASE); ?>"></td>
                                            <td name="W_wide"><input type="text" class="prism" id="ODVPD" name="ODVPD" value="<?php echo attr($ODVPD); ?>"></td>
                                            <td name="W_wide"><input type="text" class="prism" id="ODVBASE" name="ODVBASE" value="<?php echo attr($ODVBASE); ?>"></td>
                                            <td name="W_wide"><input type="text" class="prism" id="ODSLABOFF" name="ODSLABOFF" value="<?php echo attr($ODSLABOFF); ?>"></td>
                                            <td name="W_wide"><input type="text" class="prism" id="ODVERTEXDIST" name="ODVERTEXDIST" value="<?php echo attr($ODVERTEXDIST); ?>"></td>
                                        </tr>
                                        <tr class="dispense_data">
                                            <td name="W_wide" style="text-align:right;font-weight:bold;" colspan="2"><?php echo xlt('OS{{left eye}}'); ?></td>
                                            <td name="W_wide"><input type="text" class="prism" id="OSHPD" name="OSHPD" value="<?php echo attr($OSHPD); ?>"></td>
                                            <td name="W_wide"><input type="text" class="prism" id="OSHBASE" name="OSHBASE" value="<?php echo attr($OSHBASE); ?>"></td>
                                            <td name="W_wide"><input type="text" class="prism" id="OSVPD" name="OSVPD" value="<?php echo attr($OSVPD); ?>"></td>
                                            <td name="W_wide"><input type="text" class="prism" id="OSVBASE" name="OSVBASE" value="<?php echo attr($OSVBASE); ?>"></td>
                                            <td name="W_wide"><input type="text" class="prism" id="OSSLABOFF" name="OSSLABOFF" value="<?php echo attr($OSSLABOFF); ?>"></td>
                                            <td name="W_wide"><input type="text" class="prism" id="OSVERTEXDIST" name="OSVERTEXDIST" value="<?php echo attr($OSVERTEXDIST); ?>"></td>
                                         </tr>
                                        <tr class="dispense_data"><td colspan="9" class="center"><xhr /></td></tr>
                                        <tr class="dispense_data" style="font-weight:bold;text-align:center;">
                                            <td></td>
                                            <td></td>
                                            <td name="W_wide" title="<?php echo xla('Monocular Pupillary Diameter - Distance'); ?>"><?php echo xlt('MPD-D{{abbreviation for Monocular Pupillary Diameter - Distance}}'); ?></td>
                                            <td name="W_wide" title="<?php echo xla('Monocular Pupillary Diameter - Near'); ?>"><?php echo xlt('MPD-N{{abbreviation for Monocular Pupillary Diameter - Near}}'); ?></td>
                                            <td name="W_wide" title="<?php echo xla('Binocular Pupillary Diameter - Distance'); ?>"><?php echo xlt('BPD-D{{abbreviation for Binocular Pupillary Diameter - Distance}}'); ?></td>
                                            <td name="W_wide" title="<?php echo xla('Binocular Pupillary Diameter - Near'); ?>"><?php echo xlt('BPD-N{{abbreviation for Binocular Pupillary Diameter - Near}}'); ?></td>

                                            <td colspan="2">Lens Material:</td>
                                        </tr>
                                        <tr>
                                            <td name="W_wide" style="text-align:right;font-weight:bold;" colspan="2"><?php echo xlt('OD{{right eye}}'); ?></td>
                                            <td name="W_wide"><input type="text" class="prism" id="ODMPDD" name="ODMPDD" value="<?php echo attr($ODMPDD); ?>"></td>
                                            <td name="W_wide"><input type="text" class="prism" id="ODMPDN" name="ODMPDN" value="<?php echo attr($ODMPDN); ?>"></td>
                                            <td name="W_wide" rowspan="2" style="vertical-align:middle;"><input type="text" class="prism" id="BPDD" name="BPDD" value="<?php echo attr($BPDD); ?>"></td>
                                            <td name="W_wide" rowspan="2" style="vertical-align:middle;"><input type="text" class="prism" id="BPDN" name="BPDN" value="<?php echo attr($BPDN); ?>"></td>
                                            <td colspan="2">   <?php
                                                        echo generate_select_list("LENS_MATERIAL", "Eye_Lens_Material", "$LENS_MATERIAL", '', ' ', '', 'restoreSession;submit_form();', '', array('style'=>'width:120px'));
                                                                ?>
                                                </td>
                                        </tr>
                                        <tr>
                                            <td name="W_wide" style="text-align:right;font-weight:bold;" colspan="2"><?php echo xlt('OS{{left eye}}'); ?></td>
                                            <td name="W_wide"><input type="text" class="prism" id="OSMPDD" name="OSMPDD" value="<?php echo attr($OSMPDD); ?>"></td>
                                            <td name="W_wide"><input type="text" class="prism" id="OSMPDN" name="OSMPDN" value="<?php echo attr($OSMPDN); ?>"></td>
                                        </tr>
                                        <tr class="dispense_data"><td colspan="9" class="center"><xhr /></td></tr>
                                        <tr style="font-weight:bold;text-align:center;">
                                            <td colspan="3"><?php echo xlt('Lens Treatments'); ?>
                                            </td>
                                        </tr>
                                        <tr style="text-align:left;vertical-align:top;">
                                            <td colspan="5" style="font-weight:bold;text-align:left;">
                                                <?php  echo generate_lens_treatments($W, $LENS_TREATMENTS); ?>
                                            </td>
                                        </tr>
                                        <tr class="dispense_data"><td colspan="9" class="center"><hr /></td></tr>

                                    </table>&nbsp;<br /><br /><br />
                                    <?php
                                } else { ?>
                                    <table id="CTLRx" name="CTLRx" class="refraction">
                                        <tr>
                                            <td class="right bold underline"><?php echo xlt('Right Lens'); ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="right bold"><?php echo xlt('Manufacturer'); ?>:</td>
                                            <td colspan="2" class="left"><?php echo text($CTLMANUFACTUREROD); ?></td>
                                            <td class="right bold"><?php echo xlt('Brand'); ?>:</td>
                                            <td colspan="2" class="left"><?php echo text($CTLBRANDOD); ?></td>
                                        </tr>
                                        <tr class="bold" style="line-height:0.3em;font-size:0.6em;">
                                            <td><?php echo xlt('SPH{{Sphere}}'); ?></td>
                                            <td><?php echo xlt('CYL{{Cylinder}}'); ?></td>
                                            <td><?php echo xlt('AXIS{{Axis of a glasses prescription}}'); ?></td>
                                            <td><?php echo xlt('BC{{Base Curve}}'); ?></td>
                                            <td><?php echo xlt('DIAM{{Diameter}}'); ?></td>
                                            <td><?php echo xlt('ADD{{Bifocal Add}}'); ?></td>
                                            <td><?php echo xlt('ACUITY'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><input type=text id="CTLODSPH" name="CTLODSPH" value="<?php echo attr($ODSPH); ?>"></td>
                                            <td><input type=text id="CTLODCYL" name="CTLODCYL" value="<?php echo attr($ODCYL); ?>"></td>
                                            <td><input type=text id="CTLODAXIS" name="CTLODAXIS" value="<?php echo attr($ODAXIS); ?>"></td>
                                            <td><input type=text id="CTLODBC" name="CTLODBC" value="<?php echo attr($ODBC); ?>"></td>
                                            <td><input type=text id="CTLODDIAM" name="CTLODDIAM" value="<?php echo attr($ODDIAM); ?>"></td>
                                            <td><input type=text id="CTLODADD" name="CTLODADD" value="<?php echo attr($ODADD); ?>"></td>
                                            <td><input type=text id="CTLODVA" name="CTLODVA" value="<?php echo attr($ODVA); ?>"></td>
                                        </tr>
                                        <tr><td colspan="8"><hr /></td></tr>
                                        <tr>
                                            <td class="right bold underline"><?php echo xlt('Left Lens'); ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="right bold"><?php echo xlt('Manufacturer'); ?>:</td>
                                            <td colspan="2" class="left"><?php echo text($CTLMANUFACTUREROS); ?></td>
                                            <td class="right bold"><?php echo xlt('Brand'); ?>:</td>
                                            <td colspan="2" class="left"><?php echo text($CTLBRANDOS); ?></td>
                                        </tr>
                                        <tr class="bold" style="line-height:0.3em;font-size:0.6em;">
                                            <td><?php echo xlt('SPH{{Sphere}}'); ?></td>
                                            <td><?php echo xlt('CYL{{Cylinder}}'); ?></td>
                                            <td><?php echo xlt('AXIS{{Axis of a glasses prescription}}'); ?></td>
                                            <td><?php echo xlt('BC{{Base Curve}}'); ?></td>
                                            <td><?php echo xlt('DIAM{{Diameter}}'); ?></td>
                                            <td><?php echo xlt('ADD{{Bifocal Add}}'); ?></td>
                                            <td><?php echo xlt('ACUITY'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><input type=text id="CTLOSSPH" name="CTLOSSPH" value="<?php echo attr($OSSPH); ?>"></td>
                                            <td><input type=text id="CTLOSCYL" name="CTLOSCYL" value="<?php echo attr($OSCYL); ?>"></td>
                                            <td><input type=text id="CTLOSAXIS" name="CTLOSAXIS" value="<?php echo attr($OSAXIS); ?>"></td>
                                            <td><input type=text id="CTLOSBC" name="CTLOSBC" value="<?php echo attr($OSBC); ?>"></td>
                                            <td><input type=text id="CTLOSDIAM" name="CTLOSDIAM" value="<?php echo attr($OSDIAM); ?>"></td>
                                            <td><input type=text id="CTLOSADD" name="CTLOSADD" value="<?php echo attr($OSADD); ?>"></td>
                                            <td><input type=text id="CTLOSVA" name="CTLOSVA" value="<?php echo attr($OSVA); ?>"></td>
                                        </tr>
                                        <?php if ($COMMENTS >'') { ?>
                                        <tr><td colspan="8"><hr /></td></tr>
                                        <tr>
                                            <td class="right bold red" colspan="2" style="vertical-align:top;"><?php echo xlt('Comments'); ?>:</u></td>
                                            <td colspan="6" class="left">
                                                <textarea cols="30" rows="4"><?php echo text($COMMENTS); ?></textarea>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </table>
                                    <?php
                                } ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="margin:25px auto;text-align:center;">
                            <?php
                                $signature = $GLOBALS["webserver_root"]."/interface/forms/eye_mag/images/sign_".attr($_SESSION['authUserID']).".jpg";
                            if (file_exists($signature)) {
                                ?>
                                <span style="position:relative;padding-left:40px;">
                                <img src='<?php echo $web_root; ?>/interface/forms/eye_mag/images/sign_<?php echo attr($_SESSION['authUserID']); ?>.jpg'
                                    style="width:240px;height:85px;border-block-end: 1pt solid black;margin:5px;" />
                                    </span><br />

                            <?php
                            } ?>

                            <?php echo xlt('Provider'); ?>: <?php echo text($prov_data['fname']); ?> <?php echo text($prov_data['lname']);
                            if ($prov_data['suffix']) {
                                echo ", ".$prov_data['suffix'];
                            } ?><br />
                            <small><?php echo xlt('e-signed'); ?> <input type="checkbox" checked="checked"></small>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>

        </body>
        <script>
            $(document).ready(function() {
                $('.header').click(function () {
                    var $this = $(this);
                    $(this).nextUntil('tr.header').slideToggle(100).promise().done(function () {
                        $this.find('span').html(function (_, value) {
                        return value == '<i class="fa fa-plus-square-o"></i>' ? '<i class="fa fa-minus-square-o"></i>' : '<i class="fa fa-plus-square-o"></i>';
                        });
                    });
                });
                <?php
                if (!$detailed) {
                    echo "$('.header').trigger('click');";
                } ?>

                $("input[name$='PD']").blur(function() {
                                                                       //make it all caps
                                                                       var str = $(this).val();
                                                                       str = str.toUpperCase();
                                                                       $(this).val(str);
                                                                       });
                $('input[name$="SPH"]').blur(function() {
                                              var mid = $(this).val();
                                              if (mid.match(/PLANO/i)) {
                                                 $(this).val('PLANO');
                                                 return;
                                             }
                                              if (mid.match(/^[\+\-]?\d{1}$/)) {
                                              mid = mid+".00";
                                              }
                                              if (mid.match(/\.[27]$/)) {
                                              mid = mid + '5';
                                              }
                                              if (mid.match(/\.\d$/)) {
                                              mid = mid + '0';
                                              }
                                              //if near is +2. make it +2.00
                                              if (mid.match(/\.$/)) {
                                              mid= mid + '00';
                                              }
                                              if ((!mid.match(/\./))&&(mid.match(00|25|50|75))) {
                                              var front = mid.match(/(\d{0,2})(00|25|50|75)/)[1];
                                              var back = mid.match(/(\d{0,2})(00|25|50|75)/)[2];
                                              if (front =='') front ='0';
                                              mid = front + "." + back;
                                              }
                                              if (!mid.match(/\./)) {
                                              var front = mid.match(/([\+\-]?\d{0,2})(\d{2})/)[1];
                                              var back  = mid.match(/(\d{0,2})(\d{2})/)[2];
                                              if (front =='') front ='0';
                                              if (front =='-') front ='-0';
                                              mid = front + "." + back;
                                              }
                                              if (!mid.match(/^(\+|\-){1}/)) {
                                              mid = "+" + mid;
                                              }
                                              $(this).val(mid);
                                              });

                $("input[name$='ADD'],input[name$='ADD2']").blur(function() {
                                            var add = $(this).val();
                                            add = add.replace(/=/g,"+");
                                            //if add is one digit, eg. 2, make it +2.00
                                            if (add.match(/^\d{1}$/)) {
                                                add = "+"+add+".00";
                                            }
                                            //if add is '+'one digit, eg. +2, make it +2.00
                                            if (add.match(/^\+\d{1}$/)) {
                                                add = add+".00";
                                            }
                                            //if add is 2.5 or 2.0 make it 2.50 or 2.00
                                            if (add.match(/\.[05]$/)) {
                                                add = add + '0';
                                            }
                                            //if add is 2.2 or 2.7 make it 2.25 or 2.75
                                            if (add.match(/\.[27]$/)) {
                                                add = add + '5';
                                            }
                                            //if add is +2. make it +2.00
                                            if (add.match(/\.$/)) {
                                                add = add + '00';
                                            }
                                            if ((!add.match(/\./))&&(add.match(/(0|25|50|75)$/))) {
                                                var front = add.match(/([\+]?\d{0,1})(00|25|50|75)/)[1];
                                                var back  = add.match(/([\+]?\d{0,1})(00|25|50|75)/)[2];
                                                if (front =='') front ='0';
                                                add = front + "." + back;
                                            }
                                            if (!add.match(/^(\+)/) && (add.length >  0)) {
                                                add= "+" + add;
                                            }
                                            $(this).val(add);
                                            if (this.id=="ODADD2") $('#OSADD2').val(add);
                                            if (this.id=="ODMIDADD") $('#OSMIDADD').val(add);
                                            if (this.id=="CTLODADD") $('#CTLOSADD').val(add);
                                            });

                $("input[name$='AXIS']").blur(function() {
                                             // Make this a 3 digit leading zeros number.
                                             // we are not translating text to numbers, just numbers to
                                             // a 3 digit format with leading zeroes as needed.
                                             // assume the end user KNOWS there are only numbers presented and
                                             // more than 3 digits is a mistake...
                                             // (although this may change with topographic answer)
                                             var axis = $(this).val();
                                             var group = this.name.replace("AXIS", "CYL");;
                                             var cyl = $("#"+group).val();
                                             if ((cyl > '') && (cyl != 'SPH')) {
                                             if (!axis.match(/\d\d\d/)) {
                                             if (!axis.match(/\d\d/)) {
                                             if (!axis.match(/\d/)) {
                                             axis = '0';
                                             }
                                             axis = '0' + axis;
                                             }
                                             axis = '0' + axis;
                                             }
                                             } else {
                                             axis = '';
                                             }
                                             $(this).val(axis);
                                             });
                $("[name$='CYL']").blur(function() {
                                                var mid = $(this).val();
                                                var group = this.name.replace("CYL", "SPH");;
                                                var sphere = $("#"+group).val();
                                                if (((mid.length == 0) && (sphere.length >  0))||(mid.match(/sph/i))) {
                                                $(this).val('SPH');
                                                var axis = this.name.replace("CYL", "AXIS");
                                                $("#"+axis).val('');
                                                return;
                                                } else if (sphere.length >  0) {
                                                if (mid.match(/^[\+\-]?\d{1}$/)) {
                                                mid = mid+".00";
                                                }
                                                if (mid.match(/^(\d)(\d)$/)) {
                                                mid = mid[0] + '.' +mid[1];
                                                }
                                                //if mid is 2.5 or 2.0 make it 2.50 or 2.00
                                                if (mid.match(/\.[05]$/)) {
                                                mid = mid + '0';
                                                }
                                                //if mid is 2.2 or 2.7 make it 2.25 or 2.75
                                                if (mid.match(/\.[27]$/)) {
                                                mid = mid + '5';
                                                }
                                                //if mid is +2. make it +2.00
                                                if (mid.match(/\.$/)) {
                                                mid = mid + '00';
                                                }
                                                if (mid.match(/([\+\-]?\d{0,2})\.?(00|25|50|75)/)) {
                                                var front = mid.match(/([\+\-]?\d{0,2})\.?(00|25|50|75)/)[1];
                                                var back  = mid.match(/([\+\-]?\d{0,2})\.?(00|25|50|75)/)[2];
                                                if (front =='') front ='0';
                                                mid = front + "." + back;
                                                }
                                                if (!mid.match(/^(\+|\-){1}/) && (sphere.length >  0)) {
                                                //Since it doesn't start with + or - then give it '+'
                                                mid = "+" + mid;
                                                }
                                                $(this).val(mid);
                                                }
                                                });
                $("input,textarea,text,checkbox").change(function(){
                                                           submit_form($(this));
                                                           });
                $("#reverse").click(function() {
                    //alert('Start');
                    reverse_cylinder('');
                    //alert('Finish');

                });
                                  $("input[name$='SPH'],input[name$='CYL']").on('keyup', function(e) {
                                                                                        if (e.keyCode=='61' || e.keyCode=='74') {
                                                                                        now = $(this).val();
                                                                                        now = now.replace(/=/g,"+").replace(/^j/g,"J");
                                                                                        $(this).val(now);
                                                                                        }
                                                                                        });

            });
            </script>
    </html>

    <?php
    $content = ob_get_clean();
    echo $content;
    exit;
?>
