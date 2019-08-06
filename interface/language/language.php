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
require_once("../../library/acl.inc");
require_once("language.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Utils\RandomGenUtils;

//START OUT OUR PAGE....
?>
<html>
<head>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>

<body class="body_top">
<form name='translation' id='translation' method='get' action='language.php' onsubmit="return top.restoreSession()">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<input type='hidden' name='m' value='<?php echo attr($_GET['m']); ?>' />
<input type='hidden' name='edit' value='<?php echo attr($_GET['edit']); ?>' />
<span class="title"><?php echo xlt('Multi Language Tool'); ?></span>
<table>
 <tr>
  <td class="small" colspan='4'>
   <a href="?m=definition&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>" onclick="top.restoreSession()"><?php echo xlt('Edit Definitions'); ?></a> |
   <a href="?m=language&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>" onclick="top.restoreSession()"><?php echo xlt('Add Language'); ?></a> |
   <a href="?m=constant&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>" onclick="top.restoreSession()"><?php echo xlt('Add Constant'); ?></a> |
   <a href="?m=manage&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>" onclick="top.restoreSession()"><?php echo xlt('Manage Translations'); ?></a>
  </td>
 </tr>
</table>
</form>

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
}
?>

<BR><A HREF="lang.info.html" TARGET="_blank"><?php echo xlt('Info'); ?></A>
</body>
</html>
