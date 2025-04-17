<?php

/**
 * interface/therapy_groups/therapy_groups_views/appointmentComponent.php contains widget for group appointments.
 *
 * In group/participant details screen this widget shows the group's appointments.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>.
 * @copyright Copyright (c) 2016 Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Acl\AclMain;

?>


<?php $edit = AclMain::aclCheckCore("groups", "gcalendar", false, 'write');?>
<?php $view = AclMain::aclCheckCore("groups", "gcalendar", false, 'view');?>


<?php if ($view || $edit) {?>
    <?php
//If coming from participants controller groupId contains the id.
//If from group controller it's contained in groupData array.
    if ($groupData['group_id']) {
        $groupId = $groupData['group_id'];
    }
    ?>
<div class="row" xmlns="http://www.w3.org/1999/html">
    <div class="col-md-7">
        <h5><?php echo xlt('Group appointments')?></h5>
    </div>
    <div class="col-md-5">
        <?php if ($edit) { ?>
        <button id="addEvent" class="float-right"><?php echo xlt('Adding')?></button>
      <?php }?>
    </div>
</div>
<div id="component-border" class="appt-widget">
    <div class="row">
        <div class="col-md-12">
            <?php foreach ($events as $event) { ?>
                <?php
                //Taken from appointments.inc.php - gets proper date details
                $dayname = date("l", strtotime($event['pc_eventDate']));
                $dispampm = "am";
                $disphour = substr($event['pc_startTime'], 0, 2) + 0;
                $dispmin  = substr($event['pc_startTime'], 3, 2);
                if ($disphour >= 12) {
                    $dispampm = "pm";
                    if ($disphour > 12) {
                        $disphour -= 12;
                    }
                }

                //Taken from demographics.php - prepares date for add_edit_event url
                $date_for_url = preg_replace("/-/", "", $event['pc_eventDate']);
                ?>
                <div class="event_details">
                        <?php if ($edit) { ?>
                            <a onclick="goToEvent(<?php echo attr_js("{$GLOBALS['rootdir']}/main/calendar/add_edit_event.php?group=true&groupid=" . urlencode($groupId) . "&date=" . urlencode($date_for_url) . "&eid=" . urlencode($event['pc_eid'])); ?>)">
                        <?php } ?>
                        <span class="font-weight-bold"><?php echo text($event['pc_eventDate']) . " (" . xlt($dayname) . ")" ;?></span>
                        <br />
                        <span>
                            <?php echo text($disphour) . ":" . text($dispmin) . " " . text($dispampm);
                            if ($event['pc_recurrtype'] > 0) {
                                echo "<img src='" . $GLOBALS['webroot'] . "/interface/main/calendar/modules/PostCalendar/pntemplates/default/images/repeating8.png' border='0' style='margin:0px 2px 0px 2px;' title='" . xla("Repeating event") . "' alt='" . xla("Repeating event") . "'>";
                            }

                            echo " (  " . text($event['pc_apptstatus']) . "  )" ;?></span>
                        <br />
                        <span><?php echo xlt($event['pc_catname']);?></span>
                        <br />
                        <span><?php echo text($event['ufname']) . "  " . text($event['ulname']) ;?></span>
                        <br /><br />
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<script>

    $('#addEvent').on('click', function(){
        top.restoreSession();
        var url = <?php echo js_escape($GLOBALS['rootdir']); ?> + '/main/calendar/add_edit_event.php?group=true&groupid=' + <?php echo js_url($groupId); ?>;
        dlgopen(url, '_blank', 775, 500);
    });

    function goToEvent(url) {
        top.restoreSession();
        dlgopen(url, '_blank', 775, 500);
    }
</script>
<?php } ?>
