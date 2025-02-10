<?php

/**
 * load_form.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("../../../library/registry.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Forms\FormLocator;
use OpenEMR\Common\Twig\TwigContainer;

/**
 * @gloal $incdir the include directory
 */
$incdir = $incdir ?? "";

$pageName = "new.php";
if (!str_starts_with($_GET["formname"], 'LBF')) {
    if ((!empty($_GET['pid'])) && ($_GET['pid'] > 0)) {
        $pid = $_GET['pid'];
        $encounter = $_GET['encounter'];
    }

    // ensure the path variable has no illegal characters
    check_file_dir_name($_GET["formname"]);

    // ensure authorized to see the form
    if (!AclMain::aclCheckForm($_GET["formname"])) {
        $formLabel = xl_form_title(getRegistryEntryByDirectory($_GET["formname"], 'name')['name'] ?? '');
        $formLabel = (!empty($formLabel)) ? $formLabel : $_GET["formname"];
        echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => $formLabel]);
        exit;
    }
}
$formLocator = new FormLocator();
$file = $formLocator->findFile($_GET['formname'], $pageName, 'load_form.php');
require_once($file);

if (!empty($GLOBALS['text_templates_enabled']) && !($_GET['formname'] == 'fee_sheet')) { ?>
    <script src="<?php echo $GLOBALS['web_root'] ?>/library/js/CustomTemplateLoader.js"></script>
<?php } ?>
