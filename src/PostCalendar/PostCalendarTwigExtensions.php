<?php
namespace OpenEMR\PostCalendar;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PostCalendarTwigExtensions  extends AbstractExtension
{
    /**
     * @param string $displayString This is the text to be displayed(most likely representing the time of an event).  It is the responsibility of the caller to escape any entities as needed. This allows html tags to be used in the $displayString if desired.
     * @return string html anchor element with javascript onclick event that edits an appointment
     */
    public function create_event_time_anchor($displayString) {
        $title = xl('Click to edit');
        return "<a class='event_time' onclick='event_time_click(this)' title='" . attr($title) . "'>" . text($displayString) . "</a>";
    }

    public function monthSelector($Date)
    {
        // caldate depends on what the user has clicked
        $caldate = strtotime($Date);
        $cMonth = date("m", $caldate);
        $cYear = date("Y", $caldate);
        $cDay = date("d", $caldate);

        include_once($GLOBALS['fileroot'].'/interface/main/calendar/modules/PostCalendar/pntemplates/default/views/monthSelector.php');
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('monthSelector', [$this, 'monthSelector'])
            ,
            new TwigFunction(
                'create_event_time_anchor', [$this, 'create_event_time_anchor']
            ),
            new TwigFunction(
                'renderProviderTimeSlots', [$this, 'renderProviderTimeSlots']
            ),
            new TwigFunction(
                'is_weekend_day', function($date) {
                return is_weekend_day($date);
            }
            ),
            new TwigFunction(
                'PrintDatePicker', [$this, 'PrintDatePicker']
            ),
            new TwigFunction(
                'generateDOWCalendar', [$this, 'generateDOWCalendar']
            ),
            new TwigFunction(
                'PrintEvents', [$this, 'PrintEvents'], ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'getProviderInfo', [$this, 'getProviderInfo']
            ),
            new TwigFunction(
                'datetimepickerJsConfig', [$this, 'getDatetimepickerJsConfig'], ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'getCalendarImagePath', [$this, 'getCalendarImagePath']
            ),
            new TwigFunction(
                'generatePrintURL', [$this, 'generatePrintURL']
            )
        ];
    }

    /**
     * Generate the calendar data structure needed for the date picker
     * @param string $dateString Date in YYYYMMDD format
     * @param array $DOWlist Array of day of week indices (0-6)
     * @return array Calendar data structure for Twig template
     */
    public function generateDOWCalendar($dateString, $DOWlist)
    {
        $caldate = strtotime($dateString);
        $year = date('Y', $caldate);
        $month = date('m', $caldate);
        $day = date('d', $caldate);
        
        // Calculate the start and end dates for the calendar view
        $startdate = strtotime($year . $month . "01");
        $enddate = strtotime($year . $month . date("t", $startdate) . " 23:59");
        
        // Adjust to start on the first day of week
        while (date('w', $startdate) != $DOWlist[0]) {
            $startdate -= 60 * 60 * 24;
        }
        
        // Adjust to end on the last day of week
        while (date('w', $enddate) != $DOWlist[6]) {
            $enddate += 60 * 60 * 24;
        }
        
        $currdate = $startdate;
        $calendar = [];
        $week = [];
        
        // Build the calendar array
        while ($currdate <= $enddate) {
            // Start a new week row
            if (date('w', $currdate) == $DOWlist[0] && !empty($week)) {
                $calendar[] = $week;
                $week = [];
            }
            
            // Set the TD class
            $tdClass = "tdMonthDay-small";
            if (date('m', $currdate) != $month) {
                $tdClass = "tdOtherMonthDay-small";
            }
            if (is_weekend_day(date('w', $currdate))) {
                $tdClass = "tdWeekend-small";
            }
            if (is_holiday(date('Y-m-d', $currdate))) {
                $tdClass = "tdHoliday-small";
            }
            
            // Mark the current date
            if (date('Ymd', $currdate) == $dateString) {
                $tdClass .= " currentDate";
            }
            
            // Add the date picker class
            $tdClass .= " tdDatePicker";
            
            // Add the day to the week
            $week[] = [
                'class' => $tdClass,
                'id' => date("Ymd", $currdate),
                'title' => xl("Go to") . " " . date('M d, Y', $currdate),
                'text' => date('d', $currdate)
            ];
            
            // Move to the next day
            $currdate = strtotime("+1 day", $currdate);
        }
        
        // Add the last week if it's not empty
        if (!empty($week)) {
            $calendar[] = $week;
        }
        
        return $calendar;
    }

    /**
     * Generate print URL for calendar views
     * Replicates the pnModURL functionality from Smarty templates
     */
    public function generatePrintURL($template_view, $viewtype, $Date, $pc_username = '', $category = '', $topic = '')
    {
        // Build the URL parameters - always include all parameters to match Smarty behavior
        $params = array(
            'module' => 'PostCalendar',
            'func' => 'view',
            'tplview' => $template_view,
            'viewtype' => $viewtype,
            'Date' => $Date,
            'print' => 1,
            'pc_username' => $pc_username,
            'pc_category' => $category,
            'pc_topic' => $topic
        );
        
        // Build the query string
        $queryString = http_build_query($params);
        
        // Return the full URL (assuming index.php as the base)
        return 'index.php?' . $queryString;
    }

    /* output a small calendar, based on the date-picker code from the normal calendar */
    public function PrintDatePicker($dateString, $DOWlist, $daynames) {

        $caldate = strtotime($dateString);
        $cMonth = date("m", $caldate);
        $cYear = date("Y", $caldate);
        $cDay = date("d", $caldate);

        echo '<table>';
        echo '<tr>';
        echo '<td colspan="7" class="tdMonthName-small">';
        echo text(date('F Y', $caldate));
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        foreach ($DOWlist as $dow) {
            echo "<td class='tdDOW-small'>" . text($daynames[$dow]) . "</td>";
        }
        echo '</tr>';

        // to make a complete week row we need to compute the real
        // start and end dates for the view
        list ($year, $month, $day) = explode(" ", date('Y m d', $caldate));
        $startdate = strtotime($year.$month."01");
        while (date('w', $startdate) != $DOWlist[0]) { $startdate -= 60*60*24; }

        $enddate = strtotime($year.$month.date("t", $month));
        while (date('w', $enddate) != $DOWlist[6]) { $enddate += 60*60*24; }

        $currdate = $startdate;
        while ($currdate <= $enddate) {
            if (date('w', $currdate) == $DOWlist[0]) {
                echo "<tr>";
            }

            // we skip outputting some days
            $skipit = false;

            // set the TD class
            $tdClass = "tdMonthDay-small";
            if (date('m', $currdate) != $month) {
                $tdClass = "tdOtherMonthDay-small";
                $skipit = true;
            }
            if ((date('w', $currdate) == 0) || (date('w', $currdate) == 6)) {
                $tdClass = "tdWeekend-small";
            }

            if (!empty($Date) && (date('Ymd',$currdate) == $Date)) {
                // $Date is defined near the top of this file
                // and is equal to whatever date the user has clicked
                $tdClass .= " currentDate";
            }

            // add a class so that jQuery can grab these days for the 'click' event
            $tdClass .= " tdDatePicker";

            // output the TD
            $td = "<td ";
            $td .= "class=\"" . attr($tdClass) . "\" ";
            //$td .= "id=\"" . attr(date("Ymd", $currdate)) . "\" ";
            $td .= "id=\"" . attr(date("Ymd", $currdate)) . "\" ";
            $td .= "title=\"Go to week of " . attr(date('M d, Y', $currdate)) . "\" ";
            $td .= "> " . text(date('d', $currdate)) . "</td>\n";
            if ($skipit == true) { echo "<td></td>"; }
            else { echo $td; }

            // end of week row
            if (date('w', $currdate) == $DOWlist[6]) echo "</tr>\n";

            // time correction = plus 1000 seconds, for some unknown reason
            $currdate += (60*60*24)+1000;
        }
        echo "</table>";
    }

    private function overlapDetection($times, $interval, $events, $calEndMin, $calStartMin, $providerid)
    {
        // determine if events overlap and adjust their width and left position as needed
        // 26 Feb 2008 - This needs fine tuning or total replacement
        //             - it doesn't work as well as I'd like - JRM
        foreach ($times as $slottime) {
            $starttimeh = $slottime['hour'];
            $starttimem = $slottime['minute'];

            $slotstartmins = $starttimeh * 60 + $starttimem;
            $slotendmins = $starttimeh * 60 + $starttimem + $interval;

            $events_in_timeslot = array();
            foreach ($events as $e1) {
                // ignore IN event
                if (($e1['catid'] == 2)) { continue; }

                // skip events without an ID (why they are in the loop, I have no idea)
                if ($e1['eid'] == "") { continue; }

                // skip events for other providers
                // $e1['aid']!=0 :With the holidays we included clinic events, they have provider id =0
                // we dont want to exclude those events from being displayed
                if ($providerid != $e1['aid'] && $e1['aid'] != 0) { continue; }

                // specially handle all-day events
                if ($e1['alldayevent'] == 1) {
                    $tmpTime = $times[0];
                    if (strlen($tmpTime['hour']) < 2) { $tmpTime['hour'] = "0".$tmpTime['hour']; }
                    if (strlen($tmpTime['minute']) < 2) { $tmpTime['minute'] = "0".$tmpTime['minute']; }
                    $e1['startTime'] = $tmpTime['hour'].":".$tmpTime['minute'].":00";
                    $e1['duration'] = ($calEndMin - $calStartMin) * 60;  // measured in seconds
                }

                // create a numeric start and end for comparison
                $starth = substr($e1['startTime'], 0, 2);
                $startm = substr($e1['startTime'], 3, 2);
                $e1Start = ($starth * 60) + $startm;
                $e1End = $e1Start + $e1['duration']/60;

                // three ways to overlap:
                // start-in, end-in, span
                if ((($e1Start >= $slotstartmins) && ($e1Start < $slotendmins)) // start-in
                    || (($e1End > $slotstartmins) && ($e1End <= $slotendmins)) // end-in
                    || (($e1Start < $slotstartmins) && ($e1End > $slotendmins))) // span
                {
                    array_push($events_in_timeslot, $e1['eid']);
                    if($e1['catid'] == 3)
                    {
                        array_pop($events_in_timeslot);
                        array_unshift($events_in_timeslot, $e1['eid']);
                    }
                }

            }
            $leftpos = 0;
            $width = 100;
            $eventPositions = [];
            if (!empty($events_in_timeslot)) {
                $width = 100 / count($events_in_timeslot);

                // loop over the events in this timeslot and adjust their width
                foreach ($events_in_timeslot as $eid) {
                    // set the width if not already set or if the current width is smaller
                    // than was was previously set
                    if (!isset($eventPositions[$eid]->width))
                    {
                        $eventPositions[$eid] = new \stdClass();
                        $eventPositions[$eid]->width = $width;
                    } else if ($eventPositions[$eid]->width > $width)
                    {
                        $eventPositions[$eid]->width = $width;
                    }

                    // set the left position if not already set or if the current left is
                    // greater than what was previously set
                    if (!isset($eventPositions[$eid]->leftpos))
                    {
                        $eventPositions[$eid]->leftpos = $leftpos;
                    } else if ($eventPositions[$eid]->leftpos < $leftpos)
                    {
                        $eventPositions[$eid]->leftpos = $leftpos;
                    }

                    // increment the leftpos by the width
                    $leftpos += $width;
                }
            }
        } // end overlap detection
        return $eventPositions;
    }

    public function renderProviderTimeSlots($times, $events, $interval, $provider, $providerid, $calEndMin, $calStartMin
        , $timeformat, $openhour, $closehour, $timeslotHeightVal, $timeslotHeightUnit, $date, $viewtype)
    {
        $eventdate = substr($date, 0, 4) . substr($date, 5, 2) . substr($date, 8, 2);
        $defaultDate = date("Y-m-d", strtotime($date));
        
        // Generate empty time slots for creating new appointments
        $html = '';
        
        // Always create time slots container, regardless of whether there are events or not
        if ($viewtype == 'week' || $viewtype == 'day') {
            $html .= "<div class='time-slots-container' style='position: absolute; width: 100%; height: 100%; z-index: 1;'>";
            foreach ($times as $slottime) {
                $startampm = (isset($slottime['mer'])) ? (($slottime['mer'] == "pm") ? 2 : 1) : 1;
                $starttimeh = $slottime['hour'];
                $starttimem = $slottime['minute'];
                $slotendmins = $starttimeh * 60 + $starttimem + $interval;
                
                // default to the passed-in provider
                $in_cat_id = 0; // default category id for a new event
                // Format hour for display
                $disptimeh = ($starttimeh > 12 && $timeformat == 12) ? ($starttimeh - 12) : $starttimeh;
                $disptimeh = ($starttimeh === 0 && $timeformat == 12) ? 12 : $disptimeh;
                
                // Calculate the vertical position of this timeslot
                $eMinDiff = ($starttimeh * 60 + $starttimem) - $calStartMin;
                $eStartInterval = $eMinDiff / $interval;
                $eStartPos = $eStartInterval * $timeslotHeightVal;
                $slotTop = $eStartPos . $timeslotHeightUnit;
                
                // Position and style for the time slot
                $html .= "<div class='time-slot' style='position: absolute; top: " . attr($slotTop) . "; width: 100%; height: " . attr($timeslotHeightVal . $timeslotHeightUnit) . ";'>";
                $html .= "<a href='javascript:newEvt(" . attr_js($startampm) . "," . attr_js($starttimeh) . "," . attr_js($starttimem) . "," . attr_js($defaultDate) . "," . attr_js($providerid) . "," . attr_js($in_cat_id) . ")' 
                    title='" . xla("New Appointment") . "' 
                    class='time-slot-link' data-time='" . text($disptimeh . ":" . str_pad($starttimem, 2, '0', STR_PAD_LEFT)) . "'>
                    " . text($disptimeh . ":" . str_pad($starttimem, 2, '0', STR_PAD_LEFT)) . "
                </a>";
                $html .= "</div>";
            }
            $html .= "</div>";
        }

        $eventPositions = $this->overlapDetection($times, $interval, $events, $calEndMin, $calStartMin, $providerid);

        // now loop over the events for the day and output their DIVs
        foreach ($events as $event) {
            // skip events for other providers
            // yeah, we've got that sort of overhead here... it ain't perfect
            // $event['aid']!=0 :With the holidays we included clinic events, they have provider id =0
            // we dont want to exclude those events from being displayed
            if (!empty($event['aid']) && ($providerid != $event['aid'])) { continue; }

            // skip events without an ID (why they are in the loop, I have no idea)
            if (empty($event['eid'])) { continue; }

            // specially handle all-day events
            if ($event['alldayevent'] == 1) {
                $tmpTime = $times[0];
                if (strlen($tmpTime['hour']) < 2) { $tmpTime['hour'] = "0".$tmpTime['hour']; }
                if (strlen($tmpTime['minute']) < 2) { $tmpTime['minute'] = "0".$tmpTime['minute']; }
                $event['startTime'] = $tmpTime['hour'].":".$tmpTime['minute'].":00";
                $event['duration'] = ($calEndMin - $calStartMin) * 60; // measured in seconds
            }

            // figure the start time and minutes (from midnight)
            $starth = substr($event['startTime'], 0, 2);
            $startm = substr($event['startTime'], 3, 2);
            $eStartMin = $starth * 60 + $startm;
            $dispstarth = ($starth > 12) ? ($starth - $timeformat) : $starth; // used to display the hour


            //fix bug 456 and 455
            //check to see if the event is in the clinic hours range, if not it will not be displayed
            if  ( (int)$starth < (int)$openhour || (int)$starth > (int)$closehour ) { continue; }

            // determine the class for the event DIV based on the event category
            $evtClass = "event_appointment";
            switch ($event['catid']) {
                case 1:  // NO-SHOW appt
                    $evtClass = "event_noshow";
                    break;
                case 2:  // IN office
                    $evtClass = "event_in";
                    break;
                case 3:  // OUT of office
                    $evtClass = "event_out";
                    break;
                case 4:  // VACATION
                    $evtClass = "event_reserved";
                    break;
                case 6:  // HOLIDAY
                    $evtClass = "event_holiday";
                    break;
                case 8:  // LUNCH
                    $evtClass = "event_reserved";
                    break;
                case 11: // RESERVED
                    $evtClass = "event_reserved";
                    break;
                case 99: // HOLIDAY
                    $evtClass = "event_holiday";
                    break;
                default: // some appointment
                    $evtClass = "event_appointment";
                    break;
            }
            // eventViewClass allows the event class to override the template (such as from a dispatched system event).
            $evtClass = $event['eventViewClass'] ?? $evtClass;

            // if this is an IN or OUT event then we have some extra special
            // processing to be done
            // the IN event creates a DIV until the OUT event
            // or, without an OUT DIV matching the IN event
            // then the IN event runs until the end of the day
            if ($event['catid'] == 2) {
                // locate a matching OUT for this specific IN
                $found = false;
                $outMins = 0;
                foreach ($events as $outevent) {
                    // skip events for other providers
                    if (!empty($outevent['aid']) && ($providerid != $outevent['aid'])) { continue; }
                    // skip events with blank IDs
                    if (empty($outevent['eid'])) { continue; }

                    if ($outevent['eid'] == $event['eid']) { $found = true; continue; }
                    if (($found == true) && ($outevent['catid'] == 3)) {
                        // calculate the duration from this event to the outevent
                        $outH = substr($outevent['startTime'], 0, 2);
                        $outM = substr($outevent['startTime'], 3, 2);
                        $outMins = ($outH * 60) + $outM;
                        $event['duration'] = ($outMins - $eStartMin) * 60; // duration is in seconds
                        $found = 2;
                        break;
                    }
                }
                if ($outMins == 0) {
                    // no OUT was found so this event's duration goes
                    // until the end of the day
                    $event['duration'] = ($calEndMin - $eStartMin) * 60; // duration is in seconds
                }
            }

            // calculate the TOP value for the event DIV
            // diff between event start and schedule start
            $eMinDiff = $eStartMin - $calStartMin;
            // diff divided by the time interval of the schedule
            $eStartInterval = $eMinDiff / $interval;
            // times the interval height
            $eStartPos = $eStartInterval * $timeslotHeightVal;
            $evtTop = $eStartPos.$timeslotHeightUnit;
            // calculate the HEIGHT value for the event DIV
            // diff between end and start of event
            $eEndMin = $eStartMin + ($event['duration']/60);
            // prevent the overall height of the event from going beyond the bounds
            // of the time table
            if ($eEndMin > $calEndMin) { $eEndMin = $calEndMin + $interval; }
            $eMinDiff = $eEndMin - $eStartMin;
            // diff divided by the time interval of the schedule
            $eEndInterval = $eMinDiff / $interval;
            // times the interval height
            $eHeight = $eEndInterval * $timeslotHeightVal;
            // this cat id was only on the daily view... do we want it to be a weekly or monthly?
            if($event['catid']==3)
            {
                // An "OUT" that is "zero duration" still needs height so we can click it.
                $eHeight = $eEndInterval==0 ? $timeslotHeightVal : $eHeight ;
            }
            $evtHeight = $eHeight.$timeslotHeightUnit;

            // determine the DIV width based on any overlapping events
            // see further above for the overlapping calculation code
            $divWidth = "";
            $divLeft = "";
            if (isset($eventPositions[$event['eid']])) {
                $divWidth = "width: ".$eventPositions[$event['eid']]->width."%";
                $divLeft = "left: ".$eventPositions[$event['eid']]->leftpos."%";
            }

            $eventid = $event['eid'] ?? null;
            $eventtype = sqlQuery("SELECT pc_cattype FROM openemr_postcalendar_categories as oc LEFT OUTER JOIN openemr_postcalendar_events as oe ON oe.pc_catid=oc.pc_catid WHERE oe.pc_eid=?", [$eventid]);
            $pccattype = '';
            if($eventtype['pc_cattype']==1)
                $pccattype = 'true';
            $patientid = $event['pid'];
            $commapos = strpos(($event['patient_name'] ?? ''), ",");
            $lname = substr(($event['patient_name'] ?? ''), 0, $commapos);
            $fname = substr(($event['patient_name'] ?? ''), $commapos + 2);
            $patient_dob = oeFormatShortDate($event['patient_dob']);
            $patient_age = $event['patient_age'];
            $catid = $event['catid'] ?? '';
            $comment = $event['hometext'];
            $catname = $event['catname'];
            $title = "Age $patient_age ($patient_dob)";

            //Variables for therapy groups
            $groupid = $event['gid'];
            // this comes from weekly view, was not present on daily view.
            if($groupid) $patientid = '';
            $groupname = $event['group_name'];
            $grouptype = $event['group_type'];
            $groupstatus = $event['group_status'];
            $groupcounselors = '';
            foreach($event['group_counselors'] as $counselor){
                $groupcounselors .= getUserNameById($counselor) . " \n ";
            }
            $content = "";

            if ($comment && $GLOBALS['calendar_appt_style'] < 4) $title .= " " . $comment;

            // note we've merged the day and week divTitle's here but we should look at merging these
            // the divTitle is what appears when the user hovers the mouse over the DIV
            if ($viewtype == 'week') {
                $divTitle = date("D, d M Y", strtotime($date));
            } else {
                if ($groupid)
                    $divTitle = xl('Counselors') . ": \n" . $groupcounselors . " \n";
                else
                    $divTitle = $provider["fname"] . " " . $provider["lname"];
            }
            $result = sqlStatement("SELECT name,id,color FROM facility WHERE id=(SELECT pc_facility FROM openemr_postcalendar_events WHERE pc_eid=?)", [$eventid]);
            $row = sqlFetchArray($result);
            $color=$event["catcolor"];
            if($GLOBALS['event_color']==2)
                $color=$row['color'];
            $divTitle .= "\n" . $row['name'];

            if ($catid == 2 || $catid == 3 || $catid == 4 || $catid == 8 || $catid == 11) {
                if      ($catid ==  2) $catname = xl("IN");
                else if ($catid ==  3) $catname = xl("OUT");
                else if ($catid ==  4) $catname = xl("VACATION");
                else if ($catid ==  8) $catname = xl("LUNCH");
                else if ($catid == 11) $catname = xl("RESERVED");

                $atitle = $catname;
                if ($comment) $atitle .= " $comment";
                $divTitle .= "\n[".$atitle ."]";
                $content .= text($catname);
                if ($event['recurrtype'] > 0) {
                    $imagePath = $this->getCalendarImagePath();
                    $content .= "<img class='border-0' src='" . attr($imagePath . "/repeating8.png") . "' style='margin: 0 2px 0 2px;' title='" . attr(xl("Repeating event")) . "' alt='" . attr(xl("Repeating event")) . "' />";
                }
                if ($comment) $content .= " " . text($comment);
            }
            else {
                // TODO: all these changes have issues with the divtitle
                // some sort of patient appointment
                if($groupid){
                    $divTitle .= "\n" . xl('Counselors') . ": \n" . $groupcounselors . " \n";
                    $divTitle .= "\r\n[" . $catname . ' ' . $comment . "]" . $groupname;
                }
                else
                    $divTitle .= "\r\n[" . $catname . ' ' . $comment . "]" .$fname . " " . $lname;

                $content .= "<span class='appointment" . attr($apptToggle ?? "") . "'>";
                $content .= $this->create_event_time_anchor($dispstarth . ':' . $startm);
                if ($event['recurrtype'] > 0) {
                    $imagePath = $this->getCalendarImagePath();
                    $content .= "<img src='" . attr($imagePath . "/repeating8.png") . "' class='border-0' style='margin:0 2px 0 2px;' title='" . attr(xl("Repeating event")) . "' alt='" . attr(xl("Repeating event")) . "' />";
                }
                $content .= text($event['apptstatus']);

                if ($patientid) {
                    // include patient name and link to their details
                    $link_title = $fname . " " . $lname . " \n";
                    $link_title .= xl('Age') . ": " . $patient_age . "\n" . xl('DOB') . ": " . $patient_dob . " $comment" . "\n";
                    $link_title .= "(" . xl('Click to view') . ")";
                    $content .= "<a class='link_title' data-pid='". attr($patientid) . "' href='javascript:goPid(" . attr_js($patientid) . ")' title='" . attr($link_title) . "'>";
                    $content .= "<i class='fas fa-user text-success' onmouseover=\"javascript:ShowImage(" . attr_js($GLOBALS['webroot']."/controller.php?document&retrieve&patient_id=".attr($patientid)."&document_id=-1&as_file=false&original_file=true&disable_exit=false&show_original=true&context=patient_picture") . ");\" onmouseout=\"javascript:HideImage();\" title='" . attr($link_title) . "'></i>";
                    if ($catid == 1) $content .= "<s>";
                    $content .= text($lname);
                    if ($GLOBALS['calendar_appt_style'] != 1) {
                        $content .= "," . text($fname);
                        if ($event['title'] && $GLOBALS['calendar_appt_style'] >= 3) {
                            $content .= " (" . text($event['title']);
                            if ($event['hometext'] && $GLOBALS['calendar_appt_style'] >= 4)
                                $content .= ": <span class='text-success'>" . text(trim($event['hometext'])) . "</span>";
                            $content .= ")";
                        }
                    }
                    if ($catid == 1) $content .= "</s>";
                    $content .= "</a>";
                    $content .= '<a class="show-appointment shown"></a>';
                }
                elseif($groupid){
                    $divTitle .= "\n" . getTypeName($grouptype) . "\n";
                    $link_title = '';
                    $link_title .= $divTitle ."\n";
                    $link_title .= "(" . xl('Click to view') . ")";
                    $content .= "<a href='javascript:goGid(" . attr_js($groupid) . ")' title='" . attr($link_title) . "'>";
                    $content .= "<i class='fas fa-user text-primary' title='" . attr($link_title) . "'></i>";
                    if ($catid == 1) $content .= "<s>";
                    $content .= text($groupname);
                    if ($GLOBALS['calendar_appt_style'] != 1) {
                        if ($event['title'] && $GLOBALS['calendar_appt_style'] >= 3) {
                            $content .= "(" . text($event['title']);
                            if ($event['hometext'] && $GLOBALS['calendar_appt_style'] >= 4)
                                $content .= ": <span class='text-success'>" . text(trim($event['hometext'])) . "</span>";
                            $content .= ")";
                        }
                    }
                    if ($catid == 1) $content .= "</s>";
                    $content .= "</a>";

                    //Add class to wrapping div so EditEvent js function can differentiate between click on group and patient
                    $evtClass .= ' groups ';
                }
                else {
                    //Category Clinic closaed or holiday take the event title
                    if ( $catid == 6 || $catid == 7){
                        $content .= xlt($event['title']);
                    }else{
                        // no patient id, just output the category name
                        $content .= text(xl_appt_category($catname));
                    }
                }
                $content .= "</span>";
            }

            $divTitle .= "\n(" . xl('double click to edit') . ")";

            if($_SESSION['pc_facility'] == 0){
                // a special case for the 'IN' event this puts the time ABOVE
                // the normal DIV so it doesn't overlap another event DIV and include the time
                if ($event['catid'] == 2) {
                    $inTop = 20+($eStartPos - $timeslotHeightVal).$timeslotHeightUnit;
                    echo "<div data-eid='" . attr($eventid) . "' class='" . attr($evtClass) . " event in_start' style='top:".$inTop.
                        "; height:".$timeslotHeightVal.$timeslotHeightUnit.
                        "; $divWidth".
                        "; $divLeft".
                        "; border: none".
                        "' title='" . attr($divTitle) . "'".
                        " id='" . attr($eventdate) . "-" . attr($eventid) . "-" . attr($pccattype) . "'".
                        ">";
                    $content = text($dispstarth) . ':' . text($startm) . " " . $content;
                    echo $content;
                    echo "</div>\n";
                }

                // output the DIV and content
                //This is to differentiate between the events of holiday(6) or vacation(4) in order to disable
                //the ability to double click and edit this events
                if ($event['catid']!="6" && $event['catid']!="4" )
                {
                    $background_string = "; background-color:" . attr($color);
                    echo "<div data-eid='" . attr($eventid) . "' class='" . attr($evtClass) . " event' style='top:".$evtTop."; height:".$evtHeight.
                        $background_string.
                        "; $divWidth".
                        "; $divLeft".
                        "' title='" . attr($divTitle) . "'".
                        " id='" . attr($eventdate) . "-" . attr($eventid) . "-" . attr($pccattype) . "'".
                        ">";
                } else {
                    $background_string= "; background-color:" . attr($color);
                    echo "<div data-eid='" . attr($eventid) . "' class='" . attr($evtClass) . " hiddenevent' style='top:".$evtTop."; height:".$evtHeight.
                        $background_string.
                        "; $divWidth".
                        "; $divLeft".
                        "' title='" . attr($divTitle) . "'".
                        " id='" . attr($eventdate) . "-" . attr($eventid) . "-" . attr($pccattype) . "'".
                        ">";
                }
                // second part for the special IN event
                if ($event['catid'] != 2) { echo $content; }
                echo "</div>\n";
            }
            elseif($_SESSION['pc_facility'] == $row['id']){
                if ($event['catid'] == 2) {
                    $inTop = 20+($eStartPos - $timeslotHeightVal).$timeslotHeightUnit;
                    echo "<div data-eid='" . attr($eventid) . "' class='" . attr($evtClass) . " event in_start' style='top:".$inTop.
                        "; height:".$timeslotHeightVal.$timeslotHeightUnit.
                        "; $divWidth".
                        "; $divLeft".
                        "; border: none".
                        "' title='" . attr($divTitle) . "'".
                        " id='" . attr($eventdate) . "-" . attr($eventid) . "-" . attr($pccattype) . "'".
                        ">";
                    $content = text($dispstarth) . ':' . text($startm) . " " . $content;
                    echo $content;
                    echo "</div>\n";
                }

                // output the DIV and content
                // For "OUT" events, applying the background color in CSS.
                $background_string= "; background-color:".$event["catcolor"];
                echo "<div data-eid='" . attr($eventid) . "' class='" . attr($evtClass) . " event' style='top:".$evtTop."; height:".$evtHeight.
                    $background_string.
                    "; $divWidth".
                    "; $divLeft".
                    "' title='" . attr($divTitle) . "'".
                    " id='" . attr($eventdate) . "-" . attr($eventid) . "-" . attr($pccattype) . "'".
                    ">";
                // second part for the special IN event
                if ($event['catid'] != 2) { echo $content; }
                echo "</div>\n";
            }
            else{

                if ($event['catid'] == 2) {
                    $inTop = 20+($eStartPos - $timeslotHeightVal).$timeslotHeightUnit;
                    echo "<div data-eid='" . attr($eventid) . "' class='" . attr($evtClass) ." event in_start' style='top:".$inTop.
                        "; height:".$timeslotHeightVal.$timeslotHeightUnit.
                        "; $divWidth".
                        "; $divLeft".
                        "; background: var(--gray300)".
                        "; border: none".
                        "' title='" . attr($divTitle) . "'".
                        " id='" . attr($eventdate) . "-" . attr($eventid) . "-" . attr($pccattype) . "'".
                        ">";
                    $content = "<span class='text-danger text-center font-weight-bold'>" . text($row['name']) . "</span>";
                    echo $content;
                    echo "</div>\n";
                }

                // output the DIV and content
                echo "<div data-eid='" . attr($eventid) . "' class='" . attr($evtClass) . " event' style='top:".$evtTop."; height:".$evtHeight.
                    "; background-color: var(--gray300)".
                    "; $divWidth".
                    "; $divLeft".
                    "' title='" . attr($divTitle) . "'".
                    " id='" . attr($eventdate) . "-" . attr($eventid) . "-" . attr($pccattype) . "'".
                    ">";
                // second part for the special IN event
                if ($event['catid'] != 2) { echo "<span class='text-danger text-center font-weight-bold'>" . text($row['name']) . "</span>"; }
                echo "</div>\n";
            }
        } // end EVENT loop

        echo "</div>";

        // Return all the html
        return $html;
    }

    /**
     * Get provider information by provider ID
     * @param int $providerID Provider ID
     * @return array Provider information
     */
    public function getProviderInfo($providerID)
    {
        $provquery = "SELECT * FROM users WHERE id=?";
        $res = sqlStatement($provquery, [$providerID]);
        $provinfo = sqlFetchArray($res);
        return $provinfo;
    }
    
    /**
     * Get the JavaScript configuration for the datetimepicker
     * @return string JavaScript configuration
     */
    public function getDatetimepickerJsConfig()
    {
        ob_start();
        $datetimepicker_timepicker = false;
        $datetimepicker_showseconds = false;
        $datetimepicker_formatInput = false;
        require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php');
        $output = ob_get_clean();
        return $output;
    }

    /**
     * Gets the path to calendar images for use in templates
     * @return string The path to calendar images
     */
    public function getCalendarImagePath()
    {
        global $webroot;
        return $webroot . "/interface/main/calendar/modules/PostCalendar/pntemplates/default/images";
    }

    public function __construct($smarty = null)
    {
        $this->_smarty = $smarty;
        $this->_tpl_vars = [];
        
        // Define necessary variables for Twig template rendering
        global $rootdir, $webroot;
        $this->_tpl_vars['rootdir'] = $rootdir;
        $this->_tpl_vars['webroot'] = $webroot;
        $this->_tpl_vars['TPL_IMAGE_PATH'] = $webroot . "/interface/main/calendar/modules/PostCalendar/pntemplates/default/images";
        
        // Other initializations...
    }
}
