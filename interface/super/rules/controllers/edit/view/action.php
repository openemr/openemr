<?php
/**
 * interface/super/rules/controllers/edit/view/action.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Aron Racho <aron@mi-squared.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2011 Aron Racho <aron@mi-squared.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<?php
    $action = $viewBean->action;
    $rule = $viewBean->rule;    ?>

<script language="javascript" src="<?php js_src('edit.js') ?>"></script>
<script language="javascript" src="<?php js_src('bucket.js') ?>"></script>
<script type="text/javascript">
    var edit = new rule_edit( {});
    edit.init();

    var bucket = new bucket( {} );
    bucket.init();
</script>

    <div class="col-12">
        <button class="btn-sm btn-primary icon_1"
                        id="edit_action_cancel">
                    <i class="fa fa-times heavy"></i></button>
                <button id="submit_action_<?php echo attr($action->groupId); ?>"
                        class="btn-sm btn-primary icon_2"
                        title='<?php echo xla('Save Goal'); ?>'> <i class="fa fa-save heavy"></i>
                </button>
          
        <span class="title3 text-left"><?php
        if ($action->ra_uid) {
            echo xlt('Edit this Action');
        } else {
            echo xlt('Add this Action');
        }?>:</span>
    </div>
    <div class="col-12">
    
        <form action="index.php?action=edit!submit_action"
              method="post"
              id="frm_submit_action_<?php echo attr($action->groupId); ?>"
              onsubmit="return top.restoreSession()">
            <input type="hidden" name="ra_uid" value="<?php echo attr($action->ra_uid); ?>"/>
            <input type="hidden" name="id" value="<?php echo attr($action->id); ?>"/>
            <input type="hidden" name="group_id" value="<?php echo attr($action->groupId); ?>"/>
        
            <!-- custom rules input -->
        
            <!-- category -->
            <table class="table table-bordered table-info table-responsive table-hover text-center">
                <tr>
                    <td nowrap="nowrap">
                        <span class='bold'
                              data-field="Category"
                              data-toggle='popover'
                              data-trigger="hover"
                              data-placement="left"
                              title='<?php echo xla('Action Categories'); ?>
                              data-content='<?php echo xla('A CR exists to reminder you to perform an action. Actions can be grouped into familiar categories, such as "Perform an Assessment" or "Measure a Value". For this action, choose the category which best describes what you are prompting the medical staff to complete. You can select this category from a list of pre-existing Categories by clicking the Pencil icon or you can create a new category by simply typing into this text box. Newly created categories are added to the list "Clinical Rule Action Category". The pre-existing categories are drawn from this list.'); ?>'>
                            <?php echo xlt('Action Category'); ?></span><br />
    
                        <input id="fld_category_lbl_action"
                               class="form-control"
                               type="text"
                               name="fld_category_lbl"
                               value="<?php echo attr($action->getCategoryLabel());?>" />
                                     <a href="javascript:;" id="change_category_action" onclick="top.restoreSession()"><i class="fa fa-pencil"></i></a>
                        <input type="hidden" id="fld_category_action" name="fld_category" value="<?php echo attr($action->category); ?>" />
                    </td>
                </tr>
                
                <!-- item -->
                <tr>
                    <td>
                        <span class='bold'
                              data-field="Item"
                              data-toggle='popover'
                              data-trigger="hover"
                              data-placement="auto, right"
                              title='<?php echo xla('Action Items'); ?>
                              data-content='<?php echo xla('Items are the actual thing you want the user to do. Measure the Systolic BP, do an EKG, get a colonoscopy, perform a smoking intervention...'); ?>'>
                            <?php echo xlt('Action Item'); ?>
                        </span><br />
                        <input id="fld_item_lbl_action"
                               class="form-control"
                               type="text"
                               name="fld_item_lbl"
                               value="<?php echo attr($action->getItemLabel());?>" />
                        <a href="javascript:;" id="change_item_action" onclick="top.restoreSession()"><i class="fa fa-pencil"></i></a>
                        <input type="hidden" id="fld_item_action" name="fld_item" value="<?php echo attr($action->item); ?>" />
                    </td>
                </tr>
                <tr>
                    <td class="text-center">
                        <span class='bold'
                              data-field="Item"
                              data-toggle='popover'
                              data-trigger="hover"
                              data-placement="auto, top"
                              container="body"
                              title='<?php echo xla('Completing Passive Alerts'); ?>'
                              data-html="true"
                              data-content='<?php echo xlas('A Passive Alert is displayed in the CR widget with the values of "Category:Item".
                              These words can be linked to a pop-up to add a note and/or mark it completed if required.
                              If the pop-up not required, you can change this to display <b>no</b> link ("Category:Item" will be black) or
                              link to anything desired by entering that url as an Action link.
                              This does not affect the References Link <i class="fa fa-link"></i> created in the CR Summary.'); ?>'
                              <img width="250px" src="<?php echo $GLOBAL['webroot'];?>/public/images/CR_widget.png">'>
                            <?php echo xlt('Passive Alerts'); ?></span>
                        <br />
                        <!-- custom rules input -->
                        <span class="" data-field="fld_custom_input">
                            <?php echo xlt('How do we know this was completed'); ?>:
           
                            <select data-grp-tgt="" class="text-center" type="dropdown" id="fld_custom_input" name="fld_custom_input">
                                <option id="Yes" value="yes" <?php echo $action->customRulesInput ? "SELECTED" : "" ?>><?php echo xlt('Link to a "Marked completed" pop-up'); ?></option>
                                <option id="No" value="no" <?php echo !$action->customRulesInput ? "SELECTED" : "" ?>><?php echo xlt('Check DB field for "completed"'); ?></option>
                            </select>
                        </span>
                        <span id="no_PopUp_needed" class="bolder">
                            <hr />
                            <?php echo xlt('Option: add a personalized link to this Action in the CR widget'); ?>:
                            <!-- reminder link  -->
                            <?php echo textfield_row(array("id" => "fld_link",
                                "name" => "fld_link",
                                "class" => "margin-auto",
                                "value" => $action->reminderLink)); ?>
                        </span>
             
                    </td>
                </tr>
                <tr>
                    <!-- reminder message  -->
                    <td class="text-center">
                        <span class='bold'
                              data-field="Item"
                              data-toggle='popover'
                              data-trigger="hover"
                              data-placement="auto, top"
                              container="body"
                              title=''<?php echo xla('Delivering Patient Reminders'); ?>
                              data-html="true"
                              data-content=''>
                            <?php echo xlt('Patient Reminders'); ?></span>
                        <br />

                        <?php echo ('If this CR includes a Patient Reminder (depending on your set-up) add this message');?>:
                            <textarea class="margin-auto"
                                      name="fld_message"
                                      id="fld_message"><?php echo text($action->reminderMessage); ?></textarea>
                    </td>
                </tr>
               
            </table>
        </form>
    </div>
    
    <div id="required_msg" class="small hidden">
        <!-- <span class="required">*</span><?php echo xlt('Required fields'); ?>  -->
    </div>
<script>
    $(function() {
        $("#change_category_action").trigger('click');
        $("#change_item_action").trigger('click');
        $("#edit_action_cancel").click(function () {
            $("#show_actions_edit_<?php echo attr($action->groupId); ?>").hide();
            $("#show_actions_<?php echo attr($action->groupId); ?>").show();
        });
        $("[id^='submit_action_']").click(function() {
            var group = this.id.match(/submit_action_(.*)/)[1];
            top.restoreSession();
            $("#frm_submit_action_"+group).submit();
        });
        $("#fld_custom_input").change(function() {
           var item = $("#fld_custom_input").val();
           if (item == 'yes') {
               $("#no_PopUp_needed").hide();
           } else {
               $("#no_PopUp_needed").show();
           }
        });
        $("#fld_custom_input").trigger('change');
    
        $('[data-toggle="popover"]').popover();
    });
</script>
