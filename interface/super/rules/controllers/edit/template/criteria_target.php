<?php
    /**
     * interface/super/rules/controllers/edit/template/criteria.php
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

<body class='body_top'>

<?php
    $rule = $viewBean->rule;
    $ruleId = $viewBean->id;
    $criteria = $viewBean->criteria;
    $groupId = $criteria->groupId;
    $rt_uid = $criteria->rt_uid;
?>

<?php $XgroupId = _get('group_id'); ?>

<script language="javascript" src="<?php js_src('edit.js') ?>"></script>
<script type="text/javascript">
    var edit = new rule_edit( {});
    edit.init();
</script>

<!-- a href="javascript:;" class="iframe_medium css_button" id="btn_save" onclick="top.restoreSession()"><span><?php echo xlt('Save'); ?></span></a>
-->

<button onclick="top.restoreSession();location.href='index.php?action=detail!view&amp;id=<?php echo attr_url($rule->id); ?>'"
        class="btn btn-sm btn-primary icon_2"
        title="Cancel"><i class="fa fa-times"></i>
</button>
<button id="frm_targets_save_<?php echo attr($groupId); ?>"
        class="btn btn-sm btn-primary icon_3"
        id="frm_targets_save_<?php echo attr($groupId); ?>"
        type="button"
        title="<?php echo attr('Save'); ?>"><i class="fa fa-save"></i>
</button>
<button class="btn btn-sm btn-primary icon_1"
        id="show_target_help"
        data-toggle="modal"
        data-target="#help_targets"
        title="Open the Help:: Who will this CR affect?"><i class="fa fa-question"></i>
</button>
<div class="col-12">
    <form action="index.php?action=edit!submit_criteria" method="post" id="frm_submit_target_<?php echo attr($groupId); ?>" onsubmit="return top.restoreSession()">
        <input type="hidden" name="id" value="<?php echo attr($rule->id); ?>"/>
        <input type="hidden" name="group_id" value="<?php echo attr($groupId); ?>"/>
        <input type="hidden" name="type" value="<?php echo attr($viewBean->type); ?>"/>
        <input type="hidden" name="rt_uid" value="<?php echo attr($criteria->rt_uid); ?>"/>
        <input type="hidden" name="criteriaTypeCode" value="<?php echo attr($criteria->criteriaType->code); ?>"/>

        <!-- ----------------- -->
        <?php
    
            if (file_exists($viewBean->_view_body)) {
                require_once($viewBean->_view_body);
            }
        ?>
        <!-- ----------------- -->
    </form>
</div>

<div id="required_msg" class="col-8 small hidden">
    <!-- <span class="required">*</span><?php echo xlt('Required fields'); ?> -->
</div>

</body>
<script>
    $(function() {
        $("[id^='edit_target_").click(function () {
            var h = $(this).data();
            var thisType = h.type;
            var thisCriteriaType = h.criteriatype;
            var group = h.group;
            
            top.restoreSession();
            var id = this.id.match(/edit_target_(.*)/)[1];
            var url = 'index.php?action=edit!choose_criteria&id='+id+'&type='+thisType+'&criteriaType='+thisCriteriaType+'&group_id='+group;
            $.ajax({
                       type: 'POST',
                       url: url,
                       data: {}
                   }).done(function (data) {
                $("#show_targets_edit_<?php echo $groupId; ?>").html(data);
            });
            
        });
        $("[id^='frm_targets_save_']").click(function() {
            var group = this.id.match(/frm_targets_save_(.*)/)[1];
            $("#frm_submit_target_"+group).submit();
        });
    });
    

</script>

</html>
