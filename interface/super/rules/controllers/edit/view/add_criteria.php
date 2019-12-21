<?php
    /**
     * interface/super/rules/controllers/edit/view/add_criteria.php
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

<?php $allowed = $viewBean->allowed?>
<?php $ruleId = $viewBean->id;?>
<?php $groupId = $viewBean->groupId;?>

<?php

if ($viewBean->type != 'filter') { ?>
    <div class="col-12">
        Now with the Target Group of patients defined in Step 1, you can look at the charts of these patients for a specific item.
        This is a powerful search tool allowing criteria like  "if present", "if not present", "if it occurred more than a year ago", etc.
        You can add multiple criteria to finely hone when this alert(s) will fire.
    </div>
    <?php }  else { ?>
        <div class="col-12">
        
            Each <B>Clinical Reminder</B> can target one or more sub-groups of patients.
            Refine your target groups by selecting one of the options below.
            If you choose none, this CR applies to everyone in your practice.
        </div>
    <?php } ?>
        <div class="col-12 text-center">

        <?php foreach ($allowed as $type) { ?>
                     <label>
                        <button class="btn btn-primary"
                                type="button"
                                id="edit_<?php echo attr($viewBean->type); ?>_<?php echo attr_url($ruleId); ?>"
                                data-type="<?php echo attr_url($viewBean->type); ?>"
                                data-group="<?php echo $groupId; ?>"
                                data-criteriatype="<?php echo attr_url($type->code); ?>">
                            <?php echo xlt($type->lbl); ?>
                        </button>
                    </label>
            
                
            <?php } ?>
        </div>
    <script>
$(function() {
    $("#frm_targets_save_<?php echo $groupId; ?>").hide();
    $("#frm_filters_save").hide();
    
})
</script>