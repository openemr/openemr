<?php

/**
 * Patient Portal QuickStart
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("verify_session.php");

$title = xlt("My Quickstarts");

$current_theme = sqlQuery("SELECT `setting_value` FROM `patient_settings` WHERE setting_patient = ? AND `setting_label` = ?", array($pid, 'portal_theme'))['setting_value'] ?? '';

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

?>
<!DOCTYPE html>
<!-- Language grabbed by PDF var that has the correct format !-->
<html lang="<?php echo $GLOBALS['pdf_language']; ?>">
<head>
    <title><?php echo($title); ?></title>
    <meta name="description" content="Patient Portal" />
    <meta name="author" content="Dashboard | sjpadgett@gmail.com" />
    <?php
    Header::setupHeader(['no_main-theme', 'portal-theme', 'datetime-picker']);
    echo "<script>var cpid='" . attr($cpid ?? $pid) . "';var cuser='" . attr($cuser ?? 'portal-user') . "';var webRoot='" . $GLOBALS['web_root'] . "';</script>";
    ?>
    <link href="<?php echo $GLOBALS['web_root']; ?>/portal/sign/css/signer_modal.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" rel="stylesheet">
    <script src="<?php echo $GLOBALS['web_root']; ?>/portal/sign/assets/signature_pad.umd.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>
    <script src="<?php echo $GLOBALS['web_root']; ?>/portal/sign/assets/signer_api.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>

    <script>
        $(function () {
            let ele = parent.document.getElementById('topNav');
            if ($(parent.document.getElementById('topNav')).is('.collapse:not(.show)')) {
                ele.classList.toggle('collapse');
            }
            // ensure top level shows in quickstart
            $(parent.document.getElementById('topNav')).removeClass("d-none");
            $("#my_theme").on('change', function (e) {
                let sel = $("#my_theme :selected").val();
                persistPatientSetting(cpid, 'portal_theme', sel);
                $(parent.document.getElementById('homeRefresh')).click();
            });
        });
        function persistPatientSetting(pid, label, setting) {
            fetch('lib/persist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(
                    {
                        'csrf_token_form': <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>,
                        'setting_patient': pid,
                        'setting_label': label,
                        'setting_value': setting
                    })
            });
        }
    </script>
</head>
<body class="pt-2">
    <!-- About Dialog -->
    <div class="modal fade" id="formdialog" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog bg-light">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><?php echo xlt('About Portal Dashboard') ?></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div>
                    <span><?php echo xlt('Please see forum or wiki'); ?>
                        <a href="<?php echo attr('https://community.open-emr.org/'); ?>" target="_blank"><?php echo xlt("Visit Forum"); ?></a>
                    </span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="okButton" data-dismiss="modal" class="btn btn-secondary"><?php echo xlt('Close...') ?></button>
                </div>
            </div>
        </div>
    </div>
    <div class="container-xl p-3">
        <div class="jumbotron jumbotron-fluid text-center p-1">
            <h3><?php echo xlt('My Quick Starts') ?><i class="fa fa-user text-danger ml-2" style="font-size: 3rem;"></i></h3>
            <p>
                <button class="btn btn-sm btn-secondary" data-toggle="modal" data-target="#formdialog"><?php echo xlt('Tell me more') ?></button>
            </p>
        </div>
        <div class='jumbotron jumbotron-fluid p-4'>
            <div class="row" id="inject_card">
                <!-- Document -->
                <div class="card d-flex mr-1 mb-1">
                    <div class="card-body">
                        <h4 class="card-title"><i class="fa fa-file-text mr-1"></i><?php echo xlt('My Documents') ?></h4>
                        <a id="documents-go" class="btn btn-success" href="<?php echo $GLOBALS['web_root']; ?>/portal/patient/onsitedocuments?pid=<?php echo attr_url($pid); ?>"><?php echo xlt('Manage Documents') ?></a>
                    </div>
                </div>
                <!-- Signature -->
                <div class="card d-flex mr-1 mb-1">
                    <div class="card-body">
                        <h4><i class="card-title fa fa-signature mr-1"></i><?php echo xlt('Signature') ?></h4>
                        <a data-type="patient-signature" class="btn btn-primary" href="#openSignModal" data-toggle="modal" data-backdrop="true" data-target="#openSignModal"><?php echo xlt('Manage Signature'); ?></a>
                    </div>
                </div>
                <!-- Theme -->
                <div class="card d-flex mr-1 mb-1">
                    <div class="card-body row">
                        <h4 class="card-title"><i class="fa fa-link mr-1"></i><?php echo xlt('Settings') ?></h4>
                        <div class="col-12 form-group">
                            <div class="col">
                                <div class="input-group">
                                    <label class="m-0" for='my_theme'><?php echo xlt('Current Theme') ?></label>
                                    <select class='form-control ml-2' id='my_theme'>
                                        <?php
                                        $theme_dir = "$webserver_root/public/themes";
                                        $fld_type = 'css';
                                        $patternStyle = 'style_';
                                        $dh = opendir($theme_dir);
                                        if ($dh) {
                                            // Collect styles
                                            $styleArray = array();
                                            while (false !== ($tfname = readdir($dh))) {
                                                $patternStyle = 'style_';
                                                if (
                                                    $tfname == 'style_blue.css' ||
                                                    $tfname == 'style_pdf.css' ||
                                                    !preg_match("/^" . $patternStyle . ".*\.css$/", $tfname)
                                                ) {
                                                    continue;
                                                }
                                                $styleDisplayName = str_replace("_", " ", substr($tfname, 6));
                                                $styleDisplayName = ucfirst(str_replace(".css", "", $styleDisplayName));
                                                $styleArray[$tfname] = $styleDisplayName;
                                            }
                                            asort($styleArray);
                                            // Generate style selector
                                            foreach ($styleArray as $styleKey => $styleValue) {
                                                echo "<option value='" . attr($styleKey) . "'";
                                                if ($styleKey == $current_theme) {
                                                    echo " selected";
                                                }
                                                echo ">";
                                                echo text($styleValue);
                                                echo "</option>\n";
                                            }
                                        }
                                        closedir($dh);
                                        ?>
                                    </select>
                                    <div class="input-group-append">
                                        <a class="btn btn-primary" href="./home.php" target="_parent"><?php echo xlt('Apply') ?></a>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group form-check">
                                    <input type="checkbox" class="form-check-input" id="exampleCheck1">
                                    <label class="form-check-label" for="exampleCheck1"><?php echo xlt('Auto Save Documents') . " (Coming soon)" ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</body>
</html>
