<?php

/**
 * Batch list processor, included from batchcom
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  cfapress
 * @author  Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @copyright Copyright (c) 2008 cfapress
 * @copyright Copyright (c) 2017 Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @todo menu for fields could be added in the future
 */

require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

?>
<html>
<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt("Phone Call List"); ?></title>
</head>
<body class="body_top container">
    <header class="row">
        <?php require_once("batch_navigation.php");?>
        <h1 class="col-md-12">
            <a href="batchcom.php"><?php echo xlt('Batch Communication Tool'); ?></a>
            <small><?php echo xlt('Phone Call List report'); ?></small>
        </h1>
    </header>
    <main class="row mx-4">
        <div class="col-md-12">
            <table class="table table-striped table-bordered">
                <thead>
                    <?php
                    foreach ([xlt('Name'),xlt('DOB'),xlt('Home'),xlt('Work'),xlt('Contact'),xlt('Cell')] as $header) {
                        echo "<th>$header</th>";
                    }
                    ?>
                </thead>
                <tbody>
                    <?php
                    while ($row = sqlFetchArray($res)) {
                        echo "<tr><td>";
                        echo text($row['title']) . ' ' . text($row['fname']) . ' ' . text($row['lname']);
                        echo "</td><td>";
                        echo text($row['DOB']);
                        echo "</td><td>";
                        echo text($row['phone_home']);
                        echo "</td><td>";
                        echo text($row['phone_biz']);
                        echo "</td><td>";
                        echo text($row['phone_contact']);
                        echo "</td><td>";
                        echo text($row['phone_cell']);
                        echo "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
