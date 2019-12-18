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
?>
<head>
    <link rel="stylesheet" href="<?php css_src('rules.css') ?>" type="text/css">


    <meta charset="utf-8">
    <!-- Viewport Meta Tag -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="<?php echo $GLOBALS['web_root']; ?>/library/css/bootstrap_navbar.css?v=<?php echo $v_js_includes; ?>" type="text/css">
    <link rel="stylesheet" href="<?php css_src('rules.css') ?>?v=<?php echo $v_js_includes; ?>" type="text/css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['web_root']; ?>/public/assets/bootstrap/BS4/css/bootstrap.css">
    <?php Header::setupHeader([ 'jquery', 'jquery-ui', 'jquery-ui-redmond','datetime-picker', 'dialog' ,'jscolor','no-bootstrap', 'fontawesome', 'modals', 'popper' ]); ?>
    <script src="<?php echo $GLOBALS['web_root']; ?>/public/assets/bootstrap/BS4/js/bootstrap.bundle.js.map"></script>
    

</head>

<body class='body_top'>
<div id="hide_nav" style="<?php if ($setting_bootstrap_submenu == 'hide') {
    echo "display:none;"; } ?>">
    <nav id="navbar_oe" class="navbar-expand-sm bgcolor2 navbar-fixed-top navbar-custom navbar-bright navbar-inner"
         data-role="page banner navigation">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="container-fluid">
            <div class="navbar-header brand">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#oer-navbar-collapse-1">
                    <span class="sr-only"><?php echo xlt("Toggle navigation"); ?></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
            <div class="navbar-collapse collapse" id="oer-navbar-collapse-1">
                <ul class="navbar-nav">
                    <?php
                        if ($GLOBALS['medex_enable'] == '1') {
                            ?>
                            <li id="menu_PREFERENCES"  name="menu_all_plans" class="">
                                        <a href="<?php echo $GLOBALS['web_root']; ?>/interface/super/rules/index.php?action=browse!plans_config"><?php echo xlt("Care Plans"); ?></a>
                            </li>
                            <?php
                        }
                    ?>

                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" id="menu_dropdown_msg" role="button" aria-expanded="true"><?php echo xlt("Clinical Reminders"); ?> </a>
                        <ul class="bgcolor2 dropdown-menu" role="menu">
                            <li id="menu_new_rule"> <a href="<?php echo $GLOBALS['web_root']; ?>/interface/super/rules/index.php?action=edit!summary"> <?php echo xlt("New Clinical Reminder"); ?></a></li>

                            <li class="divider"><hr /></li>

                            <li id="menu_all_rule"><a href="<?php echo $GLOBALS['web_root']; ?>/interface/super/rules/index.php?action=alerts!listactmgr" onclick="top.restoreSession();"> <?php echo xlt("Reminders Manager"); ?></a></li>
                        </ul>
                    </li>
                    
                   <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" id="menu_dropdown_help" role="button" aria-expanded="true"><?php echo xlt("Help"); ?> </a>
                        <ul class="bgcolor2 dropdown-menu" role="menu">
                            <li id="menu_new_help"> <a href="<?php echo $GLOBALS['web_root']; ?>/"> <?php echo xlt("Nothing here yet..."); ?></a></li>

                            <li class="divider"><hr /></li>

                            <li id="menu_all_help"><a href="<?php echo $GLOBALS['web_root']; ?>/interface/super/rules/index.php" onclick="top.restoreSession();"> <?php echo xlt("Just an example..."); ?></a></li>
                        </ul>
                    </li>
                </ul>
            </div><!-- //navbar-collapse -->
        </div>
    </nav>
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
<br /><br />
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
