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
foreach (RuleType::values() as $type) {
    if ($rule->hasRuleType(RuleType::from($type))) {
        $something=1;
    }
    //if this is a alert
}
?>

        <div id="show_summary_1" class="col-12">
            <table class="table table-sm table-condensed text-left">
                <tr>
                    <td class="text-right">
                        <span class="underline"><?php echo xlt('Name'); ?>:</span>
                    </td>

                    <td colspan="3" class="table-100"><?php echo xlt($rule->title); ?></td>
                </tr>
                <tr>
                    <td class="text-right">
                        <?php
                            $intervals = $rule->reminderIntervals;
                            $provider = $intervals->getDetailFor('provider');
                            foreach (ReminderIntervalType::values() as $type) {
                                foreach (ReminderIntervalRange::values() as $range) {
                                    $first = true;
                                    $detail = $intervals->getDetailFor($type, $range);
                                    $detail->timeUnit;
                                    $timings[$type->code][$range->code]['timeUnit'] =  $detail->timeUnit->code;
                                    $timings[$type->code][$range->code]['amount'] = $detail->amount;
                                    if ($timings[$type->code][$range->code]['amount'] >'1') {
                                        $timings[$type->code][$range->code]['timeUnit2'] =$timings[$type->code][$range->code]['timeUnit']."s";
                                    } else {
                                        $timings[$type->code][$range->code]['timeUnit2']= $timings[$type->code][$range->code]['timeUnit'];
                                    }
                                }
                            }
                            
                            $more='';
                            $something=0;
                            
                            foreach (RuleType::values() as $type) {
                                if ($rule->hasRuleType(RuleType::from($type))) {
                                    $something++;
                                }
                            }
                        ?>
                        <span class="underline"><?php
                                if ($something > '1') {
                                    echo xlt('Alert Types');
                                } else {
                                    echo xlt('Alert Type');
                                }
                            ?>:</span></td>
                    <td colspan="3">
                        <?php
                            
                            //of course move this somewhere central??
                            function xlts($data)
                            {
                                if ($GLOBALS['language_default'] !='English (Standard)') {
                                    $data = preg_replace('/<[^>]*>/', '', $data);
                                    return xlt($data);
                                }
                                return $data;
                            }
                            function xlas($data)
                            {
                                if ($GLOBALS['language_default'] !='English (Standard)') {
                                    $data = preg_replace('/<[^>]*>/', '', $data);
                                    return xla($data);
                                }
                                return $data;
                            }
                            if ($something) {
                                if ($rule->hasRuleType(RuleType::from('activealert')) || $rule->hasRuleType(RuleType::from('passivealert'))) {
                                    $clinical = '1';
                                }
                                if ($rule->hasRuleType(RuleType::from('activealert')) && $rule->hasRuleType(RuleType::from('passivealert'))) {
                                    $timing .= "This CR has both an
                                                            <span class='bold'
                                                                  data-toggle='popover'
                                                                  data-trigger='hover'
                                                                  data-placement='auto'
                                                                  title='Active Alerts'
                                                                  data-content='A Pop-up will occur daily when the demographics page is opened listing any Treatment Goals needing attention.'>Active Alert
                                                             </span>
                                                             and a
                                                            <span class='bold'
                                                                  data-toggle='popover'
                                                                  data-toggle='popover'
                                                                  data-trigger='hover'
                                                                  data-placement='auto'
                                                                  title='Passive Alerts'
                                                                  data-content='These alerts appear on the Dashboard page inside the CR widget'>Passive Alert</span>.
                                                            <br /> ".text($timings['clinical']['pre']['amount']). " " .text($timings['clinical']['pre']['timeUnit2']). "
                                                            before its Due date, this CR is marked <span class='due_soon bolder'>Due Soon</span>. <br />";
                                    $timing .= "Then for ".text($timings['clinical']['post']['amount'])." ".$timings['clinical']['post']['timeUnit2']." it is <span class='due_now'>Due</span>. ";
                                    $timing .= "After this, it is marked as <span class='past_due'>Past due</span>.";
                                    $timing = xlts($timing);
                                    $timing = "<div>".$timing."</div><br />";
                                } elseif ($rule->hasRuleType(RuleType::from('activealert'))) {
                                    $timing = "<div>".xlts("An <span class='bold'>Active Alert</span> will pop-up daily listing any Treatment Goals needing attention.")."</div>";
                                } elseif ($rule->hasRuleType(RuleType::from('passivealert'))) {
                                    $timing = "A <span class='bold'>Passive Alert</span> will appear in the
                                                <a href='#' data-toggle='popover'
                                                            data-trigger='hover'
                                                            data-placement='auto'
                                                            title='Clinical Reminders Widget(CR)'
                                                            data-content='The CR Widget is located on the demographics page.'>CR Widget</a> ";
                                    $timing .= text($timings['clinical']['pre']['amount'])." ".$timings['clinical']['pre']['timeUnit2']." before its Due date, this CR is marked <span class='due_soon bolder'>Due Soon</span>.";
                                    $timing .= text($timings['clinical']['post']['amount'])." ".$timings['clinical']['post']['timeUnit2']." after the Due Date, it is marked <span class='past_due'>Past Due</span>. <br />";
                                    $timing .= "Alerts stop when their Treatment Goals are completed.";
                                    $timing = xlts($timing);
                                    $timing = "<div>".$timing."</div><br />";
                                }
                                if ($rule->hasRuleType(RuleType::from('patientreminder'))) {
                                    $timing_pt = "This CR ";
                                    if ($clinical=='1') {
                                        $timing_pt .= "also ";
                                    }
                                    $timing_pt .= "triggers a <span class='bold'>Patient Reminder</span>.";
                                    $timing_pt = xlts($timing_pt);
                                    $timing_pt = "<div>".$timing_pt."<br /></div><div class='indent10'>";
                                    
                                    $timing_pt .= xlts("A message will be sent to the patient.");
                                    if ($GLOBALS['medex_enable'] == '1') {
                                        $timing_pt .= " ".xlts("<br /><a href='https://medexbank.com/'>MedEx</a> will send an e-mail, SMS text and/or a voice message as requested.");
                                    }
                                    $timing_pt .= "</div>";
                                    
                                }
                                if ( ($GLOBALS['medex_enable'] == '1') && ($rule->hasRuleType(RuleType::from('provideralert'))) ) {
                                    $timing_prov = "<div>".xlts("<span class='bolder red'>This CR has a Provider Alert!</span>")."</div>";
                                    $timing_prov .= "<div class='indent10'>".xlts("<span class='bold'>Provider Alert</span>: A message will be sent to the provider.");
                                    $timing_prov .="<br />".xlts("<a href='https://medexbank.com/'>MedEx</a> will send an e-mail, SMS text and/or a voice message as requested.");
                                    $timing_prov .= "</div>";
                                }
                            } else {
                                $timing = "<span class='bold'>".xlt('None. Edit this CR to create an Alert!')."</span><br />";
                            }
                            
                            echo $timing;
                            echo $timing_pt;
                            echo $timing_prov;
                        ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="text-right">
                        <span class="underline"><?php echo xlt('Developer'); ?>:</span></td>
                    <td><?php echo text($rule->developer); ?></td>
                    <td class="text-right">
                        <span class="underline"><?php echo xlt('Funding Source'); ?>:</span></td>
                    <td><?php echo text($rule->funding_source)?:xlt("None"); ?></td>
                </tr>
                <tr>
                    <td class="text-right">
                        <span class="underline"><?php echo xlt('Release'); ?>:</span></td>
                    <td><?php echo text($rule->release); ?></td>
                    <td class="text-right underline">
                                <span data-toggle='popover'
                                      title='Reference'
                                      data-html="true"
                                      data-trigger='hover'
                                      data-placement='auto'
                                      data-content='<?php echo xla('When present, References appear in the Dashboard CR widget as'); ?> <i class="fa fa-link text-primary"></i>.
                                      <hr>
                                      <img width="250px" class="table-bordered" src="<?php echo $GLOBALS['webroot'];?>/interface/super/rules/www/CR_widget.png">
                                      <hr><?php echo xla('This clickable link leads to the url specified here. It is suggested to link out to relevant clinical information, perhaps a government publication explaining why this CR exists. However, you can link to anything desired.');?>
                                      <?php
                                          if ($rule->web_ref) {
                                              echo xla('Currently this reference links to').' '. attr($rule->web_ref);
                                          } else {
                                              echo xla('Currently this reference does not link to anything.');
                                          } ?>
                                      '>
                                    <i class="fa fa-link"></i> <?php echo xlt('Reference'); ?>:</span>
                        </span>
                    </td>
                    <td><a href="<?php echo attr($rule->web_ref); ?>"><?php
                                if ($rule->web_ref) {
                                    $in = attr($rule->web_ref);
                                    echo mb_strlen($in) > 30 ? mb_substr($in, 0, 25) . "..." : $in;
                                } else {
                                    echo xlt("None");
                                }
                            ?></a></td>
                </tr>
                <tr>
                    <td class="text-right">
                                <span data-toggle='popover'
                                      title='<?php echo xla('Public Description'); ?>'
                                      data-html="true"
                                      data-trigger='hover'
                                      data-placement='auto'
                                      data-content='<?php echo xla('The text here will be displayed in the CR widget via a tooltip. Use it to describe to your staff what this CR means.'); ?>
                                            <hr>
                                            <img width="250px" src="<?php echo $GLOBALs['webroot'];?>/interface/super/rules/www/CR_tooltip.png">
                                            <hr>
                                        <?php echo xla('In the CR widget, each Treatment Goal in this CR carries this description as a tooltip. It is also a clickable link. This link leads to either a pop-up (add a note and/or mark the task completed), or to an external link. This link is set separately from the Reference link. Each Treatment Goal can have a unique link that is defined in the last step of this process (see PROMPTING YOU TO DO THIS below).'); ?> '>
                                    <span class="underline"><?php echo xlt('Description'); ?></span>:
                                </span>
                    </td>
                    <td colspan="3">
                        <?php echo attr($rule->public_description); ?>
                    </td>
                </tr>
            </table>
        </div>
