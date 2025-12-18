<?php

/**
 * _Header.tpl.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2016-2023 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;

$globalsBag = OEGlobalsBag::getInstance();
$assets_static_relative = $globalsBag->getString('assets_static_relative');
$web_root = $globalsBag->getString('web_root');
$v_js_includes = $globalsBag->get('v_js_includes');
?>
<!DOCTYPE html>
<html>
    <head>
        <?php
        if ($_SESSION['patient_portal_onsite_two'] ?? 0) {
            Header::setupHeader(['no_main-theme', 'portal-theme', 'datetime-picker']);
        } else {
            Header::setupHeader(['datetime-picker']);
        }
        ?>
        <title><?php $this->eprint($this->title); ?></title>
        <meta http-equiv="X-Frame-Options" content="deny" />
        <base href="<?php $this->eprint($this->ROOT_URL); ?>" />
        <meta name="description" content="Patient Portal" />
        <meta name="author" content="Form | sjpadgett@gmail.com" />
        <script src="<?php echo $web_root; ?>/portal/patient/scripts/libs/LAB.min.js"></script>
        <script>
            $LAB.script("<?php echo $assets_static_relative; ?>/moment/moment.js")
                .script("<?php echo $assets_static_relative; ?>/underscore/underscore-min.js").wait()
                .script("<?php echo $assets_static_relative; ?>/backbone/backbone-min.js")
                .script("<?php echo $web_root; ?>/portal/patient/scripts/app.js?v=<?php echo $v_js_includes; ?>")
                .script("<?php echo $web_root; ?>/portal/patient/scripts/model.js?v=<?php echo $v_js_includes; ?>").wait()
                .script("<?php echo $web_root; ?>/portal/patient/scripts/view.js?v=<?php echo $v_js_includes; ?>").wait()
        </script>
    </head>

    <body>
        <div class="navbar navbar-light bg-light sticky-top">
            <div class="container">
                      <a class="navbar-brand" href="./"><?php echo xlt('Home'); ?></a>
                        <a class="navbar-toggler" data-toggle="collapse" data-target=".navbar-collapse"><span class="navbar-toggler-icon"></span></a>
                        <div class="container">
                        <div class="collapse navbar-collapse">
                            <ul class="nav float-right navbar-nav">
                                <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-lock"></i> <?php echo xlt('Login'); ?> <i class="caret"></i></a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="./loginform"><?php echo xlt('Login'); ?></a></li>
                                    <li class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="./secureuser"><?php echo xlt('Patient Dashboard'); ?><i class="icon-lock"></i></a></li>
                                    <li><a class="dropdown-item" href="./secureadmin"><?php echo xlt('Provider Dashboard'); ?><i class="icon-lock"></i></a></li>
                                </ul>
                                </li>
                            </ul>
                        </div><!--/.nav-collapse -->
                    </div>
                </div>
            </div>
