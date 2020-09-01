<?php

 /**
  * Dash Board Header.
  *
  * @package   OpenEMR
  * @link      http://www.open-emr.org
  * @author    Ranganath Pathak <pathak@scrs1.org>
  * @author    Brady Miller <brady.g.miller@gmail.com>
  * @copyright Copyright (c) 2018 Ranganath Pathak <pathak@scrs1.org>
  * @copyright Copyright (c) 2018-2020 Brady Miller <brady.g.miller@gmail.com>
  * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
  */

require_once("$srcdir/display_help_icon_inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

$url_webroot = $GLOBALS['webroot'];
$portal_login_href = $url_webroot . "/interface/patient_file/summary/create_portallogin.php";
?>

<div class="clearfix">
        <?php echo  $oemr_ui->pageHeading() . "\r\n"; ?>
<?php
// If patient is deceased, then show this (along with the number of days patient has been deceased for)
$days_deceased = is_patient_deceased($pid);
if ($days_deceased) { ?>
    <p class="deceased font-weight-bold text-danger">

        <?php
        $deceased_days = intval($days_deceased['days_deceased']);
        if ($deceased_days == 0) {
            $num_of_days = xl("Today");
        } elseif ($deceased_days == 1) {
             $num_of_days =  $deceased_days . " " . xl("day ago");
        } elseif ($deceased_days > 1 && $deceased_days < 90) {
             $num_of_days =  $deceased_days . " " . xl("days ago");
        } elseif ($deceased_days >= 90 && $deceased_days < 731) {
            $num_of_days =  "~" . round($deceased_days / 30) . " " . xl("months ago");  // function intdiv available only in php7
        } elseif ($deceased_days >= 731) {
             $num_of_days =  xl("More than") . " " . round($deceased_days / 365) . " " . xl("years ago");
        }

        if (strlen($days_deceased['date_deceased']) > 10 && $GLOBALS['date_display_format'] < 1) {
            $deceased_date = substr($days_deceased['date_deceased'], 0, 10);
        } else {
            $deceased_date = oeFormatShortDate($days_deceased['date_deceased']);
        }

        //echo  xlt("Deceased") . " - " . text(oeFormatShortDate($days_deceased['date_deceased'])) . " (" . text($num_of_days) . ")" ;
        echo  xlt("Deceased") . " - " . text($deceased_date) . " (" . text($num_of_days) . ")" ;
        ?>
    </p>
    <?php
} ?>
    <div class="form-group">

            <div class="btn-group" role="group">

            <?php
            if (AclMain::aclCheckCore('admin', 'super') && $GLOBALS['allow_pat_delete']) { ?>
                <a class='btn btn-secondary btn-sm btn-delete deleter delete'
                   href='<?php echo attr($url_webroot)?>/interface/patient_file/deleter.php?patient=<?php echo attr_url($pid);?>&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>'
                   onclick='return top.restoreSession()'><?php echo xlt('Delete'); ?></a>
                <?php
            } // Allow PT delete
            if ($GLOBALS['erx_enable']) { ?>
                <a class="btn btn-secondary btn-sm btn-add erx" href="<?php echo attr($url_webroot)?>/interface/eRx.php?page=medentry" onclick="top.restoreSession()">
                  <?php echo xlt('NewCrop MedEntry');?>
                </a>
                <a class="btn btn-secondary btn-sm btn-save iframe1"
                   href="<?php echo attr($url_webroot)?>/interface/soap_functions/soap_accountStatusDetails.php"
                   onclick="top.restoreSession()">
                   <?php echo xlt('NewCrop Account Status');?>
                </a>
            <!--<div id='accountstatus'></div>RP_MOVED-->
                <?php
            } // eRX Enabled
            //Patient Portal
            $portalUserSetting = true; //flag to see if patient has authorized access to portal
            if ($GLOBALS['portal_onsite_two_enable'] && $GLOBALS['portal_onsite_two_address']) {
                $portalStatus = sqlQuery("SELECT allow_patient_portal FROM patient_data WHERE pid=?", array($pid));
                if ($portalStatus['allow_patient_portal'] == 'YES') {
                    $portalLogin = sqlQuery("SELECT pid FROM `patient_access_onsite` WHERE `pid`=?", array($pid));?>
                    <?php $display_class = (empty($portalLogin)) ? "btn-save" : "btn-undo"; ?>
                    <a class='small_modal btn btn-secondary btn-sm <?php echo attr($display_class); ?>'
                        href='<?php echo attr($portal_login_href); ?>?patient=<?php echo attr_url($pid);?>'
                        onclick='top.restoreSession()'>
                        <?php $display = (empty($portalLogin)) ? xl('Create Onsite Portal Credentials') : xl('Reset Onsite Portal Credentials'); ?>
                        <?php echo text($display); ?>
                    </a>

                    <?php
                } else {
                    $portalUserSetting = false;
                } // allow patient portal
            } // Onsite Patient Portal
            if (!($portalUserSetting)) { // Show that the patient has not authorized portal access ?>
                <p>
                    <i class="fa fa-exclamation-circle oe-text-orange"></i> <?php echo xlt('Patient has not authorized the Patient Portal.');?>
                </p>
                <?php
            }
            //Patient Portal
            if ($GLOBALS['erx_enable']) { ?>
                <div id='accountstatus'></div>
                <?php
            } ?>
            </div>

    </div>
</div>
