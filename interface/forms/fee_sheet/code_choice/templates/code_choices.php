<?php

/**
 * code_choices.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2014 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<script type="text/html" id="code-choice-options">
    &nbsp;
    <div data-bind="foreach:categories">
        <div class="category-display">
            <button class="btn btn-primary" data-bind="text:title,click: set_active_category"></button>
        </div>
    </div>
    <!-- ko if: active_category -->
    <div class='active-category' data-bind='visible: show_choices'>
        <div data-bind="template: {name: 'category-options', data: active_category}">
        </div>
    </div>
    <!-- /ko -->
</script>

<script type="text/html" id="category-options">
    <div>
        <div data-bind="text:title" class = "feesheet-title"></div>
        <div data-bind="foreach:codes">
            <div class='code-choice' data-bind="event: {mouseup: toggle_code}">
                <input type="checkbox" data-bind="checked: selected, click: toggle_code"/>
                <span data-bind="text:description"></span>
            </div>
        </div>
        <div style="clear: both; padding: 15px 0 0 10px;">
            <button class="btn btn-primary" data-bind="click:codes_ok"><?php echo xlt("OK")?></button>
            <button class="btn btn-secondary" data-bind="click:codes_cancel"><?php echo xlt("Cancel")?></button>
        </div>
    </div>
</script>
