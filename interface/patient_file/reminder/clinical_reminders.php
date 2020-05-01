<?php

/**
 * clinical reminders gui
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ensofttek, LLC
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2011-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 Ensofttek, LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/clinical_rules.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
?>

<html>
<head>
    <?php Header::setupHeader(['common']); ?>
</head>

<?php
$patient_id = ($_GET['patient_id']) ? $_GET['patient_id'] : "";
?>

<body class='body_top'>
<div>
  <span class='title'><?php echo xlt('Clinical Reminders'); ?></span>
</div>
<div id='namecontainer_creminders' class='namecontainer_creminders' style='float: left; margin-right: 10px'>
    <?php echo xlt('for');?>&nbsp;
  <span class="title">
    <a href="../summary/demographics.php" onclick="top.restoreSession()"><?php echo text(getPatientName($pid)); ?></a>
  </span>
</div>
<div>
  <a href="../summary/demographics.php" class="btn btn-secondary" onclick="top.restoreSession()"><?php echo xlt('Back To Patient');?></a>
</div>

<br />
<br />
<br />

<?php
  // collect the pertinent plans and rules
  $plans_default = resolve_plans_sql('', '0', true);
  $rules_default = resolve_rules_sql('', '0', true, '', $_SESSION['authUser']);
?>

<ul class="tabNav">
  <li class='current'><a href='#' onclick='top.restoreSession()'><?php echo xlt('Main'); ?></a></li>
  <li><a href='#' onclick='top.restoreSession()'><?php echo xlt('Plans'); ?></a></li>
  <li><a href='#' onclick='top.restoreSession()'><?php echo xlt('Admin'); ?></a></li>
</ul>

<div class="tabContainer">
  <div class="tab current text h-auto" style="width: 97%;">
    <?php
      clinical_summary_widget($pid, "reminders-all", '', 'default', $_SESSION['authUser']);
    ?>
  </div>

  <div class="tab text h-auto" style="width: 97%;">
    <?php
      clinical_summary_widget($pid, "reminders-all", '', "plans", $_SESSION['authUser']);
    ?>
  </div>

  <div class="tab h-auto" style="width: 97%;">
    <div id='report_results'>
    <div class="table-responsive">
      <table class="table table-bordered table-active table-hover">
        <tr>
          <th rowspan="2"><?php echo xlt('Plan'); ?></th>
          <th colspan="2"><?php echo xlt('Show'); ?></th>
        </tr>
        <tr>
          <th><?php echo xlt('Patient Setting'); ?></th>
          <th style="left-margin: 1em;"><?php echo xlt('Practice Default Setting'); ?></th>
        </tr>
        <?php foreach ($plans_default as $plan) { ?>
            <?php
          //only show the plan if there are any rules in it that the user has access to
            $plan_check = resolve_rules_sql('', '0', true, $plan['id'], $_SESSION['authUser']);
            if (empty($plan_check)) {
                continue;
            }
            ?>
          <tr>
            <td><?php echo generate_display_field(array('data_type' => '1','list_id' => 'clinical_plans'), $plan['id']); ?></td>
            <td align="center">
                <?php

                $patient_plan = collect_plan($plan['id'], $patient_id);

              // Set the patient specific setting for gui
                if (empty($patient_plan)) {
                    $select = "default";
                } else {
                    if ($patient_plan['normal_flag'] == "1") {
                        $select = "on";
                    } elseif ($patient_plan['normal_flag'] == "0") {
                        $select = "off";
                    } else { // $patient_rule['normal_flag'] == NULL
                        $select = "default";
                    }
                } ?>
            <div class="form-group">
              <select class="plan_show form-control col-4" name="<?php echo attr($plan['id']); ?>">
                <option value="default" <?php echo ($select == "default") ? "selected" : ""; ?>><?php echo xlt('Default'); ?></option>
                <option value="on" <?php echo ($select == "on") ? "selected" : ""; ?>><?php echo xlt('On'); ?></option>
                <option value="off" <?php echo ($select == "off") ? "selected" : ""; ?>><?php echo xlt('Off'); ?></option>
              </select>
            </div>
            </td>
            <td align="center">
                <?php
                if ($plan['normal_flag'] == "1") {
                    echo xlt('On');
                } else {
                    echo xlt('Off');
                }
                ?>
            </td>
          </tr>
        <?php } ?>
      </table>
      </div>
      <br />
      <br />
    <div class="table-responsive">
      <table class="table table-bordered table-active table-hover">
        <tr>
          <th rowspan="2"><?php echo xlt('Rule'); ?></th>
          <th colspan="2"><?php echo xlt('Passive Alert'); ?></th>
          <th colspan="2"><?php echo xlt('Active Alert'); ?></th>
        </tr>
        <tr>
          <th><?php echo xlt('Patient Setting'); ?></th>
          <th style="left-margin: 1em;"><?php echo xlt('Practice Default Setting'); ?></th>
          <th><?php echo xlt('Patient Setting'); ?></th>
          <th style="left-margin: 1em;"><?php echo xlt('Practice Default Setting'); ?></th>
        </tr>
        <?php foreach ($rules_default as $rule) { ?>
          <tr>
            <td><?php echo generate_display_field(array('data_type' => '1','list_id' => 'clinical_rules'), $rule['id']); ?></td>
            <td align="center">
                <?php
                $patient_rule = collect_rule($rule['id'], $patient_id);
              // Set the patient specific setting for gui
                if (empty($patient_rule)) {
                    $select = "default";
                } else {
                    if ($patient_rule['passive_alert_flag'] == "1") {
                        $select = "on";
                    } elseif ($patient_rule['passive_alert_flag'] == "0") {
                        $select = "off";
                    } else { // $patient_rule['passive_alert_flag'] == NULL
                        $select = "default";
                    }
                } ?>
                <div class="form-group">
              <select class="passive_alert form-control" name="<?php echo attr($rule['id']); ?>">
                <option value="default" <?php echo ($select == "default") ? "selected" : ""; ?>><?php echo xlt('Default'); ?></option>
                <option value="on" <?php echo ($select == "on") ? "selected" : ""; ?>><?php echo xlt('On'); ?></option>
                <option value="off" <?php echo ($select == "off") ? "selected" : ""; ?>><?php echo xlt('Off'); ?></option>
              </select>
                </div>
            </td>
            <td align="center">
                <?php
                if ($rule['passive_alert_flag'] == "1") {
                    echo xlt('On');
                } else {
                    echo xlt('Off');
                }
                ?>
            </td>
            <td align="center">
                <?php
              // Set the patient specific setting for gui
                if (empty($patient_rule)) {
                    $select = "default";
                } else {
                    if ($patient_rule['active_alert_flag'] == "1") {
                        $select = "on";
                    } elseif ($patient_rule['active_alert_flag'] == "0") {
                        $select = "off";
                    } else { // $patient_rule['active_alert_flag'] == NULL
                        $select = "default";
                    }
                } ?>
                 <div class="form-group">
              <select class="active_alert form-control" name="<?php echo attr($rule['id']); ?>">
                <option value="default" <?php echo ($select == "default") ? "selected" : ""; ?>><?php echo xlt('Default'); ?></option>
                <option value="on" <?php echo ($select == "on") ? "selected" : ""; ?>><?php echo xlt('On'); ?></option>
                <option value="off" <?php echo ($select == "off") ? "selected" : ""; ?>><?php echo xlt('Off'); ?></option>
              </select>
              </div>
            </td>
            <td align="center">
                <?php
                if ($rule['active_alert_flag'] == "1") {
                    echo xlt('On');
                } else {
                    echo xlt('Off');
                }
                ?>
            </td>
          </tr>
        <?php } ?>
      </table>
      </div>
    </div>
  </div>
</div>

<script>
  $(function () {

    tabbify();

    $(".passive_alert").change(function() {
      top.restoreSession();
      $.post( "../../../library/ajax/rule_setting.php", {
        rule: this.name,
        type: 'passive_alert',
        setting: this.value,
        patient_id: <?php echo js_escape($patient_id); ?>,
        csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
      });
    });

    $(".active_alert").change(function() {
      top.restoreSession();
      $.post( "../../../library/ajax/rule_setting.php", {
        rule: this.name,
        type: 'active_alert',
        setting: this.value,
        patient_id: <?php echo js_escape($patient_id); ?>,
        csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
      });
    });

    $(".plan_show").change(function() {
      top.restoreSession();
      $.post( "../../../library/ajax/plan_setting.php", {
        plan: this.name,
        type: 'normal',
        setting: this.value,
        patient_id: <?php echo js_escape($patient_id); ?>,
        csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
      });
    });

  });

  function refreshme() {
      top.restoreSession();
      location.reload();
  }

  $(".medium_modal").on('click', function(e) {
      e.preventDefault();e.stopPropagation();
      dlgopen('', '', 800, 200, '', '', {
          buttons: [
              {text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}
          ],
          onClosed: 'refreshme',
          allowResize: true,
          allowDrag: true,
          dialogId: 'reminders',
          type: 'iframe',
          url: $(this).attr('href')
      });
  });
</script>

</body>
</html>

