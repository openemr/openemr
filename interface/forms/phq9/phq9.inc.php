<?php

/**
 * phq-9.inc - common includes and constants for the gad-7 form
 * version 1.0.0  July 2020
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ruth Moulton <moulton ruth@muswell.me.uk>
 * @copyright Copyright (c) 2021 ruth moulton <ruth@muswell.me.uk>
 *
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// menu strings
$str_default = xl('Please select an answer');
$str_not = xl('Not at all');
$str_several = xl('Several days');
$str_more = xl('More than half the days');
$str_nearly = xl('Nearly every day');
$str_somewhat = xl('Somewhat difficult');
$str_very = xl('Very difficult');
$str_extremely = xl('Extremely difficult');

$str_nosave_exit = xl("Close form without saving answers");
$str_nosave_confirm = xl("Are you sure you'd like to quit without saving your answers?");

$str_form_name = xl("Patient Health Questionnaire (PHQ-9)"); /* typo correction RM */
$str_form_title = xl("PHQ-9");

$str_q10 = xl('How difficult have these problems made it to do work, take care of things at home, or get along with other people?');
$str_q10_2 = '(' . xl('This question is optional and not included in the final score') . ')  ';
