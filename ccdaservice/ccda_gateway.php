<?php
/**
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
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
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */
//authencate for portal or main- never know where it gets used
session_start();
if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    $pid = $_SESSION['pid'];
    $ignoreAuth = true;
    require_once(dirname(__FILE__) . "/../interface/globals.php");
    define('IS_DASHBOARD', false);
    define('IS_PORTAL', $_SESSION['pid']);
} else {
    session_destroy();
    $ignoreAuth = false;
    require_once(dirname(__FILE__) . "/../interface/globals.php");
    if (!isset($_SESSION['authUserID'])) {
        $landingpage = "index.php";
        header('Location: ' . $landingpage);
        exit;
    }

    define('IS_DASHBOARD', $_SESSION['authUserID']);
    define('IS_PORTAL', false);
}

// give me something to do.
$dowhat = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
if ($dowhat && $GLOBALS['ccda_alt_service_enable'] > 0) {
    if (!checkService()) { // woops, try again
        if (!checkService()) { // thats 10 seconds of wasted time.
            die("Document service start failed. Click back to return home."); // nuts! give up
        }
    }
} else {
    // maybe next time
    die("Cda generation service turned off: Verify in Administration->Globals! Click back to return home."); // Die an honorable death!!
}

//eventually below will qualify what document to fetch
$parameterArray = array();
$parameterArray ['encounter'];
$parameterArray ['combination'] = $pid;
$parameterArray ['components']; // = 'progress_note|consultation_note|continuity_care_document|diagnostic_image_reporting|discharge_summary|history_physical_note|operative_note|procedure_note|unstructured_document';
$parameterArray ['sections']; // = 'allergies|medications|problems|immunizations|procedures|results|plan_of_care|vitals|social_history|encounters|functional_status|referral|instructions';
$parameterArray ['downloadccda'] = 1;
$parameterArray ['sent_by'];
$parameterArray ['send'];
$parameterArray ['view'] = 1;
$parameterArray ['recipients'] = 'patient'; // emr_direct or hie else if not set $_SESSION['authUserID']
$parameterArray [0][6] = $_SESSION ['portal_username']; // set to an onsite portal user

if (!isset($_SESSION['site_id'])) {
    $_SESSION ['site_id'] = 'default';
}

$server_url = $_SERVER['HTTP_HOST'] . $GLOBALS['webroot'];
// CCM returns entire cda with service doing templates
$ccdaxml = portalccdafetching($pid, $server_url, $parameterArray);
// disposal decisions will be here.
$h = '';
if (!$parameterArray ['view']) {
    header('Content-Type: application/xml');
} else {
    $h = '<a href="./../portal/home.php" </a><button style="color: red; background: white;" >' . xlt("Return Home") . '</button><br>';
}

print_r($h . $ccdaxml . $h);
//service_shutdown(1); //In ssmanager  0= terminate and disable 1 = soft=terminate but still active w/no restart, > 1 just restart based on B.S timer
exit;

function portalccdafetching($pid, $server_url, $parameterArray)
{
    session_write_close();
    $site_id = $_SESSION['site_id'];
    $parameters = http_build_query($parameterArray); // future use
    try {
        $ch = curl_init();
        $url = $server_url . "/interface/modules/zend_modules/public/encounterccdadispatch/index?site=$site_id&me=" . session_id() . "&param=1&view=1&combination=$pid&recipient=patient";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0); // set true for look see
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie");
        curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie");
        //curl_setopt ($ch, CURLOPT_COOKIE, 'XDEBUG_SESSION=1'); // break on first line in public/index.php - uncomment and start any xdebug session and fetch a ccda in app.
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch) or die(curl_error($ch));
        curl_close($ch);
    } catch (Exception $e) {
        return false;
    }

    return $result;
}

function checkService($ip = "localhost", $port = '6661')
{
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if ($socket === false) {
        throw new Exception("Socket Creation Failed");
    }

    // Connect to the node server.
    $result = socket_connect($socket, $ip, $port);
    if ($result === false) {
        $path = $GLOBALS['fileroot'] . "/ccdaservice";
        if (IS_WINDOWS) {
            $cmd = "node " . $path . "/serveccda.js";
            pclose(popen("start /B " . $cmd, "r"));
        } else {
            $cmd = "nodejs " . $path . "/serveccda.js";
            exec($cmd . " > /dev/null &");
        }
        sleep(2); // give cpu a rest
        $result = socket_connect($socket, $ip, $port);
        if ($result === false) { // hmm something is amist with service.
            throw new Exception("Connection Failed");
        }
    }
    socket_close($socket);
    unset($socket);
    return true;
}

return 0;
