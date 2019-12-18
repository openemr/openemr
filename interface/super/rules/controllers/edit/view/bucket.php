<?php
    /**
     * interface/super/rules/controllers/edit/view/bucket.php
     *
     * @package   OpenEMR
     * @link      https://www.open-emr.org
     * @author    Aron Racho <aron@mi-squared.com>
     * @author    Brady Miller <brady.g.miller@gmail.com>
     * @copyright Copyright (c) 2010-2011 Aron Racho <aron@mi-squared.com>
     * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
     * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
     */
    $groupId = (int)(_get('group_id'));
?>
<head>
    <script language="javascript" src="<?php js_src('bucket.js') ?>"></script>

    <script type="text/javascript">
        var bucket = new bucket( {} );
        bucket.init();
    </script>
</head>

<div class="col-12">
    <h5><?php echo text($criteria->getTitle()); ?></h5>
</div>
<div class="row">
    <div class="col-11 offfset-1">
        <table class="table table-sm table-condensed table-hover">
            <!-- category -->
            <tr>
                <td class="text-right">
                <span class='bold'
                      data-field="Category"
                      data-toggle='popover'
                      data-trigger="hover"
                      data-placement="auto, right"
                      data-container="body"
                      title='Action Categories'
                      data-content='A CR exists to reminder you to perform an action.  Actions can be grouped into familiar categories,
                          such as "Perform an Assessment" or "Measure a Value".
                          For this action, choose the category which best describes what you are prompting the medical staff to complete.
                          You can select this category from a list of pre-existing Categories by clicking the Pencil icon or
                          you can create a new category by simply typing into this text box.
                          Newly created categories are added to the list "Clinical Rule Action Category".
                          The pre-existing categories are drawn from this list.  '>
                        <?php echo xlt('Category'); ?>:</span>
                </td>
                <td class="nowrap">
                    <input id="fld_category_lbl_<?php echo $viewBean->type; ?>"
                           class=""
                           type="text"
                           name="fld_category_lbl"
                           value="<?php echo attr($criteria->getCategoryLabel());?>" />
                    <a href="javascript:;" id="change_category_<?php echo $viewBean->type; ?>" onclick="top.restoreSession()"><i class="fa fa-pencil"></i></a>

                    <input type="hidden" id="fld_category_<?php echo $viewBean->type; ?>" name="fld_category" value="<?php echo attr($criteria->category); ?>" />
                </td>
            </tr>
            <!-- item -->
            <tr>
                <td class="text-right bold">Item:</td>
                <td class="nowrap"><?php
                         echo textfield_simple(array("id" => "fld_item_lbl_".$viewBean->type,
                            "name" => "fld_item_lbl",
                            "title" => '',
                            "value" => $criteria->getItemLabel() )); ?>
                    <a href="javascript:;" id="change_item" onclick="top.restoreSession()"><i class="fa fa-pencil"></i></a>
                    <input type="hidden" id="fld_item_<?php echo $viewBean->type; ?>" name="fld_item" value="<?php echo attr($criteria->item); ?>" />
                </td>
            </tr>
            <!-- completed -->
            <tr>
                <td class="text-right">
                    <span data-field="fld_completed"><?php echo xlt('Does this need to be marked "Completed"'); ?>?</span>
                </td>
                <td class="nowrap"><select data-grp-tgt="" class="" type="dropdown" name="fld_completed" id="">
                        <option id="" value="">--<?php echo xlt('Select'); ?>--</option>
                        <option id="Yes" value="yes" <?php echo $criteria->completed ? "SELECTED" : "" ?>><?php echo xlt('Yes'); ?></option>
                        <option id="No" value="no" <?php echo !$criteria->completed ? "SELECTED" : "" ?>><?php echo xlt('No'); ?></option>
                    </select>
                </td>
            </tr>
            <!-- frequency -->
            <tr>
                <td class="text-right">
                    <span data-field="fld_frequency"><?php echo xlt('Frequency'); ?></span>
                </td>
                <td class="tight nowrap">
                    <select data-grp-tgt="" type="dropdown" name="fld_frequency_comparator" id="">
                        <option id="" value=""></option>
                        <option id="le" value="le" <?php echo $criteria->frequencyComparator == "le" ? "SELECTED" : "" ?>><?php echo "<=" ;?></option>
                        <option id="lt" value="lt" <?php echo $criteria->frequencyComparator == "lt" ? "SELECTED" : "" ?>><?php echo "<" ;?></option>
                        <option id="eq" value="eq" <?php echo $criteria->frequencyComparator == "eq" ? "SELECTED" : "" ?>><?php echo "=" ;?></option>
                        <option id="gt" value="gt" <?php echo $criteria->frequencyComparator == "gt" ? "SELECTED" : "" ?>><?php echo ">" ;?></option>
                        <option id="ge" value="ge" <?php echo $criteria->frequencyComparator == "ge" ? "SELECTED" : "" ?>><?php echo ">=" ;?></option>
                        <option id="ne" value="ne" <?php echo $criteria->frequencyComparator == "ne" ? "SELECTED" : "" ?>><?php echo "!=" ;?></option>
                    </select>
                    <input data-grp-tgt="fld_frequency" class="field short"
                           type="text"
                           name="fld_frequency"
                           value="<?php echo attr($criteria->frequency); ?>" />
                </td>
            </tr>

            <!-- optional/required and inclusion/exclusion fields -->
            <?php echo common_fields(array( "criteria" => $criteria)); ?>
        </table>
    </div>
</div>

<script>
    $(function() {
        $("#change_category_<?php echo $viewBean->type; ?>").trigger('click');
        $("#change_item_<?php echo $viewBean->type; ?>").trigger('click');
        $("[name='edit_action_cancel").click(function () {
            $("#show_actions_edit").hide();
            $("#show_actions").show();
        });
        $("#submit_action").click(function() {
            $("#frm_submit_<?php echo $viewBean->type; echo "_".attr($group_id); ?>").submit();
        });
        $('[data-toggle="popover"]').popover();
    });
</script>
