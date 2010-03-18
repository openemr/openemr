<script type="text/javascript">
// ** I18N
// Calendar EN language
// Author: Mihai Bazon, <mihai_bazon@yahoo.com>
// Encoding: any
// Distributed under the same terms as the calendar itself.

// For translators: please use UTF-8 if possible.  We strongly believe that
// Unicode is the answer to a real internationalized world.  Also please
// include your contact information in the header, as can be seen above.

// full day names
Calendar._DN = new Array
("<?php xl("Sunday","e"); ?>",
 "<?php xl("Monday","e"); ?>",
 "<?php xl("Tuesday","e"); ?>",
 "<?php xl("Wednesday","e"); ?>",
 "<?php xl("Thursday","e"); ?>",
 "<?php xl("Friday","e"); ?>",
 "<?php xl("Saturday","e"); ?>",
 "<?php xl("Sunday","e"); ?>");

// Please note that the following array of short day names (and the same goes
// for short month names, _SMN) isn't absolutely necessary.  We give it here
// for exemplification on how one can customize the short day names, but if
// they are simply the first N letters of the full name you can simply say:
//
//   Calendar._SDN_len = N; // short day name length
//   Calendar._SMN_len = N; // short month name length
//
// If N = 3 then this is not needed either since we assume a value of 3 if not
// present, to be compatible with translation files that were written before
// this feature.

// short day names
Calendar._SDN = new Array
("<?php xl("Sun","e"); ?>",
 "<?php xl("Mon","e"); ?>",
 "<?php xl("Tue","e"); ?>",
 "<?php xl("Wed","e"); ?>",
 "<?php xl("Thu","e"); ?>",
 "<?php xl("Fri","e"); ?>",
 "<?php xl("Sat","e"); ?>",
 "<?php xl("Sun","e"); ?>");

// First day of the week. "0" means display Sunday first, "1" means display
// Monday first, etc.
Calendar._FD = 0;

// full month names
Calendar._MN = new Array
("<?php xl("January","e"); ?>",
 "<?php xl("February","e"); ?>",
 "<?php xl("March","e"); ?>",
 "<?php xl("April","e"); ?>",
 "<?php xl("May","e"); ?>",
 "<?php xl("June","e"); ?>",
 "<?php xl("July","e"); ?>",
 "<?php xl("August","e"); ?>",
 "<?php xl("September","e"); ?>",
 "<?php xl("October","e"); ?>",
 "<?php xl("November","e"); ?>",
 "<?php xl("December","e"); ?>");

// short month names
Calendar._SMN = new Array
("<?php xl("Jan","e"); ?>",
 "<?php xl("Feb","e"); ?>",
 "<?php xl("Mar","e"); ?>",
 "<?php xl("Apr","e"); ?>",
 "<?php xl("May","e"); ?>",
 "<?php xl("Jun","e"); ?>",
 "<?php xl("Jul","e"); ?>",
 "<?php xl("Aug","e"); ?>",
 "<?php xl("Sep","e"); ?>",
 "<?php xl("Oct","e"); ?>",
 "<?php xl("Nov","e"); ?>",
 "<?php xl("Dec","e"); ?>");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "<?php xl("About the calendar","e"); ?>";

Calendar._TT["ABOUT"] =
"DHTML Date/Time Selector\n" +
"(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" + // don't translate this this ;-)
"For latest version visit: http://www.dynarch.com/projects/calendar/\n" +
"Distributed under GNU LGPL.  See http://gnu.org/licenses/lgpl.html for details." +
"\n\n" +
"Date selection:\n" +
"- Use the \xab, \xbb buttons to select year\n" +
"- Use the " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " buttons to select month\n" +
"- Hold mouse button on any of the above buttons for faster selection.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"<?php xl("Time selection","e"); ?>"+":\n" +
"- "+"<?php xl("Click on any of the time parts to increase it","e"); ?>"+"\n" +
"- "+"<?php xl("or Shift-click to decrease it","e"); ?>"+"\n" +
"- "+"<?php xl("or click and drag for faster selection.","e"); ?>";

Calendar._TT["PREV_YEAR"] = "<?php xl("Prev. year (hold for menu)","e"); ?>";
Calendar._TT["PREV_MONTH"] = "<?php xl("Prev. month (hold for menu)","e"); ?>";
Calendar._TT["GO_TODAY"] = "<?php xl("Go Today","e"); ?>";
Calendar._TT["NEXT_MONTH"] = "<?php xl("Next month (hold for menu)","e"); ?>";
Calendar._TT["NEXT_YEAR"] = "<?php xl("Next year (hold for menu)","e"); ?>";
Calendar._TT["SEL_DATE"] = "<?php xl("Select date","e"); ?>";
Calendar._TT["DRAG_TO_MOVE"] = "<?php xl("Drag to move","e"); ?>";
Calendar._TT["PART_TODAY"] = " ("+"<?php xl("today","e"); ?>"+")";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "Display %s first";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "<?php xl("Close","e"); ?>";
Calendar._TT["TODAY"] = "<?php xl("Today","e"); ?>";
Calendar._TT["TIME_PART"] = "<?php xl("(Shift-)Click or drag to change value","e"); ?>";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"] = "%a, %b %e";

Calendar._TT["WK"] = "<?php xl("wk","e"); ?>";
Calendar._TT["TIME"] = "<?php xl("Time","e"); ?>"+":";
</script>