<?php

/**
 * careplan_actions.php is a template file for the action buttons displayed in the careplan form
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<div class="btn-group" role="group">
    <button type="button" class="btn btn-secondary btn-add btn-sm"
        onclick="duplicateRow(this.parentElement.parentElement.parentElement.parentElement);"
        title='<?php echo xla('Click here to duplicate the row'); ?>'>
        <?php echo xlt('Add'); ?>
    </button>
    <button class="btn btn-secondary btn-delete btn-sm" onclick="
        el=this.parentElement.parentElement.parentElement.parentElement;
        deleteRow(event, el.id, el.parentElement.getElementsByClassName('tb_row').length);"
        title='<?php echo xla('Click here to delete the row'); ?>'>
        <?php echo xlt('Delete'); ?>
    </button>
    <button class="btn btn-secondary reason-code-btn btn-sm"
            title='<?php echo xla('Click here to provide an explanation for the observation value (or lack of value)'); ?>'
            data-toggle-container="reason_code_<?php echo attr($key); ?>"><i class="fa fa-asterisk"></i>
            <?php echo xlt("Add Reason"); ?>
    </button>
</div>
