<?php
/**
 * language.php script
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


//INCLUDES, DO ANY ACTIONS, THEN GET OUR DATA
require_once("../globals.php");
require_once("$srcdir/registry.inc");
require_once("language.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Utils\RandomGenUtils;
use OpenEMR\Core\Header;

//START OUT OUR PAGE....
?>
<html>
<head>
<?php Header::setupHeader([]); ?>
</head>

<body class="body_top">
    <div id="container_div" class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="page-header clearfix">
                    <h2 class="title"><?php  echo xlt('Multi Language Tool'); ?></h2>
                </div>
            </div>
        </div><!--end of header div-->
        <div class="container-fluid mb-3">
            <form name='translation' id='translation' method='get' action='language.php' onsubmit="return top.restoreSession()">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                <input type='hidden' name='m' value='<?php echo attr($_GET['m']); ?>' />
                <input type='hidden' name='edit' value='<?php echo attr($_GET['edit']); ?>' />
                <!-- <span class="title"><?php echo xlt('Multi Language Tool'); ?></span> -->
                <ui class="nav nav-pills">
                    <li class="nav-item" id="li-definition">
                        <a href="?m=definition&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>" onclick="top.restoreSession()" class="nav-link font-weight-bold" id="definition-link"><?php echo xlt('Edit Definitions'); ?></a>
                    </li>
                    <li class="nav-item" id="li-language">
                        <a href="?m=language&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>" onclick="top.restoreSession()" class="nav-link font-weight-bold" id="language-link"><?php echo xlt('Add Language'); ?></a>
                    </li>
                    <li class="nav-item" id="li-constant">
                        <a href="?m=constant&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>" onclick="top.restoreSession()" class="nav-link font-weight-bold" id="constant-link"><?php echo xlt('Add Constant'); ?></a>
                    </li>
                    <li class="nav-item" id="li-manage">
                        <a href="?m=manage&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>" onclick="top.restoreSession()" class="nav-link font-weight-bold" id="manage-link"><?php echo xlt('Manage Translations'); ?></a>
                    </li>
                </ui>
            </form>
        </div><!--end of nav-pills div-->
        <div class="row">
            <div class="col-sm-12">
                <div class="jumbotron jumbotron-fluid py-3">
                    <div class="col-sm-12 col-md-12 col-lg-12">
                        <?php
                        if (!empty($_GET['m'])) {
                            if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
                                CsrfUtils::csrfNotVerified();
                            }

                            // Pass a unique variable, so below scripts can
                            // not be run on their own
                            $unique_id = RandomGenUtils::createUniqueToken();
                            $_SESSION['lang_module_unique_id'] = $unique_id;

                            switch ($_GET['m']) :
                                case 'definition':
                                    require_once('lang_definition.php');
                                    break;
                                case 'constant':
                                    require_once('lang_constant.php');
                                    break;
                                case 'language':
                                    require_once('lang_language.php');
                                    break;
                                case 'manage':
                                    require_once('lang_manage.php');
                                    break;
                            endswitch;
                        } else {
                            // If m is parameter empty, To autoload Edit Definitions page content
                            echo('<script>$(function () {$("#definition-link").get(0).click();});</script>');
                        }
                        ?>
                </div>
            </div>
        </div><!--end of page content div-->
        <br>
        <a href="lang.info.html" class="text-decoration-none" target="_blank"><?php echo xlt('Info'); ?></a>
    </div>

</body>
</html>
