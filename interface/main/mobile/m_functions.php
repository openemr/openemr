<?php
    /**
     * interface/main/mobile/m_functions.php
     *
     * Common mobile items.
     *
     * Copyright (C) 2018 Raymond Magauran <magauran@MedExBank.com>
     *
     * @package OpenEMR
     * @author Ray Magauran <magauran@MedExBank.com>
     * @link http://www.open-emr.org
     * @copyright Copyright (c) 2018 MedEx <magauran@MedExBank.com>
     * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
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
            margin: 1vh;
            max-height: 8vh;
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
            overflow-x: hidden;
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
            padding: 5px;
            cursor: pointer;
            border-radius: 5px;
            margin: 8px auto auto;
            text-align: center;
            background-color: #2d98cf66;
            box-shadow: 1px 1px 3px #c0c0c0;
        }
        .fa {
            padding-right:2px;
        }
        #preview {
            text-align: center;
            overflow: auto;
            height: calc(100vh - 290px);
            min-height: 70vh;
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
        .card {
            min-height: 170px;
        }
        .jumbotronA {
            margin: 8px 20px;
            min-height: 70vh;
        }
        td {
            text-align: center;
        }

        .message {
            text-align: center;
            font-size: 0.9em;
            height: calc(100vh - 240px);
            left: 10px;
            right: 10px;
            border: 8px solid #2d98cf66;
            display: list-item;
            padding: 8px 8px 40px;
            cursor: pointer;
            border-radius: 5px;
            margin: 8px auto;
            background-color: #fff;
            box-shadow: 1px 1px 3px #c0c0c0;
            overflow-x: hidden;
        }

        @media (min-width:1200px){
            .auto-clear .col-lg-1:nth-child(12n+1){clear:left;}
            .auto-clear .col-lg-2:nth-child(6n+1){clear:left;}
            .auto-clear .col-lg-3:nth-child(4n+1){clear:left;}
            .auto-clear .col-lg-4:nth-child(3n+1){clear:left;}
            .auto-clear .col-lg-6:nth-child(odd){clear:left;}
            .long {max-width: 100%; height: calc(100vh - 280px);}
        }
        @media (min-width:992px) and (max-width:1199px){
            .auto-clear .col-md-1:nth-child(12n+1){clear:left;}
            .auto-clear .col-md-2:nth-child(6n+1){clear:left;}
            .auto-clear .col-md-3:nth-child(4n+1){clear:left;}
            .auto-clear .col-md-4:nth-child(3n+1){clear:left;}
            .auto-clear .col-md-6:nth-child(odd){clear:left;}
            .long {max-width: 100%; height: calc(100vh - 280px);}
        }
        @media (min-width:768px) and (max-width:991px){
            .auto-clear .col-sm-1:nth-child(12n+1){clear:left;}
            .auto-clear .col-sm-2:nth-child(6n+1){clear:left;}
            .auto-clear .col-sm-3:nth-child(4n+1){clear:left;}
            .auto-clear .col-sm-4:nth-child(3n+1){clear:left;}
            .auto-clear .col-sm-6:nth-child(odd){clear:left;}
            .long {max-width: 100%; height: calc(100vh - 240px);}
        }
        @media (max-width:767px) {
            .auto-clear .col-xs-1:nth-child(12n+1) {clear: left;}
            .auto-clear .col-xs-2:nth-child(6n+1) {clear: left;}
            .auto-clear .col-xs-3:nth-child(4n+1) {clear: left;}
            .auto-clear .col-xs-4:nth-child(3n+1) {clear: left;}
            .auto-clear .col-xs-6:nth-child(odd) {clear: left;}
            .jumbotronA {margin: 8px auto 50px;}
            .long { height: calc(100vh - 290px);min-height:70vh;}
            #head_img {margin: 2vH 0 0 0;max-height: 15vH;}
        }

        @media (max-width:400px){
            .auto-clear .col-xs-1:nth-child(12n+1){clear:left;}
            .auto-clear .col-xs-2:nth-child(6n+1){clear:left;}
            .auto-clear .col-xs-3:nth-child(4n+1){clear:left;}
            .auto-clear .col-xs-4:nth-child(3n+1){clear:left;}
            .auto-clear .col-xs-6:nth-child(odd){clear:left;}
            .jumbotronA {margin: 8px auto 50px;}
            .long {height: calc(100vh - 180px);}
            #preview {height: calc(100vh - 90px);}
            #search_data_right {height: calc(100vh - 170px); min-height: 70vH; }
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

    <style type="text/css">
        .incoming { margin-top: 10px; margin-bottom: 10px; clear: both; }
        .outgoing { margin-top: 10px; margin-bottom: 10px; clear: both; float:right; }
        .precell { border: 1px solid white; max-width: 450px; }
        .cell { border: 1px solid red; }
        .textcell { max-width: 450px; }

        .blue_box, .green_box {
            display: block;
            clear:both;
        }

        .green_box span {
            background-color: green;
            color: white;
            padding: 10px 5px;
            display: block;
            float: left;
            text-align:left;
        }
        #first_name {
            font-size: 12px;
            float: right;
        }
        .space {
            margin-bottom: 10px;
            clear: both;
        }

        .padding_Info_text {
            padding-top : 50px;
            padding-bottom : 10px;
        }


        .blue_box, .green_box, .gray_box, .lightblue_box, .button_div{
            display: block;
            clear:both;
            max-width: 86%;
            border-radius: 4px;
            padding: 5px 10px;
            float: left;
            text-align:left;
        }
        .lightblue_box {
            background-color:#a6bfd5;
            color: #fff;

        }
        .blue_box span {
            background-color: #337ab7;
            color: white;
        }
        .gray_box span {
            background-color: #E0E4E0;
        }
        .green_box span {
            background-color: green;
            color: #fff;
        }
        .yellow_box span {
            background-color: yellow;
            color: #000;
        }

        .arrow_right  {
            position: relative;
            background: #337ab7;
            border: 1px solid #c2e1f5;
            border-radius: 10px;
            display: block;
            float: right;
            clear:both;
        }
        .Content {
            position:absolute;
            left:5px;
            top:5px;
            right:5px;
        }
        .arrow_right:after, .arrow_right:before {
            left: 100%;
            top: 50%;
            border: solid transparent;
            content: " ";
            height: 0;
            width: 0;
            position: absolute;
            pointer-events: none;
        }

        .arrow_right:after {
            border-color: rgba(30, 5, 252, 0);
            border-left-color: #337ab7;;
            border-width: 5px;
            margin-top: -5px;
        }

        .arrow_left {
            position: relative;
            background: #E0E4E0;
        }
        .arrow_left:after, .arrow_left:before {
            right: 99%;
            top: -5px;
            border: solid transparent;
            content: " ";
            height: 0;
            width: 0;
            position: absolute;
            pointer-events: none;
        }

        .arrow_left:after {
            border-color: rgba(136, 183, 213, 0);
            border-right-color: #E0E4E0;
            border-width: 5px;
            margin-top: 13px;
        }
        .date_right {
            font-size:0.8em;float:right;margin-top:4px;clear: both;
        }
        .date_left {
            font-size:0.8em;float:left;margin-top:4px;clear: both;
        }
        #message_data_right, #search_data_right{
            background-color: #fffef1;
            padding: 10px 20px 60px;
            overflow: auto;
            top: 45px;
            position: absolute;
            right: 5px;
            left: 5px;
            bottom: 41px;
        }
        
        #Content
        {
            margin: 0px auto;
            padding: 2px 10px 3px;
            background-color: #ECECEC;;
            Xborder-top-left-radius: 5px;
            Xborder-top-right-radius: 5px;
            border-bottom: 2pt solid #eae3ce;
            
        }
        .line_bottom_style {
            position: absolute;
            font-size: 0.9em;
            bottom: 5px;
            left: 5px;
            right:5px;
            height: 36px;
            color: black;
            background-color: #ECECEC;
            border-radius: 0;
            border-top: 2pt solid #eae3ce;
        }
        .logo {
            height:50px;
            vertical-align:bottom;
            max-width:200px;}
        
        #documents_list {
            width:100%;
        }
        .line_2_style {
            min-height:17px;
        }

        body { margin: 0px 0px; padding: 0px; text-align:center; background-color:transparent !important;}
        #first_name {
            font-size: 12px;
            float: right;
        }
        .space {
            margin-bottom: 10px;
            clear: both;
        }

        .padding_Info_text {
            padding-top : 50px;
            padding-bottom : 10px;
        }

        .arrow_right  {
            position: relative;
            border: 1px solid #c2e1f5;
            border-radius: 10px;
            display: block;
            float: right;
            clear:both;
        }
        .arrow_right:after, .arrow_right:before {
            left: 100%;
            top: 50%;
            border: solid transparent;
            content: " ";
            height: 0;
            width: 0;
            position: absolute;
            pointer-events: none;
        }

        .arrow_right:after {
            border-color: rgba(30, 5, 252, 0);
            border-left-color: #337ab7;;
            border-width: 5px;
            margin-top: -5px;
        }

        .arrow_left {
            position: relative;
            background: #E0E4E0;
        }
        .arrow_left:after, .arrow_left:before {
            right: 99%;
            top: -5px;
            border: solid transparent;
            content: " ";
            height: 0;
            width: 0;
            position: absolute;
            pointer-events: none;
        }

        .arrow_left:after {
            border-color: rgba(136, 183, 213, 0);
            border-right-color: #E0E4E0;
            border-width: 5px;
            margin-top: 13px;
        }
        .dialogIframe {
            border:none !important;
            background-color: transparent;
        }
        @keyframes blink {
            from, to { opacity: 1 }
            90% { opacity: 0.2 }
        }

        .shrinkToFit {
             height: 90%;
        }
        
        .pname {
            position:relative;
            margin: 0px auto;
            font-size:0.9em;
            top:3px;
            font-weight: bold;
        }

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
                        href="<?php echo $GLOBALS['webroot']; ?>/interface/main/mobile/camera.php?v=<?php echo $GLOBALS['v_js_includes']; ?>"><?php echo xlt('Imaging'); ?></a>
                <a <?php
                if ($display == 'cal') {
                    echo ' class="active" ';
                } ?>
                        href="<?php echo $GLOBALS['webroot']; ?>/interface/main/mobile/m_cal.php?v=<?php echo $GLOBALS['v_js_includes']; ?>"><?php echo xlt('Calendar'); ?></a>
                 <a <?php
                    if ($display =='flow') {
                        echo ' class="active" ';
                    } ?>
                        href="<?php echo $GLOBALS['webroot']; ?>/interface/main/mobile/m_flow.php?v=<?php echo $GLOBALS['v_js_includes']; ?>"><?php echo xlt('Flow'); ?></a>
                <?php
                    if ($GLOBALS['medex_enable'] =='1') { ?>
                    
                        <a <?php
                            if ($display =='sms') {
                                echo ' class="active" ';
                            } ?>
                                href="<?php echo $GLOBALS['webroot']; ?>/interface/main/mobile/SMS.php?v=<?php echo $GLOBALS['v_js_includes']; ?>&dir=back&show=new"><?php echo xlt('SMS'); ?></a>
                    <?php
                    }
                    ?>
            </div>
        </header>

        
        <?php
}
    
function common_footer($display = "")
{
    $versionService = new VersionService();
    $version = $versionService->fetch();
    ?>
    <br />
    <footer>
        <div class="fbar M6hT6 As6eLe">
            <div class="JQyAhb" >
                <span style="display:inline-block;position:relative; text-align:center;">
                    <a class="Fx4vi"
                       href="https://open-emr.org">OpenEMR</a>
                    <a class="Fx4vi"
                       href="https://community.open-emr.org/"><?php echo xlt('Forum'); ?></a>
                    <a class="Fx4vi"
                       href="https://chat.open-emr.org/home"><?php echo xlt('Chat'); ?></a>
                    <a class="Fx4vi"
                       href="https://www.open-emr.org/wiki/index.php/OpenEMR_<?php echo attr($version->getMajor()).".".attr($version->getMinor()).".".attr($version->getPatch()); ?>_Users_Guide"><?php echo xlt('User Manuals'); ?></a>
                    <a class="Fx4vi"
                       href="https://www.open-emr.org/blog/"><?php echo xlt('Blog'); ?></a>
                    <a class="Fx4vi"
                       href="../../../acknowledge_license_cert.html"><?php echo xlt('License'); ?></a>
                    <a class="Fx4vi"
                       href="camera.php?desktop=1"><?php echo xlt('Desktop site'); ?></a>
                </span>
            </div>
        </div>
    </footer>

    <?php
}

function common_js()
{
    
    ?>
    
    function search4Docs() {
        top.restoreSession();
        $.ajax({
            type: "POST",
            url: "m_save.php",
            data: {
                go          : 'search_Docs',
                pid         : $("#pid").val(),
                category    : $("#category").val()
            }
        }).done(function(result) {
            $('#preview').html(result).show();
            $("#search_data_right").hide();
        });
    }
    
    function search4SMS() {
        var name = $("#outpatient").val();
        if (name.length < 3) {
            //turn button red for 3 seconds
            setInterval(function() {
                $('#sms_search2').animate( { backgroundColor: 'red' }, 1000)
                .animate( { backgroundColor: 'green' }, 1000);
            }, 1000);
            $('#sms_search').animate( { backgroundColor: 'red' }, 3000);
            $('#sms_search').animate( { backgroundColor: '#063f80' }, 3000);
            return;
        }
        //turn button green for 3 seconds
        setInterval(function() {
        $('#sms_search2').animate( { backgroundColor: 'red' }, 1000)
        .animate( { backgroundColor: 'green' }, 1000);
        }, 1000);
        $('#sms_search').animate( { backgroundColor: 'red' }, 3000);
        $('#sms_search').animate( { backgroundColor: '#009933' }, 3000);
    
        top.restoreSession();
        $('#search_data_right').html('<div class="text-center">\n'+
        '                        <i class="fa fa-spinner fa-pulse fa-fw" style="font-size: 100px; color: #000080; padding: 20px"></i>\n'+
        '                        <h2 ><?php echo xlt("Loading data"); ?>...</h2>\n'+
        '                    </div>');
    
        $.ajax({
            type: "POST",
            url: "../messages/messages.php?nomenu=1",
            data: {
                pid         : 'find',
                action      : 'new_SMS',
                SMS_bot     : '1',
                outpatient  : $("#outpatient").val(),
                show        : 'pat_list',
                r           : '1'
            }
        }).done(function(result) {
            $('#search_data_right').html(result);
            $("#search_data_right").scrollTop(function() { return this.scrollHeight; });
        
            timing = 5000;
            refreshTable(pid,timing);
        });
    }

    function refreshTable(pid,timing='') {
        top.restoreSession();
    
        // Add 5 seconds to pause interval until 1 minute is reached,
        // then refresh q 1 minute while window is open
        if (timing < 60000) {
            timing = timing + 5000;
        }
    
        $.ajax({
            type: "POST",
            url: "m_save.php?nomenu=1",
            data: {
                r  			: '1',
                pid 		: pid,
                SMS_bot 	: '1',
                go 		    : 'SMS_refresh',
                device      : 'mobile',
                dfrom       : $("#msg_last_updated").val()
            }
        }).done(function(result){
            if (result) {
                results = JSON.parse(result);
                if (results.msg_last_updated >'') {
                    $('#msg_last_updated').val(results.msg_last_updated);
                    $('#message_data_right').append(results.msg_content);
                    $("#message_data_right").scrollTop(function() { return this.scrollHeight; });
                    timing = 5000;
                }
            }
            setTimeout(function(){
                refreshTable(pid,timing);
                }, timing);
        });
    }
    <?php
}



