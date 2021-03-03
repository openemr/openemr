<?php

/**
 * gad-7.inc - common includes and constants for the gad-7 form
 * version 1.0.0  July 2020
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    ruth moulton
 *
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

 require_once("../../globals.php");
 require_once("$srcdir/api.inc");

// menu strings
$str_default = 'Please select an answer';
$str_not = 'Not at all';
$str_several = 'Several days';
$str_more = 'More than half the days';
$str_nearly = 'Nearly every day';
$str_somewhat = 'Somewhat difficult';
$str_very = 'Very difficult';
$str_extremely = 'Extremely difficult';

$str_nosave_exit = "Close form without saving answers";
$str_nosave_confirm = "Are you sure you'd like to quit without saving your answers?";

$str_form_name = "General Anxiety Disorder 7 (GAD-7)";

$str_q8 = xlt('How difficult have these problems made it to do work, take care of things at home, or get along with other people? ');
$str_q8_2 = xlt('(This question is optional and not included in the final score)');
