<?php
/**
 *  Patient Portal
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Header;

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?php echo xlt('OpenEMR Portal'); ?> | <?php echo xlt('Home'); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="Developed By sjpadgett@gmail.com">

<?php Header::setupHeader(['no_main-theme', 'datetime-picker', 'jquery-ui', 'jquery-ui-sunny']); ?>

<script type="text/javascript" src="../interface/main/tabs/js/dialog_utils.js?v=<?php echo $v_js_includes; ?>"></script>
<link href="<?php echo $GLOBALS['web_root']; ?>/portal/assets/css/style.css?v=<?php echo $v_js_includes; ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo $GLOBALS['web_root']; ?>/portal/sign/css/signer_modal.css?v=<?php echo $v_js_includes; ?>" rel="stylesheet" type="text/css" />

<script type="text/javascript">
    var tab_mode = true;
    function restoreSession(){
        //dummy functions so the dlgopen function will work in the patient portal
        return true;
    }
    var isPortal = 1;
</script>

<script src="<?php echo $GLOBALS['web_root']; ?>/portal/sign/assets/signature_pad.umd.js?v=<?php echo $v_js_includes; ?>" type="text/javascript"></script>
<script src="<?php echo $GLOBALS['web_root']; ?>/portal/sign/assets/signer_api.js?v=<?php echo $v_js_includes; ?>" type="text/javascript"></script>

<?php if ($GLOBALS['payment_gateway'] == 'Stripe') { ?>
    <script type="text/javascript" src="https://js.stripe.com/v3/"></script>
<?php } ?>
<?php if ($GLOBALS['payment_gateway'] == 'AuthorizeNet') {
    // Must be loaded from their server
    $script = "https://jstest.authorize.net/v1/Accept.js"; // test script
    if ($GLOBALS['gateway_mode_production']) {
        $script = "https://js.authorize.net/v1/Accept.js"; // Production script
    } ?>
    <script type="text/javascript" src="<?php echo $script; ?>" charset="utf-8"></script>
<?php } ?>
</head>
<body class="skin-blue fixed">
    <header class="header">
        <a href="home.php" class="logo"><img src='<?php echo $GLOBALS['images_static_relative']; ?>/logo-full-con.png'/></a>
        <nav class="navbar navbar-expand-md sticky-top text-dark" role="navigation">
            <!-- Sidebar toggle button-->
            <button class="navbar-toggler" type="button" data-toggle="offcanvas" data-target="#pillCollapse" aria-controls="pillCollapse" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
            <ul class="nav navbar-nav">
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="newmsgs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"> <i class="fa fa-envelope"></i> <span class="badge badge-pill badge-success"><?php echo text($newcnt); ?></span></a>
                    <div class="dropdown-menu" aria-labelledby="newmsgs">
                        <h6 class="dropdown-header"><?php echo xlt('You have'); ?> <?php echo text($newcnt); ?> <?php echo xlt('new messages'); ?></h6>
                        <!-- inner menu: contains the actual data -->
                        <?php
                        foreach ($msgs as $i) {
                            if ($i['message_status']=='New') {
                                echo "<div><a class='dropdown-item' href='" . $GLOBALS['web_root'] . "/portal/messaging/messages.php'><h4>" . text($i['title']) . "</h4></a></div>";
                            }
                        }
                        ?>
                        <div><a class="dropdown-item" href="<?php echo $GLOBALS['web_root']; ?>/portal/messaging/messages.php"><?php echo xlt('See All Messages'); ?></a></div>
                    </div></li>

                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" id="profiletab" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> <i class="fa fa-user"></i> <span><?php echo text($result['fname']." ".$result['lname']); ?> <i class="caret"></i></span></a>
                    <div class="dropdown-menu" aria-labelledby="profiletab">
                        <div class="dropdown-header text-center"><?php echo xlt('Account'); ?></div>
                        <div><a class="dropdown-item" href="<?php echo $GLOBALS['web_root']; ?>/portal/messaging/messages.php"> <i class="fa fa-envelope-o fa-fw"></i> <?php echo xlt('Messages'); ?> <span class="badge badge-pill badge-danger"><?php echo text($msgcnt); ?></span></a></div>
                        <div class="dropdown-divider"></div>
                        <?php if ($GLOBALS['allow_portal_chat']) { ?>
                            <div><a class="dropdown-item" href="<?php echo $GLOBALS['web_root']; ?>/portal/messaging/secure_chat.php?fullscreen=true"> <i class="fa fa-user fa-fw"></i><?php echo xlt('Chat'); ?></a></div>
                        <?php } ?>
                        <div><a class="dropdown-item" href="javascript:changeCredentials(event)"> <i class="fa fa-cog fa-fw"></i> <?php echo xlt('Change Credentials'); ?></a></div>
                        <div class="dropdown-divider"></div>

                        <div><a class="dropdown-item" href="logout.php"><i class="fa fa-ban fa-fw"></i> <?php echo xlt('Logout'); ?></a></div>
                    </div>
                </li>
            </ul>
        </nav>
    </header>
    <div class="wrapper row-offcanvas row-offcanvas-left">
        <!-- Left side column. contains the logo and sidebar -->
        <aside class="left-side sidebar-offcanvas">
            <!-- sidebar: style can be found in sidebar.less -->
            <section class="sidebar">
                <!-- Sidebar user panel -->
                <div class="user-panel">
                    <div class="float-left image">
                        <i class="fa fa-user"></i>
                    </div>
                    <div class="float-left info">
                        <p><?php echo xlt('Welcome') . ' ' . text($result['fname']." ".$result['lname']); ?></p>
                        <a href="#"><i class="fa fa-circle text-success"></i> <?php echo xlt('Online'); ?></a>
                    </div>
                </div>
                <ul class="nav nav-pills flex-column text-dark" id="pillCollapse">
                    <!-- css class was sidebar-menu -->
                    <li class="nav-item" data-toggle="pill"><a class="nav-link" href="#profilecard" data-toggle="collapse" data-parent="#cardgroup"> <i class="fa fa-calendar-o"></i> <span><?php echo xlt('Profile'); ?></span>
                    </a></li>
                    <li class="nav-item" data-toggle="pill"><a class="nav-link" href="#lists" data-toggle="collapse" data-parent="#cardgroup"> <i class="fa fa-list"></i> <span><?php echo xlt('Lists'); ?></span>
                    </a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $GLOBALS['web_root']; ?>/portal/patient/onsitedocuments?pid=<?php echo attr_url($pid); ?>"> <i class="fa fa-gavel"></i><span><?php echo xlt('Patient Documents'); ?></span></a></li>
                    <?php if ($GLOBALS['allow_portal_appointments']) { ?>
                        <li class="nav-item" data-toggle="pill"><a class="nav-link" href="#appointmentcard" data-toggle="collapse"
                            data-parent="#cardgroup"> <i class="fa fa-calendar-o"></i> <span><?php echo xlt("Appointment"); ?></span>
                    </a></li>
                    <?php } ?>
                    <?php if ($GLOBALS['portal_two_ledger'] || $GLOBALS['portal_two_payments']) { ?>
                        <li class="nav-item dropdown accounting-menu"><a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown"> <i class="fa fa-book"></i> <span><?php echo xlt('Accountings'); ?></span></a>
                            <div class="dropdown-menu">
                                <?php if ($GLOBALS['portal_two_ledger']) { ?>
                                    <span data-toggle="pill"><a class="dropdown-item" href="#ledgercard" data-toggle="collapse" data-parent="#cardgroup"> <i class="fa fa-folder-open"></i> <span><?php echo xlt('Ledger'); ?></span></a></span>
                                <?php } ?>
                                <?php if ($GLOBALS['portal_two_payments']) { ?>
                                    <span data-toggle="pill"><a class="dropdown-item" href="#paymentcard" data-toggle="collapse" data-parent="#cardgroup"> <i class="fa fa-credit-card"></i> <span><?php echo xlt('Make Payment'); ?></span></a></span>
                                <?php } ?>
                             </div>
                        </li>
                    <?php } ?>
                    <li class="nav-item dropdown reporting-menu"><a href="#"
                        class="nav-link dropdown-toggle" data-toggle="dropdown"> <i class="fa fa-calendar"></i> <span><?php echo xlt('Reports'); ?></span></a>
                        <div class="dropdown-menu">
                            <?php if ($GLOBALS['ccda_alt_service_enable'] > 1) { ?>
                                <a class="dropdown-item" id="callccda" href="<?php echo $GLOBALS['web_root']; ?>/ccdaservice/ccda_gateway.php?action=startandrun">
                                    <i class="fa fa-envelope" aria-hidden="true"></i><span><?php echo xlt('View CCD'); ?></span></a>
                            <?php } ?>
                            <?php if (!empty($GLOBALS['portal_onsite_document_download'])) { ?>
                            <span data-toggle="pill"><a class="dropdown-item" href="#reportcard" data-toggle="collapse"
                                    data-parent="#cardgroup"> <i class="fa fa-folder-open"></i> <span><?php echo xlt('Report Content'); ?></span></a></span>

                                <span data-toggle="pill"><a class="dropdown-item" href="#downloadcard" data-toggle="collapse"
                                    data-parent="#cardgroup"> <i class="fa fa-download"></i> <span><?php echo xlt('Download Lab Documents'); ?></span></a></span>
                            <?php } ?>
                        </div>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $GLOBALS['web_root']; ?>/portal/messaging/messages.php"><i class="fa fa-envelope" aria-hidden="true"></i>
                            <span><?php echo xlt('Secure Messaging'); ?></span>
                    </a></li>
                    <?php if ($GLOBALS['allow_portal_chat']) { ?>
                        <li class="nav-item" data-toggle="pill"><a class="nav-link" href="#messagescard" data-toggle="collapse"
                            data-parent="#cardgroup"> <i class="fa fa-envelope"></i> <span><?php echo xlt("Secure Chat"); ?></span>
                    </a></li>
                    <?php } ?>
                    <li class="nav-item" data-toggle="pill"><a class="nav-link" href="#openSignModal" data-toggle="modal" data-type="patient-signature"> <i
                            class="fa fa-sign-in"></i><span><?php echo xlt('Signature on File'); ?></span>
                    </a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fa fa-ban fa-fw"></i> <span><?php echo xlt('Logout'); ?></span></a></li>
                </ul>
            </section>
            <!-- /.sidebar -->
        </aside>
