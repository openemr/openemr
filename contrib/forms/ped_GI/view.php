<?php

/**
 * Generated DocBlock
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  andres_paglayan <andres_paglayan>
 * @author  cfapress <cfapress>
 * @author  sunsetsystems <sunsetsystems>
 * @author  Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2005 andres_paglayan <andres_paglayan>
 * @copyright Copyright (c) 2008 cfapress <cfapress>
 * @copyright Copyright (c) 2007 sunsetsystems <sunsetsystems>
 * @copyright Copyright (c) 2017-2023 Robert Down <robertdown@live.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<!-- Form created by Andres paglayan -->
<?php
require_once(__DIR__ . "/../../globals.php");

use OpenEMR\Core\Header;

?>
<html><head>
    <?php Header::setupHeader(); ?>
</head>
<body class="body_top">

<?php
require_once("$srcdir/api.inc.php");
$obj = formFetch("form_ped_GI", $_GET["id"]);
?>

<form method=post action="<?php echo $rootdir?>/forms/ped_GI/save.php?mode=update&id=<?php echo attr_url($_GET["id"]); ?>" name="my_form">
<span class="title">Pediatric Gastro Intestinal Evaluation</span><br /><br />

<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br />
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save Changes]</a>
<br /><br />
<!-- Form goes here -->

<?php
    require('form.php');
?>

<!-- Form ends here -->
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br />
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save Changes]</a>

</form>
<?php
formFooter();
?>
