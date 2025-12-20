<?php

/**
 * forms/eye_mag/help_signature.php
 *
 * Help File for Signature Setup on the Eye Form
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <magauran@MedFetch.com>
 * @copyright Copyright (c) 2016 Raymond Magauran <magauran@MedFetch.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/lists.inc.php");
require_once("$srcdir/api.inc.php");

use OpenEMR\Core\Header;

?>
<html>
    <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="Eye Exam Signature Help" />
    <meta name="author" content="openEMR: ophthalmology help" />
    <?php Header::setupHeader(); ?>
    </head>
    <body>
        <!-- Navbar Section -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="#">
                    <img src="<?php echo attr($GLOBALS['webroot']); ?>/sites/default/images/login_logo.gif" width="30" height="30" alt="">
                    <?php echo xlt('OpenEMR: Eye Exam'); ?> <span class="font-weight-bold"><?php echo xlt('Signature Help'); ?></span>
                </a>
            </div>
        </nav>

        <div class="container mt-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4><?php echo xlt('Setting Up Your Signature'); ?></h4>
                </div>
                <div class="card-body">
                    <p><?php echo xlt('The Eye Form allows you to upload a digital signature image that will be automatically applied to your reports.'); ?></p>
                    
                    <h5><?php echo xlt('How to Setup:'); ?></h5>
                    <ol>
                        <li><?php echo xlt('Go to the'); ?> <b><?php echo xlt('Library'); ?></b> <?php echo xlt('menu in the top navigation bar.'); ?></li>
                        <li><?php echo xlt('Select'); ?> <b><?php echo xlt('Setup Signature'); ?></b>.</li>
                        <li><?php echo xlt('A modal window will appear.'); ?></li>
                        <li><?php echo xlt('Use the drawing pad to sign your name, or upload an existing image of your signature.'); ?></li>
                        <li><?php echo xlt('Click'); ?> <b><?php echo xlt('Save'); ?></b> <?php echo xlt('to store your signature.'); ?></li>
                    </ol>

                    <div class="alert alert-info">
                        <strong><?php echo xlt('Note:'); ?></strong> <?php echo xlt('This signature is specific to your user account and will be used for all Eye Exams you finalize.'); ?>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
