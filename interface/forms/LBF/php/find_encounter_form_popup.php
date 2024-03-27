<?php

/**
 *
 * Script to find encounter form for copy form field data
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/

require_once("../../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Core\Header;

$dateFormat = DateFormatRead("jquery-datetimepicker");

$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : "";
$formname = isset($_REQUEST['formname']) ? $_REQUEST['formname'] : "";
$encounter = isset($_REQUEST['encounter']) ? $_REQUEST['encounter'] : "";

function fetch_appt_signatures_data_byId($eid)
{
    if (!empty($eid)) {
        $eSql = "SELECT FE.encounter, E.id, E.tid, E.table, E.uid, U.fname, U.lname, E.datetime, E.is_lock, E.amendment, E.hash, E.signature_hash
                FROM form_encounter FE
                LEFT JOIN esign_signatures E ON (case when E.`table` ='form_encounter' then FE.encounter = E.tid else  FE.id = E.tid END)
                LEFT JOIN users U ON E.uid = U.id
                WHERE FE.encounter = ?
                ORDER BY E.datetime ASC";
        $result = sqlQuery($eSql, array($eid));
        return $result;
    }
    return false;
}

$result4 = sqlStatement("SELECT fe.encounter,fe.date,openemr_postcalendar_categories.pc_catname, us.fname, us.mname, us.lname FROM form_encounter AS fe left join openemr_postcalendar_categories on fe.pc_catid=openemr_postcalendar_categories.pc_catid  left join users AS us on fe.provider_id = us.id  WHERE fe.pid = ? AND fe.encounter != ? order by fe.date desc", array($pid, $encounter));

$enounterList = array();
while ($rowresult4 = sqlFetchArray($result4)) {
    $encounter = isset($rowresult4['encounter']) ? $rowresult4['encounter'] : '';
    $id = '';

    if (!empty($encounter)) {
        $sql = "SELECT * FROM forms WHERE deleted=0 AND pid=? AND encounter=? AND formdir=?";
        $parms = array($pid, $encounter, $formname);
        $frow = sqlQuery($sql, $parms);
        if ($frow['form_id']) {
            $id = $frow['form_id'];
        }
    }

    if (!empty($id)) {
        $rowresult4['form_id'] = $id;
        $enounterList[] = $rowresult4;
    }
}
?>

<html>
<head>
    <title><?php echo xlt('Select Encounter'); ?></title>
    <link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
    <?php Header::setupHeader(['opener', 'jquery-ui', 'jquery-ui-base']); ?>

    <style type="text/css">
        .encounter-container {
            padding-top: 20px;
            font-size: 16px;
        }

        .encounter-container ul li {
            line-height: 25px;
        }
    </style>

    <script type="text/javascript">
        function selectEncounter(encounter_id, form_id, pid) {
            return selEncounterForm(encounter_id, form_id, pid);
        }

        function selEncounterForm(encounter_id, form_id, pid) {
            if (opener.closed || ! opener.setEncounterForm)
            alert(<?php echo xlj('The destination form was closed; I cannot act on your selection.'); ?>);
            else
            opener.setEncounterForm(encounter_id, form_id, pid);
            window.close();
            return false;
        }
    </script>
</head>
<body>
    <div class="encounter-container">
        <ul>
            <?php
            foreach ($enounterList as $i => $item) {
                $edate = isset($item['date']) ? date($dateFormat, strtotime($item['date'])) : '';
                $cCat = isset($item['pc_catname']) ? $item['pc_catname'] : '';
                $pName = trim($item['fname'] . ' ' . $item['mname'] . ' ' . $item['lname']);
                if (!empty($pName)) {
                    $pName = ' - ' . $pName;
                }

                $signed = $item['signed'] === true ? xl('Signed') : xl('Unsigned');
                if (!empty($signed)) {
                    $signed = ' - <i>' . text($signed) . '</i>';
                }

                $titleLink = trim(text($edate) . ' ' . text($cCat) . text($pName) . text($signed));

                $encounter_id = isset($item['encounter']) ? $item['encounter'] : '';
                $form_id = isset($item['form_id']) ? $item['form_id'] : '';

                ?>
                <li>
                   <a href="javascript: void(0)" onClick="selectEncounter(<?php echo attr_js($encounter_id); ?>, <?php echo attr_js($form_id); ?>, <?php echo attr_js($pid); ?>)"><?php echo $titleLink; ?></a>
                </li>
            <?php } ?>
        </ul>
    </div>
</body>
</html>