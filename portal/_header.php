<?php
/**
 *
 * Copyright (C) 2016-2018 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */

use OpenEMR\Core\Header;

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?php echo xlt('OpenEMR Portal'); ?> | <?php echo xlt('Home'); ?></title>
<meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
<meta name="description" content="Developed By sjpadgett@gmail.com">

    <?php Header::setupHeader(['no_main-theme', 'datetime-picker', 'jquery-ui', 'jquery-ui-sunny', 'emodal']); ?>

<script type="text/javascript" src="../interface/main/tabs/js/dialog_utils.js?v=<?php echo $v_js_includes; ?>"></script>
<link href="assets/css/style.css?v=<?php echo $v_js_includes; ?>" rel="stylesheet" type="text/css" />
<link href="sign/css/signer.css?v=<?php echo $v_js_includes; ?>" rel="stylesheet" type="text/css" />
<link href="sign/assets/signpad.css?v=<?php echo $v_js_includes; ?>" rel="stylesheet">

<script src="sign/assets/signpad.js?v=<?php echo $v_js_includes; ?>" type="text/javascript"></script>
<script src="sign/assets/signer.js?v=<?php echo $v_js_includes; ?>" type="text/javascript"></script>
    <script type="text/javascript">
        var tab_mode = true; // for dialogs
        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
    </script>
