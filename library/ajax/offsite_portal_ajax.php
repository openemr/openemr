<?php
/**
 * Ajax script to connect to offsite patient portal.
 *
 * Currently supports collecting the vpn connection package and
 * can be expanded to support other features in the future.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once(dirname(__FILE__)."/../../interface/globals.php");
require_once("$srcdir/acl.inc");
require_once(dirname(__FILE__)."/../../myportal/soap_service/portal_connectivity.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

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