<?php
    die();
    //not sure this file is ever called....
if ($something) {
    if ($rule->hasRuleType(RuleType::from('activealert')) || $rule->hasRuleType(RuleType::from('passivealert'))) {
        echo "<br /><span class='bold'>". xlt('This is a Clinical Alert')."!</span><br />";
        $more = 'also';
    }
    if ($rule->hasRuleType(RuleType::from('activealert'))) {
        $timing .= "<span class='bold'>". xlt('Active Alert')."</span>
                        <br />". xlt('An active alert will fire when the chart is opened.');
    }
    if ($rule->hasRuleType(RuleType::from('passivealert'))) {
        $timing .= "<br /><span class='bold'>". xlt('Passive Alert')."</span>
                        <br />";
        if (empty($more)) {
            $timing .= xlt('A passive alert will appear in the CR widget');
        } else{
            $timing .= xlt('A passive alert will also appear in the CR widget');
        }
        $timing .= xlt('After X days/week it will be flagged as Past Due');
    }
    if ($rule->hasRuleType(RuleType::from('patientreminder'))) {
        $timing .= "<br /><span class='bold'><?php xlt('Patient Reminder'); ?></span>
                        <br />";
        if (empty($more)) {
            $timing .= xlt('A message will be sent to the patient');
        } else{
            $timing .= xlt('A message will also be sent to the patient');
        }
        if ($GLOBALS['medex_enable'] == '1') {
            $timing .="<br />".xlt('MedEx will send an e-mail, SMS text and/or a voice message.');
        }
    }
    if ($rule->hasRuleType(RuleType::from('provideralert'))) {
        $timing .= "<br /><span class='bold'><?php xlt('Provider Alert'); ?></span>
                        <p>". xlt('A message will be sent to a provider');
            
        $timing .="</p>";
    }
} else {
    $timing = "<br /><span class='bold'>".xlt('This CR is not active!')."</span><br />";
}


?> <!-- summary -->

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
            <?php echo $timing;
            
            ?>
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
                <span class="underline"><?php echo xlt('Reference'); ?>:</span>
            </td>
            <td><?php echo text($rule->web_ref); ?></td>
        </tr>
        <tr>
            <td class="text-right">
                <span class="underline"><?php echo xlt('Description'); ?>:</span>
            </td>
            <td>
                <?php echo text($rule->public_description); ?>
            </td>
        </tr>
    </table>
