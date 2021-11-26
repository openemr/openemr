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
use OpenEMR\Common\Twig\TwigContainer;

$url_webroot = $GLOBALS['webroot'];
$portal_login_href = $url_webroot . "/interface/patient_file/summary/create_portallogin.php";

$twigContainer = new TwigContainer();
$t = $twigContainer->getTwig();

function deceasedDays($days_deceased)
{
    $deceased_days = intval($days_deceased['days_deceased'] ?? '');
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

    if (strlen($days_deceased['date_deceased'] ?? '') > 10 && $GLOBALS['date_display_format'] < 1) {
        $deceased_date = substr($days_deceased['date_deceased'], 0, 10);
    } else {
        $deceased_date = oeFormatShortDate($days_deceased['date_deceased'] ?? '');
    }

    return xlt("Deceased") . " - " . text($deceased_date) . " (" . text($num_of_days) . ")";
}

function portalAuthorized($pid)
{
    if (!$GLOBALS['portal_onsite_two_enable'] && !$GLOBALS['portal_onsite_two_address']) {
        return false;
    }

    $return = [
        'allowed' => false,
        'created' => false,
    ];

    $portalStatus = sqlQuery("SELECT allow_patient_portal FROM patient_data WHERE pid = ?", [$pid]);
    if ($portalStatus['allow_patient_portal'] == 'YES') {
        $return['allowed'] = true;
        $portalLogin = sqlQuery("SELECT pid FROM `patient_access_onsite` WHERE `pid`=?", [$pid]);
        if ($portalLogin) {
            $return['created'] = false;
        }
        return $return;
    }
}

$deceased = is_patient_deceased($pid);

$viewArgs = [
    'pageHeading' => $oemr_ui->pageHeading(),
    'isDeceased' => ($deceased > 0) ? true : false,
    'deceasedDays' => deceasedDays($deceased),
    'isAdmin' => AclMain::aclCheckCore('admin', 'super'),
    'allowPatientDelete' => $GLOBALS['allow_pat_delete'],
    'urlWebRoot' => $url_webroot,
    'pid' => $pid,
    'csrf' => CsrfUtils::collectCsrfToken(),
    'erxEnable' => $GLOBALS['erx_enable'],
    'portalAuthorized' => portalAuthorized($pid),
    'portalLoginHref' => $portal_login_href,
];

echo $t->render('patient/dashboard_header.html.twig', $viewArgs);
