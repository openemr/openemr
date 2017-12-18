<?php
/**
 * clinical reminders gui
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Ensofttek, LLC
 * @copyright Copyright (c) 2011-2017 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 Ensofttek, LLC
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/clinical_rules.php");

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
  <span class='title'><?php echo htmlspecialchars(xl('Clinical Reminders'), ENT_NOQUOTES); ?></span>
</div>
<div id='namecontainer_creminders' class='namecontainer_creminders' style='float:left;margin-right:10px'>
    <?php echo htmlspecialchars(xl('for'), ENT_NOQUOTES);?>&nbsp;
  <span class="title">
    <a href="../summary/demographics.php" onclick="top.restoreSession()"><?php echo htmlspecialchars(getPatientName($pid), ENT_NOQUOTES); ?></a>
  </span>
</div>
<div>
  <a href="../summary/demographics.php" class="css_button" onclick="top.restoreSession()">
    <span><?php echo htmlspecialchars(xl('Back To Patient'), ENT_NOQUOTES);?></span>
  </a>
</div>

<br>
<br>
<br>

<?php
  // collect the pertinent plans and rules
  $plans_default = resolve_plans_sql('', '0', true);
  $rules_default = resolve_rules_sql('', '0', true, '', $_SESSION['authUser']);
?>

<ul class="tabNav">
  <li class='current'><a href='#' onclick='top.restoreSession()'><?php echo htmlspecialchars(xl('Main'), ENT_NOQUOTES); ?></a></li>
  <li><a href='#' onclick='top.restoreSession()'><?php echo htmlspecialchars(xl('Plans'), ENT_NOQUOTES); ?></a></li>
  <li><a href='#' onclick='top.restoreSession()'><?php echo htmlspecialchars(xl('Admin'), ENT_NOQUOTES); ?></a></li>
</ul>

<div class="tabContainer">
  <div class="tab current text" style="height:auto;width:97%;">
    <?php
      clinical_summary_widget($pid, "reminders-all", '', 'default', $_SESSION['authUser']);
    ?>
  </div>

  <div class="tab text" style="height:auto;width:97%;">
    <?php
      clinical_summary_widget($pid, "reminders-all", '', "plans", $_SESSION['authUser']);
    ?>
  </div>

  <div class="tab" style="height:auto;width:97%;">
    <div id='report_results'>
      <table>
        <tr>
          <th rowspan="2"><?php echo htmlspecialchars(xl('Plan'), ENT_NOQUOTES); ?></th>
          <th colspan="2"><?php echo htmlspecialchars(xl('Show'), ENT_NOQUOTES); ?></th>
        </tr>
        <tr>
          <th><?php echo htmlspecialchars(xl('Patient Setting'), ENT_NOQUOTES); ?></th>
          <th style="left-margin:1em;"><?php echo htmlspecialchars(xl('Practice Default Setting'), ENT_NOQUOTES); ?></th>
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
            <td style="border-right:1px solid black;"><?php echo generate_display_field(array('data_type'=>'1','list_id'=>'clinical_plans'), $plan['id']); ?></td>
            <td align="center">
                <?php

                $patient_plan = collect_plan($plan['id'], $patient_id);

              // Set the patient specific setting for gui
                if (empty($patient_plan)) {
                    $select = "default";
                } else {
                    if ($patient_plan['normal_flag'] == "1") {
                        $select = "on";
                    } else if ($patient_plan['normal_flag'] == "0") {
                        $select = "off";
                    } else { // $patient_rule['normal_flag'] == NULL
                        $select = "default";
                    }
                } ?>
              <select class="plan_show" name="<?php echo htmlspecialchars($plan['id'], ENT_NOQUOTES); ?>">
                <option value="default" <?php if ($select == "default") {
                    echo "selected";
} ?>><?php echo htmlspecialchars(xl('Default'), ENT_NOQUOTES); ?></option>
                <option value="on" <?php if ($select == "on") {
                    echo "selected";
} ?>><?php echo htmlspecialchars(xl('On'), ENT_NOQUOTES); ?></option>
                <option value="off" <?php if ($select == "off") {
                    echo "selected";
} ?>><?php echo htmlspecialchars(xl('Off'), ENT_NOQUOTES); ?></option>
              </select>
            </td>
            <td align="center" style="border-right:1px solid black;">
                <?php if ($plan['normal_flag'] == "1") {
                    echo htmlspecialchars(xl('On'), ENT_NOQUOTES);
} else {
    echo htmlspecialchars(xl('Off'), ENT_NOQUOTES);
} ?>
            </td>
          </tr>
        <?php } ?>
      </table>
      <br>
      <br>
      <table>
        <tr>
          <th rowspan="2"><?php echo htmlspecialchars(xl('Rule'), ENT_NOQUOTES); ?></th>
          <th colspan="2"><?php echo htmlspecialchars(xl('Passive Alert'), ENT_NOQUOTES); ?></th>
          <th colspan="2"><?php echo htmlspecialchars(xl('Active Alert'), ENT_NOQUOTES); ?></th>
        </tr>
        <tr>
          <th><?php echo htmlspecialchars(xl('Patient Setting'), ENT_NOQUOTES); ?></th>
          <th style="left-margin:1em;"><?php echo htmlspecialchars(xl('Practice Default Setting'), ENT_NOQUOTES); ?></th>
          <th><?php echo htmlspecialchars(xl('Patient Setting'), ENT_NOQUOTES); ?></th>
          <th style="left-margin:1em;"><?php echo htmlspecialchars(xl('Practice Default Setting'), ENT_NOQUOTES); ?></th>
        </tr>
        <?php foreach ($rules_default as $rule) { ?>
          <tr>
            <td style="border-right:1px solid black;"><?php echo generate_display_field(array('data_type'=>'1','list_id'=>'clinical_rules'), $rule['id']); ?></td>
            <td align="center">
                <?php
                $patient_rule = collect_rule($rule['id'], $patient_id);
              // Set the patient specific setting for gui
                if (empty($patient_rule)) {
                    $select = "default";
                } else {
                    if ($patient_rule['passive_alert_flag'] == "1") {
                        $select = "on";
                    } else if ($patient_rule['passive_alert_flag'] == "0") {
                        $select = "off";
                    } else { // $patient_rule['passive_alert_flag'] == NULL
                        $select = "default";
                    }
                } ?>
              <select class="passive_alert" name="<?php echo htmlspecialchars($rule['id'], ENT_NOQUOTES); ?>">
                <option value="default" <?php if ($select == "default") {
                    echo "selected";
} ?>><?php echo htmlspecialchars(xl('Default'), ENT_NOQUOTES); ?></option>
                <option value="on" <?php if ($select == "on") {
                    echo "selected";
} ?>><?php echo htmlspecialchars(xl('On'), ENT_NOQUOTES); ?></option>
                <option value="off" <?php if ($select == "off") {
                    echo "selected";
} ?>><?php echo htmlspecialchars(xl('Off'), ENT_NOQUOTES); ?></option>
              </select>
            </td>
            <td align="center" style="border-right:1px solid black;">
                <?php if ($rule['passive_alert_flag'] == "1") {
                    echo htmlspecialchars(xl('On'), ENT_NOQUOTES);
} else {
    echo htmlspecialchars(xl('Off'), ENT_NOQUOTES);
} ?>
            </td>
            <td align="center">
                <?php
              // Set the patient specific setting for gui
                if (empty($patient_rule)) {
                    $select = "default";
                } else {
                    if ($patient_rule['active_alert_flag'] == "1") {
                        $select = "on";
                    } else if ($patient_rule['active_alert_flag'] == "0") {
                        $select = "off";
                    } else { // $patient_rule['active_alert_flag'] == NULL
                        $select = "default";
                    }
                } ?>
              <select class="active_alert" name="<?php echo htmlspecialchars($rule['id'], ENT_NOQUOTES); ?>">
                <option value="default" <?php if ($select == "default") {
                    echo "selected";
} ?>><?php echo htmlspecialchars(xl('Default'), ENT_NOQUOTES); ?></option>
                <option value="on" <?php if ($select == "on") {
                    echo "selected";
} ?>><?php echo htmlspecialchars(xl('On'), ENT_NOQUOTES); ?></option>
                <option value="off" <?php if ($select == "off") {
                    echo "selected";
} ?>><?php echo htmlspecialchars(xl('Off'), ENT_NOQUOTES); ?></option>
              </select>
            </td>
            <td align="center">
                <?php if ($rule['active_alert_flag'] == "1") {
                    echo htmlspecialchars(xl('On'), ENT_NOQUOTES);
} else {
    echo htmlspecialchars(xl('Off'), ENT_NOQUOTES);
} ?>
            </td>
          </tr>
        <?php } ?>
      </table>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function() {

    tabbify();

    $(".passive_alert").change(function() {
      top.restoreSession();
      $.post( "../../../library/ajax/rule_setting.php", {
        rule: this.name,
        type: 'passive_alert',
        setting: this.value,
        patient_id: '<?php echo htmlspecialchars($patient_id, ENT_QUOTES); ?>'
      });
    });

    $(".active_alert").change(function() {
      top.restoreSession();
      $.post( "../../../library/ajax/rule_setting.php", {
        rule: this.name,
        type: 'active_alert',
        setting: this.value,
        patient_id: '<?php echo htmlspecialchars($patient_id, ENT_QUOTES); ?>'
      });
    });

    $(".plan_show").change(function() {
      top.restoreSession();
      $.post( "../../../library/ajax/plan_setting.php", {
        plan: this.name,
        type: 'normal',
        setting: this.value,
        patient_id: '<?php echo htmlspecialchars($patient_id, ENT_QUOTES); ?>'
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
              {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
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

