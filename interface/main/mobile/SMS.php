<?php
    /**
     *  /interface/main/mobile/SMS.php
     *
     *  Live SMS interface for OpenEMR via MedEx
     *
     * Copyright (C) 2018 Raymond Magauran <magauran@MedExBank.com>
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
     * @author Ray Magauran <magauran@MedExBank.com>
     * @link http://www.open-emr.org
     * @copyright Copyright (c) 2018 MedEx <magauran@MedExBank.com>
     * @license https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
     */
require_once('../../globals.php');
require_once "$srcdir/patient.inc";
require_once "$srcdir/options.inc.php";
require_once $GLOBALS['srcdir']."/../vendor/mobiledetect/mobiledetectlib/Mobile_Detect.php";
require_once("m_functions.php");
require_once("$srcdir/MedEx/API.php");

use OpenEMR\Core\Header;

$MedEx = new MedExApi\MedEx('medexbank.com');

$detect = new Mobile_Detect;
$device_type = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
$script_version = $detect->getScriptVersion();

$desktop ="";
$categories = array();
$display="cam";
$doc =array();

if (!empty($_GET['desktop'])) {
    $desktop = $_GET['desktop'];
}

// If “Go to full website” link is clicked, redirect mobile user to main website
if (!empty($_SESSION['desktop']) || ($device_type == 'computer')) {
    $desktop_url = $GLOBALS['webroot']."/interface/main/tabs/main.php";
    header("Location:" . $desktop_url);
}

if ($GLOBALS['medex_enable'] == '1') {
    $logged_in = $MedEx->login();
    $MedEx->display->SMS_bot($logged_in);
    exit;
} else {
}
