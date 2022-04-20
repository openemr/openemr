<?php

/**
 * gad-7.inc - common includes and constants for the gad-7 form
 * version 1.0.0  July 2020
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ruth Moulton <moulton ruth@muswell.me.uk>
 * @copyright Copyright (c) 2021 ruth moulton <ruth@muswell.me.uk>
 *
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

 require_once("../../globals.php");
 require_once("$srcdir/api.inc");
 require_once("$srcdir/patient.inc");


// menu strings
$str_default = xl('Please select an answer');
$str_not = xl('Not at all');
$str_several = xl('Several days');
$str_more = xl('More than half the days');
$str_nearly = xl('Nearly every day');
$str_somewhat = xl('Somewhat difficult');
$str_very = xl('Very difficult');
$str_extremely = xl('Extremely difficult');

$str_nosave_exit = xl("Close without saving");
$str_nosave_confirm = xl("Are you sure you'd like to quit without saving your answers?");
$str_generate_pdf = xl("Generate PDF");

$str_form_name = xl("General Anxiety Disorder 7 (GAD-7)");
$str_form_title = xl("GAD-7");
// question 8 strings
$str_q8 = xl('How difficult have these problems made it to do work, take care of things at home, or get along with other people?');
$str_q8_2 = '(' . xl('This question is optional and not included in the final score') . ')  ';
// strings describing the issues
$str_nervous = xl('Feeling nervous, anxious, or on edge');
$str_control_worry = xl('Not being able to stop or control worrying');
$str_worry = xl('Worrying too much about different things');
$str_relax = xl('Trouble relaxing');
$str_restless = xl("Being so restless that it's hard to sit still");
$str_annoyed = xl('Becoming easily annoyed or irritable');
$str_afraid = xl('Feeling afraid as if something awful might happen');
$str_total = xl('Total GAD-7 score');
//
// meaning of score values
$str_values = [0 => xl('Not at all') . ' (0)',1 => xl('Several days') . ' (1)',2 => xl('More than half of days') . ' (2)',3 => xl('Nearly every day') . ' (3)'];
