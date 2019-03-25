<?php
/**
 * Patient Portal Signer Modal Dynamic Template
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
require_once dirname(__FILE__) . "/../../../vendor/autoload.php";
require_once(dirname(__FILE__) . '/../../../library/sql.inc');

$isAdmin = !empty($_GET['isAdmin']) ? 'checked' : '';

$msg1 = xlt('Show Current Signature On File');
$msg2 = xlt('As appears on documents');
$msg3 = xlt('Is Authorizing Signature');
$msg4 = xlt('Sign Above');
$msg5 = xlt('Clear');
$msg6 = xlt('Acknowledge Electronic Signature');

$sign_type = 'patient-signature';
if ($isAdmin) {
    $sign_type = 'admin-signature';
}
// short & sweet
$modal = <<<MODAL
<div id='openSignModal' class='modal fade' role='dialog' tabindex='-1'>
<div class='modal-dialog modal-lg'><div class='modal-content'><div class='modal-header'>
<button type='button' class='close pull-right' data-dismiss='modal'>&times;</button><div class='input-group'>
<span class='input-group-addon' data-action='show'><em> $msg1 <br>$msg2.</em></span>
<img class='signature form-control' data-action='place' type='$sign_type' id='signatureModal' alt='Signature On File' src=''>
</div></div><div class='modal-body signature-pad-body'><ul class='sigNav'><label style='display: none;'>
<input style='display:none;' type='checkbox' id='isAdmin' name='isAdmin'/>$msg3</label></ul>
<div class='row sigPad'><div class='panel'><div class='sign-body'><div id='signature-pad' class='signature-pad'>
<div class='signature-pad--body'><canvas></canvas></div><div class='signature-pad--footer'><div class='description'>$msg4</div>
<div class='signature-pad--actions'><div><button type='button' class='btn btn-primary btn-sm clear' data-action='clear'>$msg5</button>
</div><div><button type='button' class='btn btn-primary btn-sm save' data-action='save-png'>$msg6</button></div>
</div></div></div></div></div></div></div></div></div></div>
<img id='waitend' style='display: none; position: absolute; top: 100px; left: 260px; width: 100px; height: 100px' src='sign/assets/loading.gif'/>
MODAL;

echo js_escape($modal);
exit();
