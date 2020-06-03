<?php

/**
 * Functions to support questions at order entry that are specific to order type.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
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
function generate_qoe_html($ptid = 0, $orderid = 0, $dbseq = 0, $formseq = 0)
{
    global $rootdir, $qoe_init_javascript;

    $s = "";
    $qoe_init_javascript = '';
    $prefix = 'ans' . $formseq . '_';

    if (empty($ptid)) {
        return $s;
    }
    // container is div in form.
    $s .= "<table class='table table-sm bg-light qoe-table'>";

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
        $qfieldid = $prefix . trim($qrow['question_code']);
        $fldtype = $qrow['fldtype'];
        $maxsize = 0 + $qrow['maxsize'];
        $qrow['tips'] = str_ireplace("^", " ", $qrow['tips']); // in case of HL7

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
        $s .= "<td valign='top'";
        if ($qrow['required']) {
            $s .= " style='color: #880000'"; // TBD: move to stylesheet
        }

        $s .= ">" . text($qrow['question_text']) . "</td>";
        $s .= "<td valign='top'>";

        if ($fldtype == 'T') {
            // Text Field.
            $s .= "<input class='input-sm' type='text' name='" . attr($qfieldid) . "'";
            $s .= " maxlength='" . ($maxsize ? attr($maxsize) : 255) . "'";
            if (!empty($answers)) {
                $s .= " value='" . attr($answers[0]) . "'";
            }

            $s .= " title='" . attr($qrow['tips']) . "' placeholder='" . attr($qrow['tips']) . "' />";
        } elseif ($fldtype == 'N') {
            // Numeric text Field.
            // TBD: Add some JavaScript validation for this.
            $s .= "<input class='input-sm' type='text' name='" . attr($qfieldid) . "' maxlength='8'";
            if (!empty($answers)) {
                $s .= " value='" . attr($answers[0]) . "'";
            }

            $s .= " title='" . attr($qrow['tips']) . "' placeholder='" . attr($qrow['tips']) . "' />";
        } elseif ($fldtype == 'D') {
            // Date Field.
            $s .= "<input type='text' name='" . attr($qfieldid) . "' id='" . attr($qfieldid) . "'";
            if (!empty($answers)) {
                $s .= " value='" . attr($answers[0]) . "'";
            }

            $s .= " class='datepicker input-sm' title='" . xla('Click here to choose a date') . "' />";
            /* Legacy calendar removed to update to current calendar 07/20/2018 sjp */
        } elseif ($fldtype == 'G') {
            // Gestational age in weeks and days.
            $currweeks = -1;
            $currdays  = -1;
            if (!empty($answers)) {
                $currweeks = intval($answers[0] / 7);
                $currdays  = $answers[0] % 7;
            }

            $s .= "<select class='input-sm' name='G1_" . attr($qfieldid) . "'>";
            $s .= "<option value=''></option>";
            for ($i = 5; $i <= 21; ++$i) {
                $s .= "<option value='" .  attr($i) . "'";
                if ($i == $currweeks) {
                    $s .= " selected";
                }

                $s .= ">" . text($i) . "</option>";
            }

            $s .= "</select>";
            $s .= " " . xlt('weeks') . " &nbsp;";
            $s .= "<select class='input-sm' name='G2_" . attr($qfieldid) . "'>";
            $s .= "<option value=''></option>";
            for ($i = 0; $i <= 6; ++$i) {
                $s .= "<option value='" . attr($i) . "'";
                if ($i == $currdays) {
                    $s .= " selected";
                }

                $s .= ">" . text($i) . "</option>";
            }

            $s .= "</select>";
            $s .= " " . xlt('days');

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
        } elseif ($fldtype == 'M') {
            // List of checkboxes.
            $a = explode(';', $qrow['options']);
            $i = 0;
            foreach ($a as $aval) {
                list($desc, $code) = explode(':', $aval);
                if (empty($code)) {
                    $code = $desc;
                }

                if ($i) {
                    $s .= "<br />";
                }

                $s .= "<label class='radio-inline'><input class='input-sm' type='checkbox' name='" . attr($qfieldid[$i]) . "' value='" . attr($code) . "'";
                if (in_array($code, $answers)) {
                    $s .= " checked";
                }

                $s .= " />" . text($desc) . "</label>";
                ++$i;
            }
        } else {
            // Radio buttons or drop-list, depending on the number of choices.
            $a = explode(';', $qrow['options']);
            if (count($a) > 5) {
                $s .= "<select class='input-sm' name='" . attr($qfieldid) . "'";
                $s .= ">";
                foreach ($a as $aval) {
                    list($desc, $code) = explode(':', $aval);
                    if (empty($code)) {
                        $code = $desc;
                    }

                    $s .= "<option value='" . attr($code) . "'";
                    if (in_array($code, $answers)) {
                        $s .= " selected";
                    }

                    $s .= ">" . text($desc) . "</option>";
                }

                $s .= "</select>";
            } else {
                $i = 0;
                foreach ($a as $aval) {
                    list($desc, $code) = explode(':', $aval);
                    if (empty($code)) {
                        $code = $desc;
                        if (empty($code)) {
                            $desc = "No Answer";
                        }
                    }

                    if ($i) {
                        $s .= "<br />";
                    }

                    $s .= "<label class='radio-inline'><input type='radio' name='" . attr($qfieldid) . "' value='" . attr($code) . "'";
                    if (in_array($code, $answers)) {
                        $s .= " checked";
                    }

                    $s .= " />" . text($desc) . "</label>";
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
