<?php
/**
 * CDS Hooks Settings Management
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  CDS Hooks Integration
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token_form'] ?? '')) {
    CsrfUtils::csrfNotVerified();
}

$alertmsg = '';
$error = '';

// Handle form submission
if ($_POST['form_action'] ?? false) {
    if ($_POST['form_action'] === 'save_settings') {
        // Save CDS Hook settings
        $enable_cds_hooks = $_POST['enable_cds_hooks'] ? 1 : 0;
        $cds_timeout = intval($_POST['cds_timeout']) ?: 5;
        $cds_debug = $_POST['cds_debug'] ? 1 : 0;
        
        // Update global settings
        sqlStatement("INSERT INTO `globals` (`gl_name`, `gl_value`) VALUES ('enable_cds_hooks', ?) ON DUPLICATE KEY UPDATE `gl_value` = ?", [$enable_cds_hooks, $enable_cds_hooks]);
        sqlStatement("INSERT INTO `globals` (`gl_name`, `gl_value`) VALUES ('cds_timeout', ?) ON DUPLICATE KEY UPDATE `gl_value` = ?", [$cds_timeout, $cds_timeout]);
        sqlStatement("INSERT INTO `globals` (`gl_name`, `gl_value`) VALUES ('cds_debug', ?) ON DUPLICATE KEY UPDATE `gl_value` = ?", [$cds_debug, $cds_debug]);
        
        $alertmsg = xlt("CDS Hook settings saved successfully.");
    }
}

// Get current settings
$enable_cds_hooks = getGlobalSetting('enable_cds_hooks') ?: 0;
$cds_timeout = getGlobalSetting('cds_timeout') ?: 5;
$cds_debug = getGlobalSetting('cds_debug') ?: 0;

?>

<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['common', 'datetime-picker']); ?>
    <title><?php echo xlt('CDS Hooks Settings'); ?></title>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-cogs"></i> <?php echo xlt('CDS Hooks Settings'); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($alertmsg) : ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo text($alertmsg); ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error) : ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo text($error); ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="">
                            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                            <input type="hidden" name="form_action" value="save_settings" />

                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="enable_cds_hooks" id="enable_cds_hooks" 
                                           <?php echo $enable_cds_hooks ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable_cds_hooks">
                                        <?php echo xlt('Enable CDS Hooks Integration'); ?>
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    <?php echo xlt('Automatically trigger CDS Hook services when viewing patient data'); ?>
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="cds_timeout"><?php echo xlt('CDS Service Timeout (seconds)'); ?></label>
                                <input type="number" class="form-control" name="cds_timeout" id="cds_timeout" 
                                       value="<?php echo attr($cds_timeout); ?>" min="1" max="30">
                                <small class="form-text text-muted">
                                    <?php echo xlt('Maximum time to wait for CDS service response'); ?>
                                </small>
                            </div>

                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="cds_debug" id="cds_debug" 
                                           <?php echo $cds_debug ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="cds_debug">
                                        <?php echo xlt('Enable Debug Mode'); ?>
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    <?php echo xlt('Log detailed CDS Hook requests and responses'); ?>
                                </small>
                            </div>

                            <div class="form-group">
                                <h6><?php echo xlt('Available CDS Services'); ?></h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th><?php echo xlt('Service'); ?></th>
                                                <th><?php echo xlt('Hook Type'); ?></th>
                                                <th><?php echo xlt('Status'); ?></th>
                                                <th><?php echo xlt('URL'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><?php echo xlt('Patient Greeting'); ?></td>
                                                <td><span class="badge badge-info">patient-view</span></td>
                                                <td><span class="badge badge-success"><?php echo xlt('Enabled'); ?></span></td>
                                                <td><code>https://sandbox-services.cds-hooks.org/cds-services/patient-greeting</code></td>
                                            </tr>
                                            <tr>
                                                <td><?php echo xlt('CMS Price Check'); ?></td>
                                                <td><span class="badge badge-warning">order-select</span></td>
                                                <td><span class="badge badge-secondary"><?php echo xlt('Disabled'); ?></span></td>
                                                <td><code>https://sandbox-services.cds-hooks.org/cds-services/cms-price-check</code></td>
                                            </tr>
                                            <tr>
                                                <td><?php echo xlt('PAMA Imaging'); ?></td>
                                                <td><span class="badge badge-warning">order-select</span></td>
                                                <td><span class="badge badge-secondary"><?php echo xlt('Disabled'); ?></span></td>
                                                <td><code>https://sandbox-services.cds-hooks.org/cds-services/pama-imaging</code></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?php echo xlt('Save Settings'); ?>
                                </button>
                                <a href="demographics.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> <?php echo xlt('Back to Patient'); ?>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
