<?php
    /**
     * interface/main/mobile/m_functions.php
     *
     * Common mobile items.
     *
     * Copyright (C) 2018 Raymond Magauran <magauran@MedExBank.com>
     *
     * LICENSE: This program is free software: you can redistribute it and/or modify
     *  it under the terms of the GNU Affero General Public License as
     *  published by the Free Software Foundation, either version 3 of the
     *  License, or (at your option) any later version.
     *
     *  This program is distributed in the hope that it will be useful,
     *  but WITHOUT ANY WARRANTY; without even the implied warranty of
     *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     *  GNU Affero General Public License for more details.
     *
     *  You should have received a copy of the GNU Affero General Public License
     *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
     *
     * @package OpenEMR
     * @author Ray Magauran <magauran@MedExBank.com>
     * @link http://www.open-emr.org
     * @copyright Copyright (c) 2018 MedEx <magauran@MedExBank.com>
     * @license https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
     */
    use OpenEMR\Core\Header;
    use OpenEMR\Services\VersionService;
    
    function common_head()
{
    ?>
        
    <head>
            
        <?php Header::setupHeader([ 'jquery-ui', 'jquery-ui-cupertino' ]); ?>
    
        <title><?php echo "OpenEMR ". xlt('Mobile'); ?></title>
        <meta content="width=device-width,initial-scale=1.0" name="viewport">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="description" content="OpenEMR: Mobile">
        <meta name="author" content="OpenEMR: Mobile Group">
        <?php common_style(); ?>
        <script type="text/javascript">
            <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
        </script>
      </head>
    <?php
}
    
function common_style()
{
    ?>
    <style>
        #head_img {
            margin: 2vh;
            max-height: 15vh;
        }
        
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
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: vertical;
            flex-direction: column;
            padding-right:5%;
            padding-left:5%;
        }
            
        header {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            height: 48px;
            width: 100%
        }

        #menu_top {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            padding-left: 10px;
        }

        #menu_top a {
            color: #757575;
            display: block;
            -webkit-box-flex: 0;
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

        #autocomplete {
            background: rgba(0, 0, 0, 0) none repeat scroll 0 0;
            border: 1px solid rgba(0, 0, 0, 0.25);
            border-radius: 4px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.4) inset, 0 1px 0 rgba(255, 255, 255, 0.1);
            color: #fff;
            text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.796), 0 0 10px rgba(255, 255, 255, 0.298);
            padding: 10px;
            margin: 10px 0;
        }
        input[type="file"] {
            display: none;
        }
        .custom-file-upload {
            border: 1px solid #ccc;
            display: inline-block;
            padding: 12px 12px;
            cursor: pointer;
            border-radius: 5px;
            margin: 8px auto;
            text-align: center;
            background-color: #2d98cf66;
            box-shadow: 1px 1px 3px #c0c0c0;
        }
        .fa {
            padding-right:2px;
        }
        #preview {
            text-align: center;
        }
        #preview  img {
            vertical-align: top;
            width: 85%;
            margin: 0px auto;
        }
        obj, audio, canvas, progress, video {
            margin:2%;
            max-width: 8em;
            vertical-align: top;
            text-align: center;
        }
        label {
            margin:5px;
            padding:5px 20px;
            box-shadow: 1px 1px 2px #938282;
        }
        label input {
            padding:left:30px;
        }
        .byCatDisplay {
            display:none;
        }
        .btn {
            font-size: 1.5rem;
        }
        .card-title {
            overflow:hidden;
        }
        .jumbotronA {
            min-height:400px;
            margin:8px;
            margin-bottom: 40px;
        }
        td {
            text-align: center;
        }
        @media (min-width:1200px){
            .auto-clear .col-lg-1:nth-child(12n+1){clear:left;}
            .auto-clear .col-lg-2:nth-child(6n+1){clear:left;}
            .auto-clear .col-lg-3:nth-child(4n+1){clear:left;}
            .auto-clear .col-lg-4:nth-child(3n+1){clear:left;}
            .auto-clear .col-lg-6:nth-child(odd){clear:left;}
        }
        @media (min-width:992px) and (max-width:1199px){
            .auto-clear .col-md-1:nth-child(12n+1){clear:left;}
            .auto-clear .col-md-2:nth-child(6n+1){clear:left;}
            .auto-clear .col-md-3:nth-child(4n+1){clear:left;}
            .auto-clear .col-md-4:nth-child(3n+1){clear:left;}
            .auto-clear .col-md-6:nth-child(odd){clear:left;}
        }
        @media (min-width:768px) and (max-width:991px){
            .auto-clear .col-sm-1:nth-child(12n+1){clear:left;}
            .auto-clear .col-sm-2:nth-child(6n+1){clear:left;}
            .auto-clear .col-sm-3:nth-child(4n+1){clear:left;}
            .auto-clear .col-sm-4:nth-child(3n+1){clear:left;}
            .auto-clear .col-sm-6:nth-child(odd){clear:left;}
        }
        @media (max-width:767px) {
            .auto-clear .col-xs-1:nth-child(12n+1) {clear: left;}
            .auto-clear .col-xs-2:nth-child(6n+1) {clear: left;}
            .auto-clear .col-xs-3:nth-child(4n+1) {clear: left;}
            .auto-clear .col-xs-4:nth-child(3n+1) {clear: left;}
            .auto-clear .col-xs-6:nth-child(odd) {clear: left;}
            .jumbotronA {display:none;margin: 8px auto;}
            #head_img {margin: 2vH 0 0 0;max-height: 15vH;}
        }

        @media (max-width:400px){
            .auto-clear .col-xs-1:nth-child(12n+1){clear:left;}
            .auto-clear .col-xs-2:nth-child(6n+1){clear:left;}
            .auto-clear .col-xs-3:nth-child(4n+1){clear:left;}
            .auto-clear .col-xs-4:nth-child(3n+1){clear:left;}
            .auto-clear .col-xs-6:nth-child(odd){clear:left;}
            .jumbotronA {display:none;margin: 8px auto;}
            #head_img {margin: 2vH 0 0 0;max-height: 10vH;}
        }
        .section_title {font-size:1.2em;text-decoration:underline;font-weight:600;margin-bottom:8px;}
       
          .fbar {
              background: #f2f2f2;
              line-height: 20px;
              text-align: center;
              margin-left: -27px;
              font-size: small;
              position:fixed;
              left:0px;
              bottom:0px;
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
        .white{
            background-color: #FFF;
        }
        .prov_line {
            border:1.5pt groove #c0c0c0;
        }
        .cal_cat {
            border:1.5pt inset #c0c0c0;
        }
        .visit {
            border-bottom:1.0pt solid #c0c0c0;
        }
        //end footer
    </style>
    
    <?php
}
    
    /**
     * Common header for mobile pages.
     * This was build from Google's mobile page.
     * If the goal is pure bootstrap, this would be the place to start it off.
     */
