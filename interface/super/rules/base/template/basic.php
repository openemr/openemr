<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

require_once("../../globals.php");

use OpenEMR\Core\Header;
    
    $setting_bootstrap_submenu = prevSetting('', 'setting_bootstrap_submenu', 'setting_bootstrap_submenu', ' ');
?><!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
    <!-- Viewport Meta Tag -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="<?php echo $GLOBALS['web_root']; ?>/library/css/bootstrap_navbar.css?v=<?php echo $v_js_includes; ?>" type="text/css">
    <link rel="stylesheet" href="<?php css_src('rules.css') ?>?v=<?php echo $v_js_includes; ?>" type="text/css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['web_root']; ?>/interface/super/rules/www/js/BS4/css/bootstrap.css">
    <script src="<?php echo $GLOBALS['web_root']; ?>/interface/super/rules/www/js/BS4/popper.min.js"></script>
    <?php Header::setupHeader([ 'jquery', 'jquery-ui','bootstrap', 'fontawesome', 'modals']); ?>
    <script src="<?php echo $GLOBALS['web_root']; ?>/interface/super/rules/www/js/BS4/js/bootstrap.bundle.js.map"></script>
    

</head>

<body class='body_top'>

        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="container-fluid">
           <!-- <div class="navbar-header brand bg-light">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#oer-navbar-collapse-1">
                    <span class="sr-only"><?php echo xlt("Toggle navigation"); ?></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>-->
            <div id="hide_nav" style="<?php if ($setting_bootstrap_submenu == 'hide') {
                echo "display:none;"; } ?>">
                <nav id="navbar_oe"
                     class="navbar-expand-sm bgcolor2 fixed-top navbar-fixed-top oe-pull-toward-unimportant"
                     data-role="page banner navigation">

                    <ul class="navbar-nav menuSection">
                        <li class="indent10">&nbsp; &nbsp;</li>
                        <li class="nav-item dropdown">
                            <a href="<?php echo $GLOBALS['web_root']; ?>/interface/super/rules/index.php?action=browse!plans_config"><?php echo xlt("Care Plans"); ?></a>
                        </li>
                        <li class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown" id="menu_dropdown_msg" aria-expanded="true"><?php echo xlt("Clinical Reminders"); ?> </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="<?php echo $GLOBALS['web_root']; ?>/interface/super/rules/index.php?action=alerts!listactmgr" onclick="top.restoreSession();"> <?php echo xlt("Reminders Manager"); ?></a>
                                <a class="dropdown-item" href="<?php echo $GLOBALS['web_root']; ?>/interface/super/rules/index.php?action=edit!summary" onclick="top.restoreSession();"> <?php echo xlt("New Clinical Reminder"); ?></a>
                            </div>
                        </li>
                    </ul>
                </nav><!-- //navbar-collapse -->
            </div>
        </div>
<script>
    function toggle_menu() {
        var x = document.getElementById('hide_nav');
        if (x.style.display === 'none') {
            $.post( "<?php echo $GLOBALS['webroot']."/interface/main/messages/messages.php"; ?>", {
                'setting_bootstrap_submenu' : 'show',
                success: function (data) {
                    x.style.display = 'block';
                }
            });
            
        } else {
            $.post( "<?php echo $GLOBALS['webroot']."/interface/main/messages/messages.php"; ?>", {
                'setting_bootstrap_submenu' : 'hide',
                success: function (data) {
                    x.style.display = 'none';
                }
            });
        }
        $("#patient_caret").toggleClass('fa-caret-up').toggleClass('fa-caret-down');
    }
</script>


<i class="fa fa-caret-<?php
    if ($setting_bootstrap_submenu == 'hide') {
        echo 'down';
    } else {
        echo 'up';
    } ?> menu_arrow" style="position:fixed;left:5px;top:5px;z-index:1099;" id="patient_caret" onclick='toggle_menu();' aria-hidden="true"></i>

<div class="container-fluid">
<?php
    require_once($GLOBALS["srcdir"] . "/../interface/super/rules/controllers/edit/helper/common.php");
    $rule = $viewBean->rule;
    
    if (file_exists($viewBean->_view_body)) {
    require_once($viewBean->_view_body);
}
?>
</div>
<script>
    $(function () {
        //bootstrap menu functions
        $('.dropdown').hover(function () {
            $(".dropdown").removeClass('open');
            $(this).addClass('open');
            $(this).find('.dropdown-menu').first().stop(true, true).delay(250).slideDown();
        }, function () {
            $(this).find('.dropdown-menu').first().stop(true, true).delay(250).slideUp();
            $('.dropdown').removeClass('open');
            $(this).parent().removeClass('open');
        });
        $("[class='dropdown-toggle']").hover(function () {
            $(".dropdown").removeClass('open');
            $(this).parent().addClass('open');
            $(this).find('.dropdown-menu').first().stop(true, true).delay(250).slideDown();
        });
        $('[data-toggle="popover"]').popover();
    });
</script>
</body>
</html>
