<?php

/**
 * interface/super/rules/controllers/detail/view/view.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Aron Racho <aron@mi-squared.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2011 Aron Racho <aron@mi-squared.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$rule = $viewBean->rule ?>

<script src="<?php js_src('detail.js') ?>"></script>
<script>
    var detail = new rule_detail( {editable: <?php echo $rule->isEditable() ? "true" : "false"; ?>});
    detail.init();
</script>

<br />
<header class="title"><?php echo xlt('Rule Detail'); ?>
    <a href="index.php?action=browse!list" class="btn btn-primary btn-back mx-auto" onclick="top.restoreSession();"><?php echo xlt('Back'); ?></a>
</header>
<hr />
<div class="rule_detail">
    <!--         -->
    <!-- summary -->
    <!--         -->
    <div class="section">
        <p class="header">
            <?php echo xlt('Summary'); ?>
            <a href="index.php?action=edit!summary&id=<?php echo attr_url($rule->id); ?>"
               class="action_link" id="edit_summary" onclick="top.restoreSession()">(<?php echo xlt('edit'); ?>)</a>
        </p>
        <p><b><?php echo xlt($rule->title); ?></b>
        (<?php echo implode_funcs(", ", $rule->getRuleTypeLabels(), array('xlt')); ?>)
        </p>
        <p><?php echo xlt('Bibliographic Citation'); ?><b>:</b>&nbsp;<?php echo text($rule->bibliographic_citation); ?></p>
        <p><?php echo xlt('Developer'); ?><b>:</b>&nbsp;<?php echo text($rule->developer); ?></p>
        <p><?php echo xlt('Funding Source'); ?><b>:</b>&nbsp;<?php echo text($rule->funding_source); ?></p>
        <p><?php echo xlt('Release'); ?><b>:</b>&nbsp;<?php echo text($rule->release); ?></p>
        <p><?php echo xlt('Web Reference'); ?><b>:</b>&nbsp;<?php echo text($rule->web_ref); ?></p>
        <p><?php echo xlt('Referential CDS (codetype:code)'); ?><b>:</b>&nbsp;<?php echo text($rule->linked_referential_cds); ?></p>
    </div>
    <!--                    -->
    <!-- reminder intervals -->
    <!--                    -->
    <?php $intervals = $rule->reminderIntervals; if ($intervals) { ?>
    <div class="section">
        <p class="header">
            <?php echo xlt('Reminder intervals'); ?>
            <a href="index.php?action=edit!intervals&id=<?php echo attr_url($rule->id); ?>" class="action_link" onclick="top.restoreSession()">(<?php echo xlt('edit'); ?>)</a>
        </p>

        <?php if ($intervals->getTypes()) {?>
        <p>
            <div>
                <span class="left_col colhead"><u><?php echo xlt('Type'); ?></u></span>
                <span class="end_col colhead"><u><?php echo xlt('Detail'); ?></u></span>
            </div>

            <?php foreach ($intervals->getTypes() as $type) {?>
                <div>
                <span class="left_col"><?php echo xlt($type->lbl); ?></span>
                <span class="end_col">
                    <?php echo text($intervals->displayDetails($type)); ?>
                </span>
                </div>
            <?php } ?>
        </p>
        <?php } else { ?>
        <p><?php echo xlt('None defined'); ?></p>
        <?php } ?>
    </div>
    <?php } ?>

    <!--                      -->
    <!-- rule filter criteria -->
    <!--                      -->
    <?php $filters = $rule->filters; if ($filters) { ?>
    <div class="section">
        <p class="header"><?php echo xlt('Demographics filter criteria'); ?> <a href="index.php?action=edit!add_criteria&id=<?php echo attr_url($rule->id); ?>&criteriaType=filter" class="action_link" onclick="top.restoreSession()">(<?php echo xlt('add'); ?>)</a></p>
        <p>
            <?php if ($filters->criteria) { ?>
                <div>
                    <span class="left_col">&nbsp;</span>
                    <span class="mid_col"><u><?php echo xlt('Criteria'); ?></u></span>
                    <span class="mid_col"><u><?php echo xlt('Characteristics'); ?></u></span>
                    <span class="end_col"><u><?php echo xlt('Requirements'); ?></u></span>
                </div>

                <?php foreach ($filters->criteria as $criteria) { ?>
                    <div>
                        <span class="left_col">
                            <a href="index.php?action=edit!filter&id=<?php echo attr_url($rule->id); ?>&guid=<?php echo attr_url($criteria->guid); ?>"
                               class="action_link" onclick="top.restoreSession()">
                                (<?php echo xlt('edit'); ?>)
                            </a>
                            <a href="index.php?action=edit!delete_filter&id=<?php echo attr_url($rule->id); ?>&guid=<?php echo attr_url($criteria->guid); ?>"
                               class="action_link" onclick="top.restoreSession()">
                                (<?php echo xlt('delete'); ?>)
                            </a>
                        </span>
                        <span class="mid_col"><?php echo( text($criteria->getTitle()) ); ?></span>
                        <span class="mid_col"><?php echo( text($criteria->getCharacteristics()) ); ?></span>
                        <span class="end_col"><?php echo( text($criteria->getRequirements()) ); ?></span>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p><?php echo xlt('None defined'); ?></p>
            <?php } ?>
        </p>
    </div>
    <?php } ?>

    <!--                      -->
    <!-- rule groups          -->
    <!--                      -->


    <div class="section">
    <p class="header"><?php echo xlt('Target/Action Groups'); ?></p>
    <?php $groupId = 0;
    foreach ($rule->groups as $group) {
        $groupId = $group->groupId; ?>
            <div class="group">
            <!--                      -->
            <!-- rule target criteria -->
            <!--                      -->

            <?php $targets = $group->ruleTargets; if ($targets) { ?>
        <div class="section">
            <p class="header"><?php echo xlt('Clinical targets'); ?>
                <a href="index.php?action=edit!add_criteria&id=<?php echo attr_url($rule->id); ?>&group_id=<?php echo attr_url($group->groupId); ?>&criteriaType=target" class="action_link" onclick="top.restoreSession()">
                    (<?php echo xlt('add'); ?>)
                </a>
            </p>
            <p>
                <?php if ($targets->criteria) { ?>
                    <div>
                        <span class="left_col">&nbsp;</span>
                        <span class="mid_col"><u><?php echo xlt('Criteria'); ?></u></span>
                        <span class="mid_col"><u><?php echo xlt('Characteristics'); ?></u></span>
                        <span class="end_col"><u><?php echo xlt('Requirements'); ?></u></span>
                    </div>

                    <?php foreach ($targets->criteria as $criteria) { ?>
                        <div class="form-row">
                            <span class="left_col">
                                <a href="index.php?action=edit!target&id=<?php echo attr_url($rule->id); ?>&guid=<?php echo attr_url($criteria->guid); ?>"
                                   class="action_link" onclick="top.restoreSession()">
                                    (<?php echo xlt('edit'); ?>)
                                </a>
                                <a href="index.php?action=edit!delete_target&id=<?php echo attr_url($rule->id); ?>&guid=<?php echo attr_url($criteria->guid); ?>"
                                   class="action_link" onclick="top.restoreSession()">
                                    (<?php echo xlt('delete'); ?>)
                                </a>
                            </span>
                            <span class="mid_col"><?php echo( text($criteria->getTitle()) ); ?></span>
                            <span class="mid_col"><?php echo( text($criteria->getCharacteristics()) ); ?></span>
                            <span class="end_col">
                                    <?php echo( text($criteria->getRequirements()) ) ?>
                                    <?php echo is_null($criteria->getInterval()) ?  "" :
                                    " | " . xlt('Interval') . ": " . text($criteria->getInterval()); ?>
                            </span>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <p><?php echo xlt('None defined'); ?></p>
                <?php } ?>
            </p>
        </div>
            <?php } ?>

            <!--              -->
            <!-- rule actions -->
            <!--              -->
            <?php $actions = $group->ruleActions; if ($actions) { ?>
        <div class="section">
            <p class="header"><?php echo xlt('Actions'); ?>
                <a href="index.php?action=edit!add_action&id=<?php echo attr_url($rule->id); ?>&group_id=<?php echo attr_url($group->groupId);?>" class="action_link" onclick="top.restoreSession()">
                    (<?php echo xlt('add'); ?>)
                </a>
            </p>
            <p>
                <?php if ($actions->actions) { ?>
                    <div>
                        <span class="left_col">&nbsp;</span>
                        <span class="end_col"><u><?php echo xlt('Category/Title'); ?></u></span>
                    </div>

                    <div>
                    <?php foreach ($actions->actions as $action) { ?>
                        <span class="left_col">
                            <a href="index.php?action=edit!action&id=<?php echo attr_url($rule->id); ?>&guid=<?php echo attr_url($action->guid); ?>"
                               class="action_link" onclick="top.restoreSession()">
                                (<?php echo xlt('edit'); ?>)</a>
                            <a href="index.php?action=edit!delete_action&id=<?php echo attr_url($rule->id); ?>&guid=<?php echo attr_url($action->guid); ?>"
                               class="action_link" onclick="top.restoreSession()">
                                (<?php echo xlt('delete'); ?>)</a>
                        </span>
                        <span class="end_col"><?php echo text($action->getTitle()); ?></span>
                    <?php } ?>
                    </div>
                <?php } else { ?>
                    <p><?php echo xlt('None defined'); ?></p>
                <?php } ?>
            </p>
        </div>
            <?php } ?>
            </div>
        <?php
    } // iteration over groups ?>
        <div class="group">
            <?php $nextGroupId = $groupId + 1; ?>
            <div class="section">
                <p class="header"><?php echo xlt('Clinical targets'); ?>
                    <a href="index.php?action=edit!add_criteria&id=<?php echo attr_url($rule->id); ?>&group_id=<?php echo attr_url($nextGroupId); ?>&criteriaType=target" class="action_link" onclick="top.restoreSession()">
                        (<?php echo xlt('add'); ?>)
                    </a>
                </p>
            </div>
            <div class="section">
                <p class="header"><?php echo xlt('Actions'); ?>
                    <a href="index.php?action=edit!add_action&id=<?php echo attr_url($rule->id); ?>&group_id=<?php echo attr_url($nextGroupId); ?>" class="action_link" onclick="top.restoreSession()">
                        (<?php echo xlt('add'); ?>)
                    </a>
                </p>
            </div>
        </div>

    </div>

</div>
