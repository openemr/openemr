<?php
/**
 * Patient Portal
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$this->assign('title', xlt("Portal Dashboard") . " | " . xlt("Home"));
$this->assign('nav', 'home');
$this->display('_ProviderHeader.tpl.php');
echo "<script>var cpid='" . attr($this->cpid) . "';var cuser='" . attr($this->cuser) . "';var webRoot='" . $GLOBALS['web_root'] . "';</script>";
?>
<script>
    $LAB.script("../sign/assets/signature_pad.umd.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait(function () {
        $(function () {
            console.log('*** Provider Template Load Done ***');
        });
    });
</script>
<div class="modal fade" id="formdialog" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="background:white">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo xlt('About Portal Dashboard') ?></h4>
            </div>
        </div>
        <div class="modal-body">
            <div><span><?php echo xlt('Please see forum and wiki'); ?></span></div>
        </div>
        <div class="modal-footer">
            <button id="okButton" data-dismiss="modal" class="btn btn-secondary"><?php echo xlt('Close...') ?></button>
        </div>
    </div>
</div>
<div class="container bg-info">
    <div class='well'>
    <div class="jumbotron text-center">
        <h3>
            <?php echo xlt('Portal Dashboard') ?><i class="fa fa-user-md" style="font-size:60px;color:red"></i>
        </h3>
        <p>
        <a class="btn btn-info" data-toggle="modal"
            data-target="#formdialog" href="#"><?php echo xlt('Tell me more') ?> »</a></p>
    </div>
</div>
<div class='well'>
    <div class="row">
        <div class="col-sm-3 col-md-3">
            <h4>
                <i class="icon-cogs"></i><?php echo xlt('Patient Document Templates') ?>
            </h4>
            <a class="btn btn-success btn-sm" href="<?php echo $GLOBALS['web_root'];?>/portal/import_template_ui.php"><?php echo xlt('Manage Templates') ?> »</a>
        </div>
        <div class="col-sm-3 col-md-3">
            <h4>
                <i class="icon-th"></i><?php echo xlt('Audit Changes') ?>
            </h4>
            <a class="btn btn-success btn-sm" href="<?php echo $GLOBALS['web_root'];?>/portal/patient/onsiteactivityviews"><?php echo xlt('Review Audits') ?> »</a>
        </div>
        <div class="col-sm-3 col-md-3">
            <h4>
                <i class="icon-cogs"></i><?php echo xlt('Patient Mail') ?>
            </h4>
            <a class="btn btn-success btn-sm" href="<?php echo $GLOBALS['web_root'];?>/portal/messaging/messages.php"><?php echo xlt('Mail') ?> »</a>
        </div>
        <div class="col-sm-3 col-md-3">
            <h4>
                <i class="icon-cogs"></i><?php echo xlt('Patient Chat') ?>
            </h4>
            <a class="btn btn-success btn-sm" href="<?php echo $GLOBALS['web_root'];?>/portal/messaging/secure_chat.php"><?php echo xlt('Messaging') ?> »</a>
        </div>
        <div class="col-sm-3 col-md-3">
            <h4>
                <i class="icon-signin"></i><?php echo xlt('User Signature') ?>
            </h4>
            <p>
            <a data-type="admin-signature" class="btn btn-primary btn-sm" href="#openSignModal" data-toggle="modal" data-backdrop="true" data-target="#openSignModal">
             <span><?php echo xlt('Signature on File') . '  '; ?></span><i  class="fa fa-sign-in"></i></a>
            </p>
        </div>
    </div>
</div>
</div>
<!-- /container -->
<?php
$this->display('_Footer.tpl.php');
?>
