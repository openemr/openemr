<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// check if authenticated
require_once(dirname(__FILE__) . "/../../../interface/globals.php");
$thisuser = $_SESSION['authUser'];
$thisDevice = "User:" . $thisuser;

use OpenEMR\Core\Header;

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta charset="utf-8">

    <title><?php echo "oe" . xlt("Signer") ?></title>
    <?php Header::setupHeader([]); ?>

    <link href="<?php echo $GLOBALS['web_root']; ?>/portal/sign/css/signer_modal.css?v=<?php echo $v_js_includes; ?>"
          rel="stylesheet">
    <script src="<?php echo $GLOBALS['web_root']; ?>/portal/sign/assets/signature_pad.umd.js"></script>
    <script src="<?php echo $GLOBALS['web_root']; ?>/portal/sign/assets/signer_api.js?v=<?php echo $v_js_includes; ?>"></script>
    <script>
        const remoteDevice = '' + <?php echo js_escape($thisDevice) ?>;
        let currentAuth = '';
        window.name = remoteDevice;
    </script>
</head>
<body class="container">
<div class="signer-banners row">
    <div class="jumbotron">
        <h3 class="text-center"><?php echo xlt("Welcome to OpenEMR Signature Kiosk.") ?></h3>
        <h4 class="text-center"><?php echo xlt("A component of the Patient Portal.") ?></h4>
        <p class="text-center"><?php echo xlt("Once a request is received a dialog will come into view. Simply sign in the box and Authorize as your digital signature.") ?></p>
        <h4 class="text-center"><?php echo xlt("Ready and Waiting as") . " " . text($thisDevice) ?></h4>
    </div>
</div>
</body>
</html>
