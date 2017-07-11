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
use OpenEMR\Core\Header;

?>
<html>
<head>
<title><?php echo xlt("Phone Call List"); ?></title>
<?php Header::setupHeader(); ?>
</head>
<body class="body_top">
    <header>
        <h1>
            <?php echo xlt('Batch Communication Tool'); ?>
            <small><?php echo xlt('Phone Call List report'); ?></small>
        </h1>
    </header>
    <main class="container">
        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped table-bordered">
                <thead>
                    <?php'e'
                    foreach ([xlt('Name'),xlt('DOB'),xlt('Home'),xlt('Work'),xlt('Contact'),xlt('Cell')] as $header) {
                        echo "<th>$header</th>";
                    }
                    ?>
                </thead>
                <?php
                while ($row = sqlFetchArray($res)) {
                    echo("<tr><td>${row['title']} ");
                    echo("${row['fname']} ");
                    echo("${row['lname']} </td>");
                    echo("<td>${row['DOB']} </td>");
                    echo("<td>${row['phone_home']} </td>");
                    echo("<td>${row['phone_biz']} </td>");
                    echo("<td>${row['phone_contact']} </td>");
                    echo("<td>${row['phone_cell']} </td></tr>\n");
                }
                ?>
                </table>
            </div>
        </div>
    </main>
</body>
</html>