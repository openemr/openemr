<?php require 'header.php'; ?>
<main id="group-details">
    <div class="container container-group">
        <div id="top-row" class="row">
            <div id="main-component" class="col-md-8 col-sm-12">
                <div class="row">
                    <div class="col-md-8 col-sm-8">
                        <ul class="tabNav">
                            <li  class="current"><a><?php echo xlt('General data');?></a></li>
                            <li><a><?php echo xlt('Participants ');?></a></li>
                        </ul>
                    </div>
                    <div class="col-md-4 col-sm-4">
                        <button class="float-right"><?php echo xlt('update');?></button>
                    </div>
                </div>
                <div id="component-border">
                    <div class="row">
                        <div class="col-md-12">
                        </div>
                    </div>
                </div>
            </div>
            <div id="appointment-component" class="col-md-4 col-sm-12">
                <?php require 'appointmentComponent.php';?>
            </div>
        </div>
        <div class="row">
            <div id="history-component" class="col-md-12">
                <?php require 'pastMeetingsComponent.php';?>
            </div>
        </div>
    </div>
</main>
