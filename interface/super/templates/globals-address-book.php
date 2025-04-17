<?php

/**
 * globals-address-book.php contains all of the html for the GlobalSetting::DATA_TYPE_ADDRESS_BOOK
 * data type.  The javascript that controls the adding / removing, and sorting of the list items is in the edit_globals.js
 * file.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\Common\Database\QueryUtils;

$textLabel = "";
$i = $i ?? 0;
if (!empty($fldvalue)) {
    $users = QueryUtils::fetchRecords("SELECT fname, lname FROM users WHERE id = ?", [$fldvalue]);
    $user = $users[0] ?? ['fname' => '', 'lname' => ''];
    $textLabel = trim($user['fname'] . ' ' . $user['lname']);
}
$serverConfig = new ServerConfig();
$apiUrl = $serverConfig->getStandardApiUrl();
?>
<div class="gbl-field-address-book-widget">
    <input type='hidden' class="address-book-widget-input" name='form_<?php echo attr($i); ?>' id='form_<?php echo attr($i); ?>' value='<?php echo attr($fldvalue); ?>' />
    <div class="input-group">
        <input type='text' class="address-book-widget-label form-control" readonly="readonly" id='form_label_<?php echo attr($i); ?>' value='<?php echo attr($textLabel); ?>'/>
        <div class="input-group-append">
            <button class="btn btn-danger address-book-widget-delete" id='form_button_delete_<?php echo attr($i); ?>' aria-label="<?php echo xla("Delete");?>">
                <i class='fa fa-trash'></i>
            </button>
        </div>
    </div>
    <input type='button' class="address-book-widget-btn btn btn-primary" id='form_button_<?php echo attr($i); ?>' value='<?php echo xla('Open Address Book'); ?>' />
</div>
