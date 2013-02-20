<?php
/**
 * api/classes.php contain files to be included
 * 
 * this file contain all files and information to be included.
 * 
 * Copyright (C) 2012 Karl Englund <karl@mastermobileproducts.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-3.0.html>;.
 *
 * @package OpenEMR
 * @author  Karl Englund <karl@mastermobileproducts.com>
 * @link    http://www.open-emr.org
 */
$fake_register_globals=false;
$sanitize_all_escapes=true;

include_once(dirname(dirname(__FILE__)) . "/interface/globals.php");

if(!$GLOBALS['rest_api_server']){
    echo "<openemr>
            <status>-1</status>
            <reason>Please check the REST API server settings in Administration/Globals/Connectors</reason>
        </openemr>";
    exit;
}

require_once("$srcdir/pid.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/lists.inc");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/log.inc");
require_once("$srcdir/appointments.inc.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/classes/class.phpmailer.php");

require_once("$srcdir/htmlspecialchars.inc.php");	
require_once("$srcdir/formdata.inc.php");
         

include("includes/class.arraytoxml.php");


$site = 'default';

$sitesDir = dirname(dirname(__FILE__)) . "/sites/";


$url = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
if ($_SERVER["SERVER_PORT"] != "80") {
    $url .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"].$_SERVER['REQUEST_URI'];
    $url1 = str_replace("api", '', pathinfo($url,PATHINFO_DIRNAME));
} else {
    $url .= $_SERVER["SERVER_NAME"].$_SERVER['REQUEST_URI'];
    $url1 = str_replace("api", '', pathinfo($url,PATHINFO_DIRNAME));
}

$sitesUrl = $url1 . 'sites/';
$openemrUrl = $url1;

$openemrDirName = basename(dirname(dirname(__FILE__)));


/**
 * above some variables are used in functions file
 */
include("includes/functions.php");
?>