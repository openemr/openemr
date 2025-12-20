<?php

/**
 * forms/eye_mag/help_dot_phrases.php
 *
 * Help File for Dot Phrases on the Eye Form
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
    <meta name="description" content="Eye Exam Dot Phrase Help" />
    <meta name="author" content="openEMR: ophthalmology help" />
    <?php Header::setupHeader(); ?>
    </head>
    <body>
        <!-- Navbar Section -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="#">
                    <img src="<?php echo attr($GLOBALS['webroot']); ?>/sites/default/images/login_logo.gif" width="30" height="30" alt="">
                    <?php echo xlt('OpenEMR: Eye Exam'); ?> <span class="font-weight-bold"><?php echo xlt('Dot Phrase Help'); ?></span>
                </a>
            </div>
        </nav>

        <div class="container mt-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4><?php echo xlt('Using Dot Phrases'); ?></h4>
                </div>
                <div class="card-body">
                    <p><?php echo xlt('Dot phrases allow you to quickly insert common blocks of text into your exam notes by typing a shortcut.'); ?></p>
                    
                    <h5><?php echo xlt('How to Use:'); ?></h5>
                    <ul>
                        <li><?php echo xlt('In any text area (like History or Plan), type a period'); ?> <code>.</code> <?php echo xlt('followed by your shortcut code.'); ?></li>
                        <li><?php echo xlt('Example: Typing'); ?> <code>.cataract</code> <?php echo xlt('might expand to a full paragraph describing a cataract diagnosis.'); ?></li>
                        <li><?php echo xlt('The system will automatically replace the shortcut with the full text.'); ?></li>
                    </ul>

                    <hr>

                    <h5><?php echo xlt('Single vs. Multi-Field Fill'); ?></h5>
                    <p><?php echo xlt('Dot phrases can be configured to behave in two ways:'); ?></p>
                    <ul>
                        <li><strong><?php echo xlt('Single Field Fill:'); ?></strong> <?php echo xlt('The text expands only within the box where you typed the shortcut. This is useful for simple phrases or sentences.'); ?></li>
                        <li><strong><?php echo xlt('Multi-Field Fill:'); ?></strong> <?php echo xlt('Advanced dot phrases can populate multiple fields across the form simultaneously. For example, a "Normal Exam" dot phrase might fill in the Vision, Slit Lamp, and Fundus sections all at once.'); ?></li>
                    </ul>

                    <hr>

                    <h5><?php echo xlt('Managing Dot Phrases:'); ?></h5>
                    <p><?php echo xlt('You can create and edit your own dot phrases.'); ?></p>
                    <ol>
                        <li><?php echo xlt('Go to the'); ?> <b><?php echo xlt('Library'); ?></b> <?php echo xlt('menu.'); ?></li>
                        <li><?php echo xlt('Select'); ?> <b><?php echo xlt('Dot Phrases'); ?></b> <?php echo xlt('(if available) or manage them through the standard list editor.'); ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </body>
</html>
