<?php
    
    use OpenEMR\Core\Header;
    
    
    function common_head() {
        ?>
        
        <head>
            
            <?php Header::setupHeader([ 'jquery-ui', 'jquery-ui-cupertino', 'font-awesome-4-6-3']); ?>
    
            <title>OpenEMR Mobile</title>
            <meta content="width=device-width,initial-scale=1.0" name="viewport">
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="description" content="OpenEMR: Eye Exam">
            <meta name="author" content="OpenEMR: Ophthalmology">
            <?php common_style(); ?>
            <script type="text/javascript">
                <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
            </script>
      </head>
    <?php
    }
    
    function common_style() {
        ?>
        <style>
            #head_img {
                margin: 2vH;
                max-height: 15VH;
            }
            
            //bootstrap 4.1 not in codebase yet 8.17.18

            .btn-group > .btn:first-child:not(:last-child):not(.dropdown-toggle) {
                border-top-right-radius: 0;
                border-bottom-right-radius: 0;
            }
            .btn-group > .btn-group:not(:last-child) > .btn, .btn-group > .btn:not(:last-child):not(.dropdown-toggle) {
                border-top-right-radius: 0;
                border-bottom-right-radius: 0;
            }
            .btn-group > .btn:first-child {
                margin-left: 0;
            }
            .btn:not(:disabled):not(.disabled) {
                cursor: pointer;
            }
            .btn-group > .btn:first-child {
                margin-left: 0;
            }
            .btn-group-vertical > .btn, .btn-group > .btn {
                position: relative;
                -ms-flex: 0 1 auto;
                flex: 0 1 auto;
            }
            .btn-group-vertical > .btn, .btn-group > .btn {
                position: relative;
                float: left;
            }
            .btn-primary {
                color: #fff;
                background-color: #007bff;
                border-color: #007bff;
            }
            .btn {
                display: inline-block;
                font-weight: 400;
                text-align: center;
                white-space: nowrap;
                vertical-align: middle;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
                border: 1px solid transparent;
                border-top-color: transparent;
                border-right-color: transparent;
                border-bottom-color: transparent;
                border-left-color: transparent;
                padding: .375rem .75rem;
                font-size: 1rem;
                line-height: 1.5;
                border-radius: .25rem;
                border-top-right-radius: 0.25rem;
                border-bottom-right-radius: 0.25rem;
                transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
            }

            body {
                font-family: Roboto, HelveticaNeue, Arial, sans-serif;
                font-size: 10pt;
                margin: 0;
            }
            #gb-main {
                display: -webkit-box;
                display: flex;
                display: -ms-flexbox;
                display: flex;
                -webkit-box-orient: vertical;
                flex-direction: column;
                padding-right:5%;
                padding-left:5%;
            }
            
            header {
                display: -webkit-box;
                display: flex;
                display: -ms-flexbox;
                display: flex;
                height: 48px;
                width: 100%
            }

            #menu_top {
                display: -webkit-box;
                display: flex;
                display: -ms-flexbox;
                display: flex;
                padding-left: 10px;
            }

            #menu_top a {
                color: #757575;
                display: block;
                -webkit-box-flex: 0;
                flex: 0 0 auto;
                flex: 0 0 auto;
                font-size: 12px;
                font-weight: 600;
                line-height: 48px;
                margin-right: 8px;
                padding: 0 8px;
                text-align: center;
                text-transform: uppercase;
                text-decoration: none
            }

            #menu_top a.active {
                color: #4285f4;
                border-bottom: 2px solid #4285f4
            }
        </style>
    
        <?php
    }
    
    function common_header($display='') { ?>
        <header>
            <div id="menu_top">
                <a <?php
                if ($display =='photo') {
                    echo ' class="active" ';
                    } ?>
                        href="/openemr/interface/main/mobile/camera.php?v=<?php echo mt_rand(); ?>"><?php echo xlt('Upload'); ?></a>
                <a <?php
                    if ($display == 'cal') {
                        echo ' class="active" ';
                    } ?>
                        href="<?php echo $GLOBALS['webroot']; ?>/interface/main/calendar/index.php?module=PostCalendar&viewtype=day&func=view"><?php echo xlt('Calendar'); ?></a>
                <a <?php
                    if ($display =='flow') {
                        echo ' class="active" ';
                    } ?>
                        href="<?php echo $GLOBALS['webroot']; ?>/interface/patient_tracker/patient_tracker.php?skip_timeout_reset=1"><?php echo xlt('Flow'); ?></a>
                <?php if ($GLOBALS['medex_enable'] =='1') { ?>
                <a <?php
                    if ($display =='sms') {
                        echo ' class="active" ';
                    } ?>
                        href="<?php echo $GLOBALS['webroot']; ?>/interface/main/messages/messages.php?nomenu=1&go=SMS_bot&dir=back&show=new"><?php echo xlt('SMS'); ?></a>
                <?php } ?>
            </div>
        </header>

        
        <?php
    }
    
    function common_footer($display="") { ?>
        
        <div class="fbar M6hT6 As6eLe" style="position:fixed;
   left:0px;
   bottom:0px;
   ">
            <style>
                .fbar {
                    background: #f2f2f2;
                    line-height: 20px;
                    padding: 0px 20px;
                    text-align: center;
                    margin-left: -27px;
                    font-size: small;
                }
                .fbar a {
                    text-decoration: none;
                    white-space: nowrap;
                    color: #777;
                }

                .fbar a:hover {
                    color: #333;
                }
                
                .M6hT6 {
                    background: #f2f2f2;
                    left: 0;
                    right: 0;
                    -webkit-text-size-adjust: none;
                }
                .M6hT6 a {
                    text-decoration: none;
                }
                
                .As6eLe {
                    padding-bottom: 5px;
                }
                
                html, body {
                    height: 100%
                }
                .JQyAhb {
                    border-top: 1px solid #e4e4e4;
                    padding-top: 10px
                }
                .Fx4vi {
                    padding-left: 27px;
                    margin: 0 !important
                }


            </style>
            <div class="JQyAhb" >
                <span style="display:inline-block;position:relative">
                    <a class="Fx4vi"
                       href="https://open-emr.org"><?php echo xlt('openEMR'); ?></a>
                    <a class="Fx4vi"
                       href="//community.open-emr.org/"><?php echo xlt('Forum'); ?></a>
                    <a class="Fx4vi"
                       href="//chat.open-emr.org/home"><?php echo xlt('Chat'); ?></a>
                    <a class="Fx4vi"
                       href="//www.open-emr.org/wiki/index.php/OpenEMR_5.0.1_Users_Guide"><?php echo xlt('User Manuals'); ?></a>
                    <a class="Fx4vi"
                       href="//www.open-emr.org/blog/"><?php echo xlt('Blog'); ?></a>
                    <a class="Fx4vi"
                       href="//en.wikipedia.org/wiki/GNU_General_Public_License"><?php echo xlt('License'); ?></a>
                    <a class="Fx4vi"
                       href="<?php echo $GLOBALS['webroot']; ?>/interface/main/tabs/main.php?desktop=1"><?php echo xlt('Desktop site'); ?></a>
                </span>
            </div>
        </div>

    <?php
    }