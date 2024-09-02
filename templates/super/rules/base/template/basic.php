<?php

// Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

use OpenEMR\Core\Header;

?>
<!DOCTYPE html>
<html>
<head>
    <!-- TODO: FIX No Bootstrap header !-->
    <?php Header::setupHeader(['opener']); ?>

    <?php if ($_SESSION['language_direction'] == "rtl") { ?>
        <link rel="stylesheet" href="<?php echo $GLOBALS['themes_static_relative']; ?>/misc/rtl_rules.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" />
    <?php } else { ?>
        <link rel="stylesheet" href="<?php echo $GLOBALS['themes_static_relative']; ?>/misc/rules.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" />
    <?php } ?>
</head>

<body class='body_top'>
    <div class="container-xl">
        <?php
        if (file_exists($viewBean->_view_body)) {
            require_once($viewBean->_view_body);
        }
        ?>
    </div>
</body>
</html>
