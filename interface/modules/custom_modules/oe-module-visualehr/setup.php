<?php

/*
 *
 * @package     OpenEMR Telehealth Module
 * @link        https://visualehr.com
 *
 * @author      Kofi Appiah <kkappiah@medsov.com>
 * @copyright   Copyright (c) 2022 Visual EHR <visualehr.com>
 * @license     GNU General Public License 3
 *
 */

require_once "../../../globals.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('admin', 'manage_modules')) {
    echo xlt('Not Authorized');
    exit;
}

?>
<!DOCTYPE html>
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
<head style="text-align: center;">
    <?php Header::setupHeader(); ?>
    <meta charset="utf-8" />
    <title>VisualEHR</title>
</head>
<body style="background-color: lightgray">

<div class="container">
    <div class="page-content">
        <p>
            Welcome to Visual EHR
        </p>
    </div>
</div>
</body>
<script>

</script>
</html>

