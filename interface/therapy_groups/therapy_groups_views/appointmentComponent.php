<?php
//If coming from participants controller groupId contains the id.
//If from group controller it's contained in groupData array.
if($groupData['group_id']) $groupId = $groupData['group_id'];
?>
<div class="row" xmlns="http://www.w3.org/1999/html">
    <div class="col-md-7">
        <h5><?php echo xlt('Group appointments')?></h5>
    </div>
    <div class="col-md-5">
        <a href="<?php echo "javascript:dlgopen('{$GLOBALS['rootdir']}/main/calendar/add_edit_event.php?group=true&groupid={$groupId}', '_blank', 775, 500)"; ?>"><button class="float-right"><?php echo xlt('Adding')?></button></a>
    </div>
</div>
<div id="component-border" class="appt-widget">
    <div class="row">
        <div class="col-md-12">
            <?php foreach ($events as $event){ ?>
                <?php
                //Taken from appointments.inc.php - gets proper date details
                $dayname = date("l", strtotime($event['pc_eventDate']));
                $dispampm = "am";
                $disphour = substr($event['pc_startTime'], 0, 2) + 0;
                $dispmin  = substr($event['pc_startTime'], 3, 2);
                if ($disphour >= 12) {
                    $dispampm = "pm";
                    if ($disphour > 12)
                        $disphour -= 12;
                }
                //Taken from demographics.php - prepares date for add_edit_event url
                $date_for_url = htmlspecialchars(preg_replace("/-/", "", $event['pc_eventDate']),ENT_QUOTES);
                ?>
                <div class="event_details">
                    <a href="<?php echo "javascript:dlgopen('{$GLOBALS['rootdir']}/main/calendar/add_edit_event.php?group=true&groupid={$groupId}&date={$date_for_url}&eid={$event['pc_eid']}', '_blank', 775, 500)"; ?>">
                        <span><b><?php echo $event['pc_eventDate'] . " (" . xl($dayname) . ")" ;?></b></span>
                        </br>
                        <span>
                            <?php echo $disphour . ":" . $dispmin . " " . $dispampm;
                            if($event['pc_recurrtype'] > 0)
                                echo "<img src='" . $GLOBALS['webroot'] . "/interface/main/calendar/modules/PostCalendar/pntemplates/default/images/repeating8.png' border='0' style='margin:0px 2px 0px 2px;' title='".htmlspecialchars(xl("Repeating event"),ENT_QUOTES)."' alt='".htmlspecialchars(xl("Repeating event"),ENT_QUOTES)."'>";
                            echo " (  " . $event['pc_apptstatus'] . "  )" ;?></span>
                        </br>
                        <span><?php echo $event['pc_catname'];?></span>
                        </br>
                        <span><?php echo $event['ufname'] . "  " . $event['ulname'] ;?></span>
                        </br></br>
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</div>