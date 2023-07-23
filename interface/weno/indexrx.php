<?php

/*
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Rx\Weno\Container;

//ensure user has proper access
if (!AclMain::aclCheckCore('patients', 'rx')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Weno eRx")]);
    exit;
}

$container = new Container();

$wenoProperties = $container->getTransmitproperties();
$provider_info = $wenoProperties->getProviderEmail();
$urlParam = $wenoProperties->cipherpayload();          //lets encrypt the data
$logsync = $container->getLogproperties();
$logsync->logSync();
$newRxUrl = "https://online.wenoexchange.com/en/NewRx/ComposeRx?useremail=";
if ($urlParam == 'error') {   //check to make sure there were no errors
    echo xlt("Cipher failure check encryption key");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8">
 <title><?php echo xlt('Weno eRx') ?></title>
</head>
<body >
<?php
    //**warning** do not add urlencode to  $provider_info['email']
    $urlOut = $newRxUrl . $provider_info['email'] . "&data=" . urlencode($urlParam);
    //echo $urlOut; die;  //troubleshooting
    header("Location: " . $urlOut);

?>
</body>
</html>

