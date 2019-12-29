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
    
    use OpenEMR\Core\Header;

?>
<html>
<head>
    <?php Header::setupHeader(['no_bootstrap', 'no_fontawesome', 'no_textformat', 'no_dialog']); ?>

    <link rel="stylesheet" href="<?php css_src('rules.css') ?>" type="text/css">
</head>

<body class='body_top'>
<?php $rule = $viewBean->rule; ?>
<?php $ruleId = $viewBean->id; ?>
<?php $criteria = $viewBean->criteria; ?>

<script language="javascript" src="<?php js_src('edit.js') ?>"></script>
<script type="text/javascript">
    var edit = new rule_edit( {});
    edit.init();
</script>

<button type="submit" form="frm_submit"
        class="btn btn-sm btn-primary icon_3"
        id="frm_filters_save"
        title="Save"><i class="fa fa-save"></i>
</button>
<button onclick="top.restoreSession();location.href='index.php?action=detail!view&amp;id=<?php echo attr_url($ruleId); ?>'"
        class="btn btn-sm btn-primary icon_2"
        title="Cancel"><i class="fa fa-times"></i>
</button>
<button class="btn btn-sm btn-primary icon_1"
        id="show_filters_help"
        data-toggle="modal"
        data-target="#help_filters"
        title="Open the Help:: Who will this CR affect?"><i class="fa fa-question"></i>
</button>

<div class="col-12">
    <span class="title text-left"><?php echo xlt('Step 1: Who are we targeting?'); ?> </span>
</div>
<div class="col-12">
    <form action="index.php?action=edit!submit_criteria" method="post" id="frm_submit" onsubmit="return top.restoreSession()">

        <input type="hidden" name="id" value="<?php echo attr($rule->id); ?>"/>
        <input type="hidden" name="group_id" value="<?php echo attr($criteria->groupId); ?>"/>
        <input type="hidden" name="guid" value="<?php echo attr($criteria->guid); ?>"/>
        <input type="hidden" name="rf_uid" value="<?php echo attr($criteria->rf_uid); ?>"/>
        <input type="hidden" name="type" value="<?php echo attr($viewBean->type); ?>"/>
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
        $("[id^='edit_filter_").click(function () {
            var h = $(this).data();
            var thisType = h.type;
            var thisCriteriaType = h.criteriatype;
            
            top.restoreSession();
            var id = this.id.match(/edit_filter_(.*)/)[1];
            var url = 'index.php?action=edit!choose_criteria&id='+id+'&type='+thisType+'&criteriaType='+thisCriteriaType;
            $.ajax({
                       type: 'POST',
                       url: url,
                       data: {}
                   }).done(function (data) {
                $("#show_filters_edit").html(data);
            });
            
        });
    });

</script>

</html>
