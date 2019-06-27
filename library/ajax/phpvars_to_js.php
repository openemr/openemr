<?php
/**
 * import php variables to js
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../../interface/globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

// @todo make class move to controllers add route
$scope = isset($_REQUEST['scope']) ? $_REQUEST['scope'] : 'all';
$import = [];
if ($scope == 'globals' || $scope == 'all' || $scope == 'top') {
    $import['globals']['webroot'] = $GLOBALS['webroot'];
    $import['globals']['pid'] = $GLOBALS['pid'];
    $import['globals']['encounter'] = $GLOBALS['encounter'];
    $import['globals']['v_js_includes'] = $GLOBALS['v_js_includes'];
}
if ($scope == 'xl' || $scope == 'all') {
}
if ($import === []) {
    $custom['custom_template']['templatesWarn'] = xlt("These templates are text only and will not render any other formatting other than pure text.");
    $custom['custom_template']['templatesWarn'] .= xlt("You may still use formatting if template is also used in Nation Notes however, pure text will still render here.") . "<br><br>";
    $custom['custom_template']['templatesWarn'] .= xlt("Click Got it icon to dismiss this alert forever.");
    $custom['custom_template']['title'] = xlt("Custom Templates");
    $custom['alert']['gotIt'] = xlt("Got It");
    $custom['alert']['title'] = xlt("Alert");
    $custom['alert']['dismiss'] = xlt("Dismiss");

    $import[$scope] = $custom[$scope];
}
$error = json_last_error_msg();
$import['error'] = $error ? $error : '';

echo js_escape($import);
exit();
