<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

$rule = $viewBean->rule ?>

<script language="javascript" src="<?php js_src('detail.js') ?>"></script>
<script type="text/javascript">
    var detail = new rule_detail( {editable: <?php echo $rule->isEditable() ? "true":"false"; ?>});
    detail.init();
</script>

<table class="header">
  <tr >
        <td class="title"><?php echo out( xl( 'Rule Detail' ) ); ?></td>
        <td>
            <a href="index.php?action=browse!list" class="iframe_medium css_button" onclick="top.restoreSession()"><span><?php echo out( xl( 'Back' ) ); ?></span></a>
        </td>
  </tr>
</table>

<div class="rule_detail">
    <!--         -->
    <!-- summary -->
    <!--         -->
    <div class="section text">
        <p class="header">
            <?php echo out( xl( 'Summary' ) ); ?>
            <a href="index.php?action=edit!summary&id=<?php echo out( $rule->id ); ?>"
               class="action_link" id="edit_summary" onclick="top.restoreSession()">(<?php echo out( xl( 'edit' ) ); ?>)</a>
        </p>
        <p><b><?php echo out( xl( $rule->title ) ); ?></b>
        (<?php echo implode_funcs( ", ", $rule->getRuleTypeLabels(), array( 'xl', 'out' ) ); ?>)
        </p>
    </div>

    <!--                    -->
    <!-- reminder intervals -->
    <!--                    -->
    <?php $intervals = $rule->reminderIntervals; if ( $intervals) { ?>
    <div class="section text">
        <p class="header">
            <?php echo out( xl( 'Reminder intervals' ) ); ?>
            <a href="index.php?action=edit!intervals&id=<?php echo $rule->id ?>" class="action_link" onclick="top.restoreSession()">(<?php echo out( xl( 'edit' ) ); ?>)</a>
        </p>

        <?php if ( $intervals->getTypes() ) {?>
        <p>
            <div>
                <span class="left_col colhead"><u><?php echo out( xl( 'Type' ) ); ?></u></span>
                <span class="end_col colhead"><u><?php echo out( xl( 'Detail' ) ); ?></u></span>
            </div>

            <?php foreach($intervals->getTypes() as $type) {?>
                <div>
                <span class="left_col"><?php echo out( xl( $type->lbl ) ); ?></span>
                <span class="end_col">
                    <?php echo out( $intervals->displayDetails( $type ) ); ?>
                </span>
                </div>
            <?php } ?>
        </p>
        <?php } else { ?>
        <p><?php echo out( xl( 'None defined' ) ); ?></p>
        <?php } ?>
    </div>
    <?php } ?>

    <!--                      -->
    <!-- rule filter criteria -->
    <!--                      -->
    <?php $filters = $rule->filters; if ( $filters ) { ?>
    <div class="section text">
        <p class="header"><?php echo out( xl( 'Demographics filter criteria' ) ); ?> <a href="index.php?action=edit!add_criteria&id=<?php echo out( $rule->id ); ?>&criteriaType=filter" class="action_link" onclick="top.restoreSession()">(<?php echo out( xl( 'add' ) ); ?>)</a></p>
        <p>
            <?php if ( $filters->criteria ) { ?>

                <div>
                    <span class="left_col">&nbsp;</span>
                    <span class="mid_col"><u><?php echo out( xl( 'Criteria' ) ); ?></u></span>
                    <span class="mid_col"><u><?php echo out( xl( 'Characteristics' ) ); ?></u></span>
                    <span class="end_col"><u><?php echo out( xl( 'Requirements' ) ); ?></u></span>
                </div>

                <?php foreach($filters->criteria as $criteria) { ?>
                    <div>
                        <span class="left_col">
                            <a href="index.php?action=edit!filter&id=<?php echo out( $rule->id ); ?>&guid=<?php echo out( $criteria->guid ); ?>"
                               class="action_link" onclick="top.restoreSession()">
                                (<?php echo out( xl( 'edit' ) ); ?>)
                            </a>
                            <a href="index.php?action=edit!delete_filter&id=<?php echo out( $rule->id ); ?>&guid=<?php echo out( $criteria->guid ); ?>" 
                               class="action_link" onclick="top.restoreSession()">
                                (<?php echo out( xl( 'delete' ) ); ?>)
                            </a>
                        </span>
                        <span class="mid_col"><?php echo( out( $criteria->getTitle() ) ); ?></span>
                        <span class="mid_col"><?php echo( out( $criteria->getCharacteristics() ) ); ?></span>
                        <span class="end_col"><?php echo( out( $criteria->getRequirements() ) ); ?></span>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p><?php echo out( xl( 'None defined' ) ); ?></p>
            <?php } ?>
        </p>
    </div>
    <?php } ?>
    
    <!--                      -->
    <!-- rule groups          -->
    <!--                      -->
    
    
    <div class="section text">
	<p class="header"><?php echo out( xl( 'Target/Action Groups' ) ); ?></p>
	<?php $groupId = 0; foreach ( $rule->groups as $group ) { $groupId = $group->groupId; ?>
		<div class="group">
        <!--                      -->
        <!-- rule target criteria -->
        <!--                      -->
        
        <?php $targets = $group->ruleTargets; if ( $targets ) { ?>
        <div class="section text">
            <p class="header"><?php echo out( xl( 'Clinical targets' ) ); ?> 
            	<a href="index.php?action=edit!add_criteria&id=<?php echo out( $rule->id ); ?>&group_id=<?php echo out( $group->groupId ); ?>&criteriaType=target" class="action_link" onclick="top.restoreSession()">
            		(<?php echo out( xl( 'add' ) ); ?>)
            	</a>
            </p>
            <p>
                <?php if ( $targets->criteria ) { ?>
    
                    <div>
                        <span class="left_col">&nbsp;</span>
                        <span class="mid_col"><u><?php echo out( xl( 'Criteria' ) ); ?></u></span>
                        <span class="mid_col"><u><?php echo out( xl( 'Characteristics' ) ); ?></u></span>
                        <span class="end_col"><u><?php echo out( xl( 'Requirements' ) ); ?></u></span>
                    </div>
    
                    <?php foreach($targets->criteria as $criteria) { ?>
                        <div class="row">
                            <span class="left_col">
                                <a href="index.php?action=edit!target&id=<?php echo out( $rule->id ); ?>&guid=<?php echo out( $criteria->guid ); ?>"
                                   class="action_link" onclick="top.restoreSession()">
                                    (<?php echo out( xl( 'edit' ) ); ?>)
                                </a>
                                <a href="index.php?action=edit!delete_target&id=<?php echo out( $rule->id ); ?>&guid=<?php echo out( $criteria->guid ); ?>"
                                   class="action_link" onclick="top.restoreSession()">
                                    (<?php echo out( xl( 'delete' ) ); ?>)
                                </a>
                            </span>
                            <span class="mid_col"><?php echo( out( $criteria->getTitle() ) ); ?></span>
                            <span class="mid_col"><?php echo( out( $criteria->getCharacteristics() ) ); ?></span>
                            <span class="end_col">
                                    <?php echo( $criteria->getRequirements() ) ?>
                                    <?php echo is_null( $criteria->getInterval() ) ?  "" :
                                    " | " . out( xl( 'Interval' ) ) . ": " . out( $criteria->getInterval() ); ?>
                            </span>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <p><?php echo out( xl( 'None defined' ) ); ?></p>
                <?php } ?>
    
            </p>
        </div>
        <?php } ?>
    
        <!--              -->
        <!-- rule actions -->
        <!--              -->
        <?php $actions = $group->ruleActions; if ( $actions ) { ?>
        <div class="section text">
            <p class="header"><?php echo out( xl( 'Actions' ) ); ?>
                <a href="index.php?action=edit!add_action&id=<?php echo out( $rule->id ); ?>&group_id=<?php echo out( $group->groupId );?>" class="action_link" onclick="top.restoreSession()">
                    (<?php echo out( xl( 'add' ) ); ?>)
                </a>
            </p>
            <p>
                <?php if ( $actions->actions) { ?>
                    <div>
                        <span class="left_col">&nbsp;</span>
                        <span class="end_col"><u><?php echo out( xl( 'Category/Title' ) ); ?></u></span>
                    </div>
    
                    <div>
                    <?php foreach($actions->actions as $action) { ?>
                        <span class="left_col">
                            <a href="index.php?action=edit!action&id=<?php echo out( $rule->id ); ?>&guid=<?php echo out( $action->guid ); ?>"
                               class="action_link" onclick="top.restoreSession()">
                                (<?php echo out( xl( 'edit' ) ); ?>)</a>
                            <a href="index.php?action=edit!delete_action&id=<?php echo out( $rule->id ); ?>&guid=<?php echo out( $action->guid ); ?>"
                               class="action_link" onclick="top.restoreSession()">
                                (<?php echo out( xl( 'delete' ) ); ?>)</a>
                        </span>
                        <span class="end_col"><?php echo out( $action->getTitle() ); ?></span>
                    <?php } ?>
                    </div>
                <?php } else { ?>
                    <p><?php echo out( xl( 'None defined' ) ); ?></p>
                <?php } ?>
            </p>
        </div>
        <?php } ?>
    	</div>
    <?php } // iteration over groups ?>
    	<div class="group">
    		<?php $nextGroupId = $groupId + 1; ?>
    		<div class="section text">
        		<p class="header"><?php echo out( xl( 'Clinical targets' ) ); ?> 
        			<a href="index.php?action=edit!add_criteria&id=<?php echo out( $rule->id ); ?>&group_id=<?php echo $nextGroupId; ?>&criteriaType=target" class="action_link" onclick="top.restoreSession()">
        				(<?php echo out( xl( 'add' ) ); ?>)
        			</a>
        		</p>
    		</div>
    		<div class="section text">
        		<p class="header"><?php echo out( xl( 'Actions' ) ); ?>
                    <a href="index.php?action=edit!add_action&id=<?php echo out( $rule->id ); ?>&group_id=<?php echo $nextGroupId; ?>" class="action_link" onclick="top.restoreSession()">
                        (<?php echo out( xl( 'add' ) ); ?>)
                    </a>
                </p>
            </div>
    	</div>
    
    </div>

</div>
