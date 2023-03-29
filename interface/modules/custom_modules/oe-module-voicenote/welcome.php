<?php

use OpenEMR\Core\Header;

require_once dirname(__FILE__, 4) . "/globals.php";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo xlt("Welcome to the module"); ?></title>
    <?php echo Header::setupHeader(); ?>
    <style>
        .note {
            color: #942a25;
            font-size: medium;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div>
        <h1><?php echo xlt("Voicenote"); ?></h1>
    </div>
</div>

</body>
</html>