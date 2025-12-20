<?php

/**
 * forms/eye_mag/help_fax_config.php
 *
 * Help File for Configuring vFax/Email Gateway
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <magauran@MedFetch.com>
 * @copyright Copyright (c) 2024 Raymond Magauran <magauran@MedFetch.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");

use OpenEMR\Core\Header;

?>
<html>
    <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="Eye Exam Fax Configuration Help" />
    <meta name="author" content="openEMR: ophthalmology help" />
    <?php Header::setupHeader(); ?>
    </head>
    <body>
        <!-- Navbar Section -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="#">
                    <img src="<?php echo attr($GLOBALS['webroot']); ?>/sites/default/images/login_logo.gif" width="30" height="30" alt="">
                    <?php echo xlt('OpenEMR: Eye Exam'); ?> <span class="font-weight-bold"><?php echo xlt('Fax Configuration Help'); ?></span>
                </a>
            </div>
        </nav>
        <!-- Content Section -->
        <div class="container" style="margin-top: 20px;">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo xlt('Configuring the vFax/Email Gateway'); ?></h5>
                </div>
                <div class="card-body">
                    <p><?php echo xlt('The Eye Form allows sending documents via a vFax/Email gateway. This requires configuring OpenEMR\'s global settings.'); ?></p>
                    
                    <h6><?php echo xlt('1. Configure SMTP Settings'); ?></h6>
                    <p><?php echo xlt('Go to'); ?> <b><?php echo xlt('Administration -> Globals -> Connectors'); ?></b> <?php echo xlt('and configure the SMTP settings:'); ?></p>
                    <ul>
                        <li><b><?php echo xlt('SMTP Host:'); ?></b> <?php echo xlt('Your SMTP server address (e.g., smtp.gmail.com).'); ?></li>
                        <li><b><?php echo xlt('SMTP Port:'); ?></b> <?php echo xlt('The port for your SMTP server (e.g., 587 or 465).'); ?></li>
                        <li><b><?php echo xlt('SMTP User:'); ?></b> <?php echo xlt('Your email address for authentication.'); ?></li>
                        <li><b><?php echo xlt('SMTP Password:'); ?></b> <?php echo xlt('Your email password or app-specific password.'); ?></li>
                        <li><b><?php echo xlt('SMTP Security:'); ?></b> 'tls' <?php echo xlt('or'); ?> 'ssl' (<?php echo xlt('usually'); ?> 'tls' <?php echo xlt('for'); ?> 587, 'ssl' <?php echo xlt('for'); ?> 465).</li>
                    </ul>

                    <h6><?php echo xlt('2. Configure HylaFAX Server'); ?></h6>
                    <p><?php echo xlt('You need to define the'); ?> <code>hylafax_server</code> <?php echo xlt('global variable. This is the domain used for the fax gateway (e.g.,'); ?> <code>fax.example.com</code>).</p>
                    <p><?php echo xlt('When a fax is sent to number'); ?> <code>1234567890</code>, <?php echo xlt('the system will email a PDF to'); ?> <code>1234567890@fax.example.com</code>.</p>
                    <p><?php echo xlt('To set this up:'); ?></p>
                    <ol>
                        <li><?php echo xlt('Go to'); ?> <b><?php echo xlt('Administration -> Globals -> Miscellaneous'); ?></b>.</li>
                        <li><?php echo xlt('Find the'); ?> <code>hylafax_server</code> <?php echo xlt('setting.'); ?></li>
                        <li><?php echo xlt('Ensure the value is set to your fax provider\'s email domain (e.g.,'); ?> <code>fax.example.com</code>).</li>
                    </ol>

                    <h6><?php echo xlt('3. Sender Address'); ?></h6>
                    <p><?php echo xlt('The system uses the'); ?> <b><?php echo xlt('SMTP User'); ?></b> <?php echo xlt('defined in Globals as the "From" address for all outgoing faxes.'); ?></p>
                    <p><?php echo xlt('This ensures compatibility with SMTP servers that require the sender address to match the authenticated user.'); ?></p>
                    
                    <div class="alert alert-info">
                        <strong><?php echo xlt('Note:'); ?></strong> <?php echo xlt('Ensure that your SMTP provider allows sending emails from the configured addresses.'); ?>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
