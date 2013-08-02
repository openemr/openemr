<?php
/**
* Functions to support questions at order entry that are specific to order type.
*
* Copyright (C) 2012 Rod Roark <rod@sunsetsystems.com>
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
*
* @package   OpenEMR
* @author    Rod Roark <rod@sunsetsystems.com>
*/

/**
 * Generate HTML for the QOE form suitable for insertion into a <div>.
 * This HTML may contain single quotes but not unescaped double quotes.
 *
 * @param  integer $ptid     Value matching a procedure_type_id in the procedure_types table.
 * @param  integer $orderid  Procedure order ID, if there is an existing order.
 * @param  integer $dbseq    Procedure order item sequence number, if there is an existing procedure.
 * @param  string  $formseq  Zero-relative occurrence number in the form.
 * @return string            The generated HTML.
 */
function generate_qoe_html($ptid=0, $orderid=0, $dbseq=0, $formseq=0) {
  global $rootdir, $qoe_init_javascript;

  $s = "";
  $qoe_init_javascript = '';
  $prefix = 'ans' . $formseq . '_';

  if (empty($ptid)) return $s;

  $s .= "<table>";

  // Get all the questions for the given procedure order type.
  $qres = sqlStatement("SELECT " .
    "q.question_code, q.question_text, q.options, q.required, q.maxsize, " .
    "q.fldtype, q.tips " .
    "FROM procedure_type AS t " .
    "JOIN procedure_questions AS q ON q.lab_id = t.lab_id " .
    "AND q.procedure_code = t.procedure_code AND q.activity = 1 " .
    "WHERE t.procedure_type_id = ? " .
    "ORDER BY q.seq, q.question_text", array($ptid));

  while ($qrow = sqlFetchArray($qres)) {
    $options = trim($qrow['options']);
    $qfieldid = $prefix . attr(trim($qrow['question_code']));
    $fldtype = $qrow['fldtype'];
    $maxsize = 0 + $qrow['maxsize'];

    // Get answer value(s) to this question, if any.
    $answers = array();
    if ($orderid && $dbseq > 0) {
      $ares = sqlStatement("SELECT answer FROM procedure_answers WHERE " .
        "procedure_order_id = ? AND procedure_order_seq = ? AND question_code = ? " .
        "ORDER BY answer_seq", array($orderid, $dbseq, $qrow['question_code']));
      while ($arow = sqlFetchArray($ares)) {
        $answers[] = $arow['answer'];
      }
    }

    $s .= "<tr>";
    $s .= "<td width='1%' valign='top' nowrap";
    if ($qrow['required']) $s .= " style='color:#880000'"; // TBD: move to stylesheet
    $s .= ">" . attr($qrow['question_text']) . "</td>";
    $s .= "<td valign='top'>";

    if ($fldtype == 'T') {
      // Text Field.
      $s .= "<input type='text' name='$qfieldid' size='50'";
      $s .= " maxlength='" . ($maxsize ? $maxsize : 255) . "'";
      if (!empty($answers)) $s .= " value='" . attr($answers[0]) . "'";
      $s .= " />";
      $s .= "&nbsp;" . text($qrow['tips']);
    }

    else if ($fldtype == 'N') {
      // Numeric text Field.
      // TBD: Add some JavaScript validation for this.
      $s .= "<input type='text' name='$qfieldid' maxlength='8'";
      if (!empty($answers)) $s .= " value='" . attr($answers[0]) . "'";
      $s .= " />";
      $s .= "&nbsp;" . text($qrow['tips']);
    }

    else if ($fldtype == 'D') {
      // Date Field.
      $s .= "<input type='text' size='10' name='$qfieldid' id='$qfieldid'";
      if (!empty($answers)) $s .= " value='" . attr($answers[0]) . "'";
      $s .= " onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />";
      $s .= "<img src='$rootdir/pic/show_calendar.gif' align='absbottom' width='24' height='22'" .
        " id='img_$qfieldid' border='0' alt='[?]' style='cursor:pointer'" .
        " title='" . htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES) . "' />";
      $qoe_init_javascript .= " Calendar.setup({inputField:'$qfieldid', ifFormat:'%Y-%m-%d', button:'img_$qfieldid'});";
    }

    else if ($fldtype == 'G') {
      // Gestational age in weeks and days.
      $currweeks = -1;
      $currdays  = -1;
      if (!empty($answers)) {
        $currweeks = intval($answers[0] / 7);
        $currdays  = $answers[0] % 7;
      }
      $s .= "<select name='G1_$qfieldid'>";
      $s .= "<option value=''></option>";
      for ($i = 5; $i <= 21; ++$i) {
        $s .= "<option value='$i'";
        if ($i == $currweeks) $s .= " selected";
        $s .= ">$i</option>";
      }
      $s .= "</select>";
      $s .= " " . xlt('weeks') . " &nbsp;";
      $s .= "<select name='G2_$qfieldid'>";
      $s .= "<option value=''></option>";
      for ($i = 0; $i <= 6; ++$i) {
        $s .= "<option value='$i'";
        if ($i == $currdays) $s .= " selected";
        $s .= ">$i</option>";
      }
      $s .= "</select>";
      $s .= " " . xlt('days');
    }

    // Possible alternative code instead of radio buttons and checkboxes.
    // Might use this for cases where the list of choices is large.
    /*****************************************************************
    else {
      // Single- or multi-select list.
      $multiple = false;
      if (substr($options, 0, 2) == '+;') {
        $multiple = true;
        $options = substr($options, 2);
      }
      $s .= "<select name='$qfieldid'";
      if ($multiple) $s .= " multiple";
      $s .= ">";
      $a = explode(';', $qrow['options']);
      foreach ($a as $aval) {
        list($desc, $code) = explode(':', $aval);
        if (empty($code)) $code = $desc;
        $s .= "<option value='" . attr($code) . "'";
        if (in_array($code, $answers)) $s .= " selected";
        $s .= ">" . text($desc) . "</option>";
      }
      $s .= "</select>";
    }
    *****************************************************************/

    else if ($fldtype == 'M') {
      // List of checkboxes.
      $a = explode(';', $qrow['options']);
      $i = 0;
      foreach ($a as $aval) {
        list($desc, $code) = explode(':', $aval);
        if (empty($code)) $code = $desc;
        if ($i) $s .= "<br />";
        $s .= "<input type='checkbox' name='$qfieldid[$i]' value='" . attr($code) . "'";
        if (in_array($code, $answers)) $s .= " checked";
        $s .= " />" . text($desc);
        ++$i;
      }
    }

    else {
      // Radio buttons or drop-list, depending on the number of choices.
      $a = explode(';', $qrow['options']);
      if (count($a) > 5) {
        $s .= "<select name='$qfieldid'";
        $s .= ">";
        foreach ($a as $aval) {
          list($desc, $code) = explode(':', $aval);
          if (empty($code)) $code = $desc;
          $s .= "<option value='" . attr($code) . "'";
          if (in_array($code, $answers)) $s .= " selected";
          $s .= ">" . text($desc) . "</option>";
        }
        $s .= "</select>";
      }
      else {
        $i = 0;
        foreach ($a as $aval) {
          list($desc, $code) = explode(':', $aval);
          if (empty($code)) $code = $desc;
          if ($i) $s .= "<br />";
          $s .= "<input type='radio' name='$qfieldid' value='" . attr($code) . "'";
          if (in_array($code, $answers)) $s .= " checked";
          $s .= " />" . text($desc);
          ++$i;
        }
      }
    }

    $s .= '</td>';
    $s .= '</tr>';
  }

  $s .= '</table>';
  return $s;
}
?>
