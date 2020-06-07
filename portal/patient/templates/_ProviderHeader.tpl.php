<?php

/**
 * Patient Portal
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<!DOCTYPE html>
<!-- Language grabbed by PDF var that has the correct format !-->
<html lang="<?php echo $GLOBALS['pdf_language']; ?>">
    <head>
        <meta charset="utf-8">

        <title><?php $this->eprint($this->title); ?></title>
        <meta content="width=device-width, initial-scale=1, user-scalable=yes" name="viewport">

        <meta name="description" content="Provider Portal" />
        <meta name="author" content="Dashboard | sjpadgett@gmail.com" />

        <!-- Styles -->
        <link href="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <?php if ($_SESSION['language_direction'] == 'rtl') { ?>
            <link href="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-v4-rtl/dist/css/bootstrap-rtl.min.css" rel="stylesheet">
        <?php } ?>

        <link href="<?php echo $GLOBALS['web_root']; ?>/portal/patient/styles/style.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" rel="stylesheet" />
        <link href="<?php echo $GLOBALS['assets_static_relative']; ?>/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet" />
        <link href="<?php echo $GLOBALS['web_root']; ?>/portal/sign/css/signer_modal.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" rel="stylesheet">

        <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery/dist/jquery.min.js"></script>
        <script src="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <script src="<?php echo $GLOBALS['web_root']; ?>/portal/sign/assets/signature_pad.umd.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>
        <script src="<?php echo $GLOBALS['web_root']; ?>/portal/sign/assets/signer_api.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>

        <script src="<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/libs/LAB.min.js"></script>
        <script>
            $LAB.script("<?php echo $GLOBALS['assets_static_relative']; ?>/underscore/underscore-min.js")
                .script("<?php echo $GLOBALS['assets_static_relative']; ?>/moment/moment.js")
                .script("<?php echo $GLOBALS['assets_static_relative']; ?>/backbone/backbone-min.js")
                .script("<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/app.js?v=<?php echo $GLOBALS['v_js_includes']; ?>")
                .script("<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/model.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait()
                .script("<?php echo $GLOBALS['web_root']; ?>/portal/patient/scripts/view.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait()
        </script>
    </head>

    <body class="skin-blue pt-2">
 <!--      Removed until we can find a use for this
<div class="navbar navbar-light navbar-expand-md bg-light fixed-top">
            <div class="container">
                    <a class="navbar-brand" href="<?php// echo $GLOBALS['web_root']; ?>/portal/patient/provider"><?php //echo xlt('Home'); ?></a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#provider-home" aria-controls="provider-home" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
                        <div class="container">
                        <div class="collapse navbar-collapse" id="provider-home">
                            <ul class="nav navbar-nav">
                                !--><!-- Reserved !--><!--
                                </ul>
                        </div>!--><!--/.nav-collapse !--><!--
                    </div>
                </div>
            </div> !-->
