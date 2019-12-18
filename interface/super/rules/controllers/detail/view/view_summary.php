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
    
    
    $rule = $viewBean->rule;
    $more='';
    $something='';
    //$rule->getRuleTypeLabels()
    echo "<br><br><br><br>";
    foreach (RuleType::values() as $type) {
        if ($rule->hasRuleType(RuleType::from($type))) {
            $something=1;
        }
        echo $count++.". type=".$type."<br>displayDetaild=".$intervals->displayDetails($type)."<br>getDetailFor=".$intervals->getDetailFor($type)."<br>";
        //if this is a alert
    }
    die();
    if ($something) {
        if ($rule->hasRuleType(RuleType::from('activealert')) || $rule->hasRuleType(RuleType::from('passivealert'))) {
            echo "<br /><span class='bold'>". xlt('This is a Clinical Alert')."!</span><br />";
            $more = 'also';
        }
        if ($rule->hasRuleType(RuleType::from('activealert'))) {
            $timing .= "<span class='bold'>". xlt('Active Alert')."</span>
                        <br />An active alert will fire when the chart is opened.";
        }
        if ($rule->hasRuleType(RuleType::from('passivealert'))) {
            $timing .= "<br /><span class='bold'>". xlt('Passive Alert')."</span>
                        <br />".xlt('A passive alert will ' . $more . ' appear in the CR widget').".
                            ".xlt('After X days/week it will be flagged as Past Due.');
            
        }
        if ($rule->hasRuleType(RuleType::from('patientreminder'))) {
            $timing .= "<br /><span class='bold'><?php xlt('Patient Reminder'); ?></span>
                        <br />". xlt('A message will '.$more.' be sent to the patient');
            if ($GLOBALS['medex_enable'] == '1') {
                $timing .="<br />MedEx will send an e-mail, SMS text and/or a voice message.";
            }
        }
        if ($rule->hasRuleType(RuleType::from('provideralert'))) {
            $timing .= "<br /><span class='bold'><?php xlt('Provider Alert'); ?></span>
                        <p>". xlt('A message will be sent to the provider');
            
            $timing .="</p>";
        }
    } else {
        $timing = "<br /><b>This CR is not active!</b><br />";
    }
    
?> <!-- summary -->
<div class="section text-center row">
    <button class="btn btn-primary icon_2"
            id="edit_summary"
            title="Edit this Rule."><i class="fa fa-pencil"></i>
    </button>
    <button class="btn btn-primary icon_1"
            data-toggle="modal" data-target="#help_summary"
            title="Open the Help:: Summary Modal"><i class="fa fa-question"></i>
    </button>
    <table class="table table-sm table-responsive">
        <tr>
            <td class="text-right">
                <span class="underline"><?php echo xlt('Name'); ?>:</span>
            </td>
            
            <td><?php echo xlt($rule->title); ?></td>
        </tr>
        <tr>
            <td class="text-right">
                <span class="underline"><?php echo xlt('Alert Type'); ?>:</span></td>
            
            <td><?php echo implode_funcs(", ", $rule->getRuleTypeLabels(), array('xlt')); ?><br />
                <?php echo $timing; ?>
            </td>
        </tr>
        <tr>
            <td class="text-right">
                <span class="underline"><?php echo xlt('Developer'); ?>:</span></td>
            <td><?php echo text($rule->developer); ?></td>
        </tr>
        <tr>
            <td class="text-right">
                <span class="underline"><?php echo xlt('Funding Source'); ?>:</span></td>
            <td><?php echo text($rule->funding_source)?:"None"; ?></td>
        </tr>
        <tr>
            <td class="text-right">
                <span class="underline"><?php echo xlt('Release'); ?>:</span></td>
            <td><?php echo text($rule->release); ?></td>
        </tr>
        <tr>
            <td class="text-right">
                <span class="underline"><?php echo xlt('Web Reference'); ?>:</span>
            </td>
            <td><?php echo text($rule->web_ref); ?></td>
        </tr>
    </table>
</div>