</head>
<body class="skin-blue fixed">
    <header class="header">
        <a href="home.php" class="logo"><img src='<?php echo $GLOBALS['images_static_relative']; ?>/logo-full-con.png'/></a>
        <nav class="navbar navbar-static-top" role="navigation">
            <!-- Sidebar toggle button-->
            <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas"
                role="button"> <span class="sr-only"><?php echo xlt('Toggle navigation'); ?></span> <span
                class="icon-bar"></span> <span class="icon-bar"></span> <span
                class="icon-bar"></span>
            </a>
            <div class="navbar-right">
                <ul class="nav navbar-nav">
                    <li class="dropdown messages-menu"><a href="#"
                        class="dropdown-toggle" data-toggle="dropdown"> <i
                            class="fa fa-envelope"></i> <span class="label label-success"> <?php echo text($newcnt); ?></span>
                    </a>
                        <ul class="dropdown-menu">
                            <li class="header"><?php echo xlt('You have'); ?> <?php echo text($newcnt); ?> <?php echo xlt('new messages'); ?></li>
                            <li>
                                <!-- inner menu: contains the actual data -->
                                <ul class="menu">
                                <?php
                                foreach ($msgs as $i) {
                                    if ($i['message_status']=='New') {
                                        echo "<li><a href='" . $GLOBALS['web_root'] . "/portal/messaging/messages.php'><h4>" . text($i['title']) . "</h4></a></li>";
                                    }
                                }
                                ?>
                                </ul>
                            </li>
                            <li class="footer"><a href="<?php echo $GLOBALS['web_root']; ?>/portal/messaging/messages.php"><?php echo xlt('See All Messages'); ?></a></li>
                        </ul></li>

                    <li class="dropdown user user-menu"><a href="#"
                        class="dropdown-toggle" data-toggle="dropdown"> <i
                            class="fa fa-user"></i> <span><?php echo text($result['fname']." ".$result['lname']); ?>
                                <i class="caret"></i></span>
                    </a>
                        <ul class="dropdown-menu dropdown-custom dropdown-menu-right">
                            <li class="dropdown-header text-center"><?php echo xlt('Account'); ?></li>
                            <li><a href="<?php echo $GLOBALS['web_root']; ?>/portal/messaging/messages.php"> <i class="fa fa-envelope-o fa-fw pull-right"></i>
                                    <span class="badge badge-danger pull-right"> <?php echo text($msgcnt); ?></span> <?php echo xlt('Messages'); ?></a></li>
                            <li class="divider"></li>
                            <li>
                            <?php if ($GLOBALS['allow_portal_chat']) { ?>
                                <a href="<?php echo $GLOBALS['web_root']; ?>/portal/messaging/secure_chat.php?fullscreen=true"> <i class="fa fa-user fa-fw pull-right"></i><?php echo xlt('Chat'); ?></a>
                                <?php } ?>
                                <a href="#openSignModal" data-toggle="modal" data-backdrop="true" data-target="#openSignModal"> <i class="fa fa-cog fa-fw pull-right"></i> <?php echo xlt('Settings'); ?></a></li>

                            <li class="divider"></li>

                            <li><a href="logout.php"><i class="fa fa-ban fa-fw pull-right"></i>
                                    <?php echo xlt('Logout'); ?></a></li>
                        </ul></li>
                </ul>
            </div>
        </nav>
    </header>
    <div class="wrapper row-offcanvas row-offcanvas-left">
        <!-- Left side column. contains the logo and sidebar -->
        <aside class="left-side sidebar-offcanvas">
            <!-- sidebar: style can be found in sidebar.less -->
            <section class="sidebar">
                <!-- Sidebar user panel -->
                <div class="user-panel">
                    <div class="pull-left image">
                        <i class="fa fa-user"></i>
                    </div>
                    <div class="pull-left info">
                        <p><?php echo xlt('Welcome') . ' ' . text($result['fname']." ".$result['lname']); ?></p>
                        <a href="#"><i class="fa fa-circle text-success"></i> <?php echo xlt('Online'); ?></a>
                    </div>
                </div>
                <ul class="nav  nav-pills nav-stacked" style='font-color:#fff;'><!-- css class was sidebar-menu -->
                    <li data-toggle="pill"><a href="#profilepanel" data-toggle="collapse"
                        data-parent="#panelgroup"> <i class="fa fa-calendar-o"></i> <span><?php echo xlt('Profile'); ?></span>
                    </a></li>
                    <li data-toggle="pill"><a href="#lists" data-toggle="collapse"
                        data-parent="#panelgroup"> <i class="fa fa-list"></i> <span><?php echo xlt('Lists'); ?></span>
                    </a></li>
                    <li><a href="<?php echo $GLOBALS['web_root']; ?>/portal/patient/onsitedocuments?pid=<?php echo attr($pid); ?>"> <i class="fa fa-gavel"></i> <span><?php echo xlt('Patient Documents'); ?></span>
                    </a></li>
                    <?php if ($GLOBALS['allow_portal_appointments']) { ?>
                        <li data-toggle="pill"><a href="#appointmentpanel" data-toggle="collapse"
                            data-parent="#panelgroup"> <i class="fa fa-calendar-o"></i> <span><?php echo xlt("Appointment"); ?></span>
                    </a></li>
                    <?php } ?>
                    <?php if ($GLOBALS['portal_two_ledger'] && $GLOBALS['portal_two_payments']) { ?>
                        <li class="dropdown accounting-menu"><a href="#"
                            class="dropdown-toggle" data-toggle="dropdown"> <i
                                class="fa fa-book"></i> <span><?php echo xlt('Accountings'); ?></span>
                        </a>
                            <ul class="dropdown-menu">
                                <?php if ($GLOBALS['portal_two_ledger']) { ?>
                                    <li data-toggle="pill"><a href="#ledgerpanel" data-toggle="collapse"
                                        data-parent="#panelgroup"> <i class="fa fa-folder-open"></i> <span><?php echo xlt('Ledger'); ?></span>
                                    </a></li>
                                <?php } ?>
                                <?php if ($GLOBALS['portal_two_payments']) { ?>
                                    <li data-toggle="pill"><a href="#paymentpanel" data-toggle="collapse"
                                        data-parent="#panelgroup"> <i class="fa fa-credit-card"></i> <span><?php echo xlt('Make Payment'); ?></span>
                                    </a></li>
                                <?php } ?>
                             </ul></li>
                    <?php } ?>
                    <li class="dropdown reporting-menu"><a href="#"
                        class="dropdown-toggle" data-toggle="dropdown"> <i class="fa fa-calendar"></i> <span><?php echo xlt('Reports'); ?></span></a>
                        <ul class="dropdown-menu">
                            <?php if ($GLOBALS['ccda_alt_service_enable'] > 1) { ?>
                                <li><a id="callccda" href="<?php echo $GLOBALS['web_root']; ?>/ccdaservice/ccda_gateway.php?action=startandrun">
                                    <i class="fa fa-envelope" aria-hidden="true"></i><span><?php echo xlt('View CCD'); ?></span></a></li>
                            <?php } ?>
                            <?php if (!empty($GLOBALS['portal_onsite_document_download'])) { ?>
                                <li data-toggle="pill"><a href="#reportpanel" data-toggle="collapse"
                                    data-parent="#panelgroup"> <i class="fa fa-folder-open"></i> <span><?php echo xlt('Report Content'); ?></span></a></li>

                                <li data-toggle="pill"><a href="#downloadpanel" data-toggle="collapse"
                                    data-parent="#panelgroup"> <i class="fa fa-download"></i> <span><?php echo xlt('Download Lab Documents'); ?></span></a></li>
                            <?php } ?>
                        </ul></li>
                    <li><a href="<?php echo $GLOBALS['web_root']; ?>/portal/messaging/messages.php"><i class="fa fa-envelope" aria-hidden="true"></i>
                            <span><?php echo xlt('Secure Messaging'); ?></span>
                    </a></li>
                    <?php if ($GLOBALS['allow_portal_chat']) { ?>
                        <li data-toggle="pill"><a href="#messagespanel" data-toggle="collapse"
                            data-parent="#panelgroup"> <i class="fa fa-envelope"></i> <span><?php echo xlt("Secure Chat"); ?></span>
                    </a></li>
                    <?php } ?>
                    <li data-toggle="pill"><a href="#openSignModal" data-toggle="modal" > <i
                            class="fa fa-sign-in"></i><span><?php echo xlt('Signature on File'); ?></span>
                    </a></li>
                    <li><a href="logout.php"><i class="fa fa-ban fa-fw"></i> <span><?php echo xlt('Logout'); ?></span></a></li>
                </ul>
            </section>
            <!-- /.sidebar -->
        </aside>
