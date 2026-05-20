<?php

/**
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

    require_once "../../../../globals.php";

    use OpenEMR\Common\Acl\AccessDeniedHelper;
    use OpenEMR\Common\Acl\AclMain;
    use OpenEMR\Common\Csrf\CsrfUtils;
    use OpenEMR\Core\Header;
    use OpenEMR\Modules\ClaimRevConnector\ClaimRevModuleSetup;
    use OpenEMR\Modules\ClaimRevConnector\CsrfHelper;
    use OpenEMR\Modules\ClaimRevConnector\ModuleInput;
    use OpenEMR\Modules\ClaimRevConnector\TypeCoerce;

    $tab = "setup";

    //ensure user has proper access
if (!AclMain::aclCheckCore('admin', 'manage_modules')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for admin/manage_modules: ClaimRev Connect - Setup", xl("ClaimRev Connect - Setup"));
}

$actionMessage = '';
if (ModuleInput::isPostRequest()) {
    if (!CsrfHelper::verifyCsrfToken(ModuleInput::postString('csrf_token_form'), 'ClaimRevModule')) {
        CsrfUtils::csrfNotVerified();
    }
    if (ModuleInput::postExists('deactivateSftp')) {
        ClaimRevModuleSetup::deactivateSftpService();
        $actionMessage = xlt("SFTP service has been deactivated.");
    }
    if (ModuleInput::postExists('reactivateSftp')) {
        ClaimRevModuleSetup::reactivateSftpService();
        $actionMessage = xlt("SFTP service has been reactivated.");
    }
    if (ModuleInput::postExists('backgroundService')) {
        ClaimRevModuleSetup::createBackGroundServices();
        $actionMessage = xlt("Background services have been reset to defaults.");
    }
    if (ModuleInput::postExists('runMigrations')) {
        ClaimRevModuleSetup::runMigrations();
        $actionMessage = xlt("Upgrade complete. New tables and services have been applied.");
    }
    if (ModuleInput::postExists('createPartner')) {
        $idNumber = ModuleInput::postString('partnerIdNumber');
        $senderId = ModuleInput::postString('partnerSenderId');
        ClaimRevModuleSetup::createPartnerRecord($idNumber, $senderId);
        $actionMessage = xlt("X12 partner record has been created.");
    }
}

$services = ClaimRevModuleSetup::getBackgroundServices();

?>
<html>
    <head>
        <title><?php echo xlt("ClaimRev Connect - Setup"); ?></title>
        <?php Header::setupHeader(); ?>
    </head>
    <body class="body_top">
        <div class="container-fluid">
            <?php require '../templates/navbar.php'; ?>

            <?php if ($actionMessage != '') { ?>
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    <?php echo $actionMessage; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php } ?>

            <h3 class="mt-3"><?php echo xlt("Setup"); ?></h3>

            <div class="row mt-3">
                <div class="col-lg-6">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><?php echo xlt("x12 Partner Record"); ?></h6>
                        </div>
                        <div class="card-body">
                            <?php
                            /** @var array<string, mixed> $globalsForConfig */
                            $globalsForConfig = $GLOBALS;
                            $globalsConfig = new \OpenEMR\Modules\ClaimRevConnector\GlobalConfig($globalsForConfig);
                            if (ClaimRevModuleSetup::doesPartnerExists()) {
                                echo "<span class='text-success'>" . xlt("It looks like your X12 partner record is setup.") . "</span>";
                            } elseif (!$globalsConfig->isConfigured()) {
                                echo "<p class='text-warning'>" . xlt("Before creating a partner record, you need to configure your ClaimRev credentials.") . "</p>";
                                echo "<p>" . xlt("Go to Admin -> Config -> ClaimRev Connect and fill in:") . "</p>";
                                echo "<ul>";
                                echo "<li><strong>" . xlt("ClaimRev Environment") . "</strong> — " . xlt("Set to P for production") . "</li>";
                                echo "<li><strong>" . xlt("Client ID") . "</strong> — " . xlt("Available in the ClaimRev Portal under Client Connect") . "</li>";
                                echo "<li><strong>" . xlt("Client Secret") . "</strong> — " . xlt("Available in the ClaimRev Portal under Client Connect") . "</li>";
                                echo "</ul>";
                                echo "<p class='text-muted'>" . xlt("Once configured, return here to create the partner record.") . "</p>";
                            } else {
                                echo "<p class='text-danger'>" . xlt("Your x12 Partner has not been created. Click below to create it now.") . "</p>";
                                echo "<p class='text-muted'>" . xlt("You will need your practice Tax ID (EIN) and your ClaimRev account number (Submitter ID). If you don't have these handy you can leave them blank and update the partner record later under Admin -> Practice -> X12 Partners.") . "</p>";
                                ?>
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#createPartnerModal"><?php echo xlt("Create Partner Record"); ?></button>
                                <?php
                            }
                            ?>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><?php echo xlt("Background Services"); ?></h6>
                        </div>
                        <div class="card-body">
                            <p><?php echo xlt("There are required background services that are needed to send claims, pick up reports, and check eligibility. They are listed below in a table, but if there is something strange going on use the button to re-create the records."); ?></p>
                            <form method="post" action="setup.php" class="d-inline">
                                <button type="submit" name="backgroundService" class="btn btn-primary"><?php echo xlt("Set Defaults"); ?></button>
                                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfHelper::collectCsrfToken('ClaimRevModule')); ?>" />
                            </form>
                            <form method="post" action="setup.php" class="d-inline ml-2">
                                <button type="submit" name="runMigrations" class="btn btn-secondary"><?php echo xlt("Run Upgrade"); ?></button>
                                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfHelper::collectCsrfToken('ClaimRevModule')); ?>" />
                            </form>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><?php echo xlt("SFTP Background Service"); ?></h6>
                        </div>
                        <div class="card-body">
                            <?php
                            if (ClaimRevModuleSetup::couldSftpServiceCauseIssues()) {
                                echo "<p>" . xlt("The SFTP service is still activated to send claims. We have noticed that this service can cause our service not to work correctly. If you would like to deactivate it, click the following button. Note: if you're sending claims elsewhere through SFTP, this would stop that.") . "</p>";
                                ?>
                                <form method="post" action="setup.php">
                                    <button type="submit" name="deactivateSftp" class="btn btn-warning"><?php echo xlt("Deactivate"); ?></button>
                                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfHelper::collectCsrfToken('ClaimRevModule')); ?>" />
                                </form>
                                <?php
                            } else {
                                echo "<p>" . xlt("The SFTP Service has been disabled, this is good and will prevent the service from working against sending your claims. However if you would like to reactivate it then click this button.") . "</p>";
                                ?>
                                <form method="post" action="setup.php">
                                    <button type="submit" name="reactivateSftp" class="btn btn-outline-secondary"><?php echo xlt("Reactivate"); ?></button>
                                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfHelper::collectCsrfToken('ClaimRevModule')); ?>" />
                                </form>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <h3 class="mt-3"><?php echo xlt("Background Services"); ?></h3>
            <div class="card mt-3">
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th scope="col"><?php echo xlt("Name"); ?></th>
                                <th scope="col"><?php echo xlt("Active"); ?></th>
                                <th scope="col"><?php echo xlt("Running"); ?></th>
                                <th scope="col"><?php echo xlt("Next Run"); ?></th>
                                <th scope="col"><?php echo xlt("Execute Interval"); ?></th>
                                <th scope="col"><?php echo xlt("Function"); ?></th>
                                <th scope="col"><?php echo xlt("Require Once"); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($services as $service) {
                                ?>
                                <tr>
                                    <td><?php echo text(TypeCoerce::asString($service["name"] ?? '')); ?> - <?php echo text(TypeCoerce::asString($service["title"] ?? '')); ?></td>
                                    <td><?php echo text(TypeCoerce::asString($service["active"] ?? '')); ?></td>
                                    <td>
                                        <?php if (TypeCoerce::asInt($service["running"] ?? 0) === 1) { ?>
                                            <span class="text-danger font-weight-bold"><?php echo text(TypeCoerce::asString($service["running"])); ?></span>
                                        <?php } else { ?>
                                            <?php echo text(TypeCoerce::asString($service["running"] ?? '')); ?>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo text(TypeCoerce::asString($service["next_run"] ?? '')); ?></td>
                                    <td><?php echo text(TypeCoerce::asString($service["execute_interval"] ?? '')); ?></td>
                                    <td><?php echo text(TypeCoerce::asString($service["function"] ?? '')); ?></td>
                                    <td><small><?php echo text(TypeCoerce::asString($service["require_once"] ?? '')); ?></small></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Create Partner Modal -->
            <div class="modal fade" id="createPartnerModal" tabindex="-1" role="dialog" aria-labelledby="createPartnerModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form method="post" action="setup.php">
                            <div class="modal-header">
                                <h5 class="modal-title" id="createPartnerModalLabel"><?php echo xlt("Create X12 Partner Record"); ?></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p><?php echo xlt("Enter your practice information below. This will create the X12 partner record needed for ClaimRev to process your claims."); ?></p>
                                <div class="form-group">
                                    <label for="partnerIdNumber"><?php echo xlt("Tax ID (ETIN)"); ?></label>
                                    <input type="text" class="form-control" id="partnerIdNumber" name="partnerIdNumber" placeholder="<?php echo xla("e.g. 123456789"); ?>" maxlength="15"/>
                                    <small class="form-text text-muted"><?php echo xlt("Your practice's federal tax ID number (EIN)."); ?></small>
                                </div>
                                <div class="form-group">
                                    <label for="partnerSenderId"><?php echo xlt("Submitter ID"); ?></label>
                                    <input type="text" class="form-control" id="partnerSenderId" name="partnerSenderId" placeholder="<?php echo xla("e.g. your account number"); ?>" maxlength="15"/>
                                    <small class="form-text text-muted"><?php echo xlt("Your ClaimRev account number. Used in ISA06 and GS02. Leave blank if unsure."); ?></small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <input type="hidden" name="createPartner" value="1" />
                                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfHelper::collectCsrfToken('ClaimRevModule')); ?>" />
                                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo xlt("Cancel"); ?></button>
                                <button type="submit" class="btn btn-success"><?php echo xlt("Create"); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
