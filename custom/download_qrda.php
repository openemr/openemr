<?php

/**
 * QRDA Download
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ensoftek
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Ensoftek, Inc
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../interface/globals.php");
require_once "$srcdir/report_database.inc";
require_once("$srcdir/options.inc.php");
require_once("qrda_category1.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$report_id = (isset($_GET['report_id'])) ? trim($_GET['report_id']) : "";
$provider_id = (isset($_GET['provider_id'])) ? trim($_GET['provider_id']) : "";

$report_view = collectReportDatabase($report_id);
$dataSheet = json_decode($report_view['data'], true);
$type_report = $report_view['type'];
$type_report = (($type_report == "amc") || ($type_report == "amc_2011") || ($type_report == "amc_2014") ||
                  ($type_report == "cqm") || ($type_report == "cqm_2011") || ($type_report == "cqm_2014")) ? $type_report : "standard";

?>
<html>

<head>
<?php Header::setupHeader(['common', 'opener']); ?>

<script>
    var reportID = <?php echo js_escape($report_id); ?>;
    var provider_id = <?php echo js_escape($provider_id);?>;
    var zipFileArray = new Array();
    var failureMessage = "";
    $(function () {
        $("#checkAll").on("change", function() {
            var checked =  ( $("#checkAll").prop("checked") ) ? true : false;
            $("#thisForm input:checkbox").each(function() {
                $(this).prop("checked", checked);
            });
        });
    });

    function downloadSelected() {
        zipFileArray.length = 0;
        var criteriaArray = new Array();
        $("#thisForm input:checkbox:checked").each(function() {
            if ( $(this).attr("id") == "checkAll")
                return;
            criteriaArray.push($(this).attr("id"));
        });
        if ( criteriaArray.length == 0 ) {
            alert(<?php echo xlj('Please select at least one criteria to download');?>);
            return false;
        }
        for( var i=0 ; i < criteriaArray.length ; i++) {
            var checkBoxCounterArray = criteriaArray[i].split("check");
            var ruleID = $("#text" + checkBoxCounterArray[1]).val();
            //console.log(ruleID);
            var lastOne = ( ( i + 1 ) == criteriaArray.length ) ? 1 : 0;
            downloadXML(checkBoxCounterArray[1],lastOne,ruleID);
        }
    }

    function downloadXML(counter,lastOne) {
        $("#download" + counter).css("display","none");
        $("#spin" + counter).css("display","inline");
        $.ajax({
            type : "POST",
            url: "ajax_download.php",
            data : {
                reportID: reportID,
                counter: counter,
                ruleID: $("#text" + counter).val(),
                provider_id: provider_id,
                csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
            },
            context: document.body,
            success :
         function(data){
            // Check if download is complete
            var status = data.substr(0, 8);
            if ( status == "FAILURE:") {
                data = data.substr(8);
                //console.log(data);
                failureMessage += data + "\n";
            } else {
                zipFileArray.push(data);
                $("#checkmark" + counter).css("display","inline");
            }
            $("#download" + counter).css("display","inline");
            $("#spin" + counter).css("display","none");
            if ( lastOne == 1 ) {
                if ( zipFileArray.length ) {
                    var zipFiles = zipFileArray.join(",");
                    //console.log(zipFiles);
                    window.location = 'ajax_download.php?fileName=' + encodeURIComponent(zipFiles) + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>;
                    zipFileArray.length = 0;
                }
                if ( failureMessage ) {
                    console.log(failureMessage);
                    alert(failureMessage);
                }
                failureMessage = "";
            }
         }
        });
    }

    function closeMe() {
        window.close();
    }
</script>
<style>
    .downloadIcon:hover {
        cursor: hand;
    }
    .multiDownload {

    }
</style>
</head>

<body class="body_top">
<form id="thisForm" name="thisForm">
<table>
    <tr>
        <td><span class="title"><?php echo xlt("Generate/Download QRDA I - 2014"); ?>&nbsp;</span></td>
        <td>
            <a class="btn btn-primary multiDownload" href="#" onclick="downloadSelected();"><?php echo xlt("Download"); ?></a>
            <a class="btn btn-secondary" href="#" onclick="closeMe();"><?php echo xlt("Close"); ?></a>
        </td>
    </tr>
</table>
<br/>
<div id="report_results" style="width:95%">
<table class="oemr_list text">
    <thead>
        <th scope="col" class="multiDownload">
            <input type="checkbox" name="checkAll" id="checkAll"/>
            <div style="display:none" id=downloadAll>
                <img class="downloadIcon" src="<?php echo $GLOBALS['images_static_relative'];?>/downbtn.gif" onclick=downloadAllXML(); />
            </div>
            <div style="display:none" id=spinAll>;
                <img src="<?php echo $GLOBALS['webroot'];?>/interface/pic/ajax-loader.gif"/>
            </div>
        </th>
        <th scope="col">
            <?php echo xlt('Title'); ?>
        </th>

        <th scope="col">
            <?php echo xlt('Download'); ?>
        </th>
        <th scope="col">&nbsp;&nbsp;&nbsp;</th>
    </thead>
    <tbody>
        <?php
            $counter = 0;
        foreach ($dataSheet as $row) {
            if (isset($row['is_main']) || isset($row['is_sub'])) {
                if (count($cqmCodes ?? []) && in_array($row['cqm_nqf_code'], $cqmCodes)) {
                    continue;
                }

                echo "<tr>";
                $cqmCodes[] = $row['cqm_nqf_code'];
                echo "<td class=multiDownload>";
                echo "<input id=check" . attr($counter) . " type=checkbox />";
                echo "</td>";
                echo "<td class='detail'>";
                if (isset($row['is_main'])) {
                    echo "<b>" . generate_display_field(array('data_type' => '1','list_id' => 'clinical_rules'), $row['id']) . "</b>";
                    $tempCqmAmcString = "";
                    if (($type_report == "cqm") || ($type_report == "cqm_2011") || ($type_report == "cqm_2014")) {
                        if (!empty($row['cqm_pqri_code'])) {
                            $tempCqmAmcString .= " " .  xl('PQRI') . ":" . $row['cqm_pqri_code'] . " ";
                        }

                        if (!empty($row['cqm_nqf_code'])) {
                            $tempCqmAmcString .= " " .  xl('NQF') . ":" . $row['cqm_nqf_code'] . " ";
                        }
                    }

                    if (!empty($tempCqmAmcString)) {
                        echo "(" . text($tempCqmAmcString) . ")";
                    }
                } else {
                    echo generate_display_field(array('data_type' => '1','list_id' => 'rule_action_category'), $row['action_category']);
                    echo ": " . generate_display_field(array('data_type' => '1','list_id' => 'rule_action'), $row['action_item']);
                }

                echo "<input type=hidden id=text" . attr($counter) . " name=text" . attr($counter) . " value='" . attr($row['cqm_nqf_code']) . "'/>";
                echo "</td>";
                echo "<td align=center>";
                echo "<div id=download" . attr($counter) . ">";
                echo "<img class='downloadIcon' src='" . $GLOBALS['images_static_relative'] . "/downbtn.gif' onclick=downloadXML(" . attr_js($counter) . ",1); />";
                echo "</div>";
                echo "<div style='display:none' id=spin" . attr($counter) . ">";
                echo "<img src='" . $GLOBALS['webroot'] . "/interface/pic/ajax-loader.gif'/>";
                echo "</div>";
                echo "</td>";
                echo "<td>";
                echo "<div style='display:none' id=checkmark" . attr($counter) . ">";
                echo "<img src='" . $GLOBALS['images_static_relative'] . "/checkmark.png' />";
                echo "</div>";
                echo "</td>";
                echo "</tr>";
                $counter++;
            }
        } ?>
    </tbody>
</table>
</div>

</form>
</body>
</html>
