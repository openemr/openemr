<?php
/**
 * Ajax script to connect to offsite patient portal.
 *
 * Currently supports collecting the vpn connection package and
 * can be expanded to support other features in the future.
 *
 * Copyright (C) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
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
 * @author  Eldho Chacko <eldho@zhservices.com>
 * @author  Vinish K <vinish@zhservices.com>
 * @link    http://www.open-emr.org
 */


require_once(dirname(__FILE__)."/../../interface/globals.php");
require_once("$srcdir/acl.inc");
require_once(dirname(__FILE__)."/../../myportal/soap_service/portal_connectivity.php");

if ($_POST['action'] == 'check_file' && acl_check('admin', 'super')) {
    $client = portal_connection();
    $error_message = '';
    try {
        $response = $client->getPortalConnectionFiles($credentials);
    } catch (SoapFault $e) {
        error_log('SoapFault Error');
        $error_message = xlt('Patient Portal connectivity issue');
    } catch (Exception $e) {
        error_log('Exception Error');
        $error_message = xlt('Patient Portal connectivity issue');
    }

    if ($response['status'] == 1) {
        if ($response['value'] != '') {
            echo "OK";
        } else {
            echo $error_message;
        }
    } else {
        echo xlt('Offsite Portal web Service Failed').": ".text($response['value']);
    }
}
