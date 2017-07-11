<?php
/*    
    batch list processor, included from batchcom 
*/

// create a list for phone calls
// menu for fields could be added in the future

include_once("../globals.php");
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
            <?php xlt('Batch Communication Tool', 'e'); ?>
            <small><?php xlt('Phone Call List report', 'e'); ?></small>
        </h1>
    </header>
    <main class="container">
        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped table-bordered">
                <thead>
                    <?php
                    foreach ([xl('Name'),xl('DOB'),xl('Home'),xl('Work'),xl('Contact'),xl('Cell')] as $header) {
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