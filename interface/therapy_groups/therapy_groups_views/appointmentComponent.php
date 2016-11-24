<div class="row" xmlns="http://www.w3.org/1999/html">
    <div class="col-md-7">
        <h5><?php echo xlt('Group appointments')?></h5>
    </div>
    <div class="col-md-5">
        <a href=""><button class="float-right"><?php echo xlt('Adding')?></button></a>
    </div>
</div>
<div id="component-border">
    <div class="row">
        <div class="col-md-12">
            <?php foreach ($events as $event){ ?>
                <?php $appt_day = "Mon"; ?>
                <div class="event_details">
                    <a href="">
                        <span><b><?php echo $event['pc_eventDate'] . " (" . $appt_day . ")" ;?></b></span>
                        </br>
                        <span>
                            <?php echo $event['pc_startTime'];
                            if($event['pc_recurrtype'] > 0)
                                echo "<img src='" . $GLOBALS['webroot'] . "/interface/main/calendar/modules/PostCalendar/pntemplates/default/images/repeating8.png' border='0' style='margin:0px 2px 0px 2px;' title='".htmlspecialchars(xl("Repeating event"),ENT_QUOTES)."' alt='".htmlspecialchars(xl("Repeating event"),ENT_QUOTES)."'>";
                            echo " (  " . $event['pc_apptstatus'] . "  )" ;?></span>
                        </br>
                        <span><?php echo $event['pc_catname'];?></span>
                        </br>
                        <span>
                            <?php foreach ($event['counselors'] as $counselor){
                                echo $counselor . "</br>";
                            }?>
                        </span>
                        </br>
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</div>