function common_header($display = '')
{
    ?>
        <header>
            <div id="menu_top">
                <a <?php
                if ($display =='photo') {
                    echo ' class="active" ';
                } ?>
                        href="<?php echo $GLOBALS['webroot']; ?>/interface/main/mobile/camera.php?v=<?php echo mt_rand(); ?>"><?php echo xlt('Upload'); ?></a>
                <a <?php
                if ($display == 'cal') {
                    echo ' class="active" ';
                } ?>
                        href="<?php echo $GLOBALS['webroot']; ?>/interface/main/mobile/m_cal.php?v=<?php echo mt_rand(); ?>"><?php echo xlt('Calendar'); ?></a>
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
    
function common_footer($display = "")
{
    $versionService = new VersionService();
    $version = $versionService->fetch();
    ?>
        <div class="fbar M6hT6 As6eLe">
            <div class="JQyAhb" >
                <span style="display:inline-block;position:relative; text-align:center;">
                    <a class="Fx4vi"
                       href="https://open-emr.org">OpenEMR</a>
                    <a class="Fx4vi"
                       href="//community.open-emr.org/"><?php echo xlt('Forum'); ?></a>
                    <a class="Fx4vi"
                       href="//chat.open-emr.org/home"><?php echo xlt('Chat'); ?></a>
                    <a class="Fx4vi"
                       href="//www.open-emr.org/wiki/index.php/OpenEMR_<?php echo attr($version->getMajor()).".".attr($version->getMinor()).".".attr($version->getPatch()); ?>_Users_Guide"><?php echo xlt('User Manuals'); ?></a>
                    <a class="Fx4vi"
                       href="//www.open-emr.org/blog/"><?php echo xlt('Blog'); ?></a>
                    <a class="Fx4vi"
                       href="../../../acknowledge_license_cert.html"><?php echo xlt('License'); ?></a>
                    <a class="Fx4vi"
                       href="main.php?desktop=1"><?php echo xlt('Desktop site'); ?></a>
                </span>
            </div>
        </div>

    <?php
}