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
            .z1asCe svg, .qa__svg-icon svg {
                display: block;
                height: 100%;
                width: 100%
            }

            .no_outline a, .no_outline div {
                outline: none;
                -webkit-tap-highlight-color: rgba(0, 0, 0, 0)
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

            #ddlxsharemenu {
                background-color: #fff;
                border-radius: 2px;
                box-shadow: 0 2px 1px rgba(0, 0, 0, 0.1), 0 0 1px rgba(0, 0, 0, 0.1);
                color: rgba(0, 0, 0, 0.87);
                font-size: 16px;
                opacity: 0;
                position: absolute;
                right: 8px;
                text-align: left;
                top: 8px;
                transform: translate3d(0px, -200px, 0px);
                -webkit-transform: translate3d(0px, -200px, 0px);
                width: 200px;
                z-index: 105
            }

            #ddlxsharemenu.ddlx-expanded-share {
                opacity: 1;
                -webkit-transform: translate3d(0px, 0px, 0px);
                transform: translate3d(0px, 0px, 0px)
            }

            .Uadjd {
                background-size: 72px 24px;
                border-radius: 3px;
                bottom: 2px;
                display: inline-block;
                height: 24px;
                position: relative;
                margin: 12px 16px;
                vertical-align: middle;
                width: 24px
            }

            .wXlXob {
                background-color: #dd4b39
            }

            .PTpbI {
                background-color: #3b579d;
                background-position: -24px 0
            }

            .eZZiz {
                background-color: #55acee;
                background-position: -48px 0
            }
        </style>
        <style>.gb_8 .gb_b {
                background-position: -64px -29px;
                opacity: .55
            }

            .gb_9 .gb_8 .gb_b {
                background-position: -64px -29px
            }

            .gb_S .gb_8 .gb_b {
                background-position: -29px -29px;
                opacity: 1
            }

            @-webkit-keyframes gb__a {
                0% {
                    opacity: 0
                }
                50% {
                    opacity: 1
                }
            }

            @keyframes gb__a {
                0% {
                    opacity: 0
                }
                50% {
                    opacity: 1
                }
            }

            a.gb_wa {
                border: none;
                color: #4285f4;
                cursor: default;
                font-weight: bold;
                outline: none;
                position: relative;
                text-align: center;
                text-decoration: none;
                text-transform: uppercase;
                white-space: nowrap;
                -webkit-user-select: none
            }

            a.gb_wa:hover:after, a.gb_wa:focus:after {
                background-color: rgba(0, 0, 0, .12);
                content: '';
                height: 100%;
                left: 0;
                position: absolute;
                top: 0;
                width: 100%
            }

            a.gb_wa:hover, a.gb_wa:focus {
                text-decoration: none
            }

            a.gb_wa:active {
                background-color: rgba(153, 153, 153, .4);
                text-decoration: none
            }

            a.gb_xa {
                background-color: #4285f4;
                color: #fff
            }

            a.gb_xa:active {
                background-color: #0043b2
            }

            .gb_ya {
                -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, .16);
                box-shadow: 0 1px 1px rgba(0, 0, 0, .16)
            }

            .gb_wa, .gb_xa, .gb_za, .gb_Aa {
                display: inline-block;
                line-height: 28px;
                padding: 0 12px;
                -webkit-border-radius: 2px;
                border-radius: 2px
            }

            .gb_za {
                background: #f8f8f8;
                border: 1px solid #c6c6c6
            }

            .gb_Aa {
                background: #f8f8f8
            }

            .gb_za, #gb a.gb_za.gb_za, .gb_Aa {
                color: #666;
                cursor: default;
                text-decoration: none
            }

            #gb a.gb_Aa.gb_Aa {
                cursor: default;
                text-decoration: none
            }

            .gb_Aa {
                border: 1px solid #4285f4;
                font-weight: bold;
                outline: none;
                background: #4285f4;
                background: -webkit-linear-gradient(top, #4387fd, #4683ea);
                background: linear-gradient(top, #4387fd, #4683ea);
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#4387fd, endColorstr=#4683ea, GradientType=0)
            }

            #gb a.gb_Aa.gb_Aa {
                color: #fff
            }

            .gb_Aa:hover {
                -webkit-box-shadow: 0 1px 0 rgba(0, 0, 0, .15);
                box-shadow: 0 1px 0 rgba(0, 0, 0, .15)
            }

            .gb_Aa:active {
                -webkit-box-shadow: inset 0 2px 0 rgba(0, 0, 0, .15);
                box-shadow: inset 0 2px 0 rgba(0, 0, 0, .15);
                background: #3c78dc;
                background: -webkit-linear-gradient(top, #3c7ae4, #3f76d3);
                background: linear-gradient(top, #3c7ae4, #3f76d3);
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#3c7ae4, endColorstr=#3f76d3, GradientType=0)
            }

            .gb_7a {
                display: none !important
            }

            .gb_8a {
                visibility: hidden
            }

            .gb_9a {
                -webkit-background-size: 32px 32px;
                background-size: 32px 32px;
                -webkit-border-radius: 50%;
                border-radius: 50%;
                display: block;
                margin: -1px;
                overflow: hidden;
                position: relative;
                height: 32px;
                width: 32px;
                z-index: 0
            }

            @media (min-resolution: 1.25dppx),(-o-min-device-pixel-ratio: 5/4),(-webkit-min-device-pixel-ratio: 1.25),(min-device-pixel-ratio: 1.25) {
                .gb_9a::before {
                    display: inline-block;
                    -webkit-transform: scale(.5);
                    transform: scale(.5);
                    -webkit-transform-origin: left 0;
                    transform-origin: left 0
                }

                .gb_Bb::before {
                    display: inline-block;
                    -webkit-transform: scale(.5);
                    transform: scale(.5);
                    -webkit-transform-origin: left 0;
                    transform-origin: left 0
                }
            }

            .gb_9a:hover, .gb_9a:focus {
                -webkit-box-shadow: 0 1px 0 rgba(0, 0, 0, .15);
                box-shadow: 0 1px 0 rgba(0, 0, 0, .15)
            }

            .gb_9a:active {
                -webkit-box-shadow: inset 0 2px 0 rgba(0, 0, 0, .15);
                box-shadow: inset 0 2px 0 rgba(0, 0, 0, .15)
            }

            .gb_9a:active::after {
                background: rgba(0, 0, 0, .1);
                -webkit-border-radius: 50%;
                border-radius: 50%;
                content: '';
                display: block;
                height: 100%
            }

            .gb_ab {
                cursor: pointer;
                line-height: 30px;
                min-width: 30px;
                opacity: .75;
                overflow: hidden;
                vertical-align: middle;
                text-overflow: ellipsis
            }

            .gb_b.gb_ab {
                width: auto
            }

            .gb_ab:hover, .gb_ab:focus {
                opacity: .85
            }

            .gb_bb .gb_ab, .gb_bb .gb_cb {
                line-height: 26px
            }

            #gb#gb.gb_bb a.gb_ab, .gb_bb .gb_cb {
                font-size: 11px;
                height: auto
            }

            .gb_db {
                border-top: 4px solid #000;
                border-left: 4px dashed transparent;
                border-right: 4px dashed transparent;
                display: inline-block;
                margin-left: 6px;
                opacity: .75;
                vertical-align: middle
            }

            .gb_eb:hover .gb_db {
                opacity: .85
            }

            .gb_Ra > .gb_fb {
                padding: 3px 3px 3px 4px
            }

            .gb_S .gb_ab, .gb_S .gb_db {
                opacity: 1
            }

            #gb#gb.gb_S.gb_S a.gb_ab, #gb#gb .gb_S.gb_S a.gb_ab {
                color: #fff
            }

            .gb_S.gb_S .gb_db {
                border-top-color: #fff;
                opacity: 1
            }

            .gb_9 .gb_9a:hover, .gb_S .gb_9a:hover, .gb_9 .gb_9a:focus, .gb_S .gb_9a:focus {
                -webkit-box-shadow: 0 1px 0 rgba(0, 0, 0, .15), 0 1px 2px rgba(0, 0, 0, .2);
                box-shadow: 0 1px 0 rgba(0, 0, 0, .15), 0 1px 2px rgba(0, 0, 0, .2)
            }

            .gb_gb .gb_fb, .gb_hb .gb_fb {
                position: absolute;
                right: 1px
            }

            .gb_fb.gb_R, .gb_ib.gb_R, .gb_eb.gb_R {
                -webkit-flex: 0 1 auto;
                flex: 0 1 auto;
                -webkit-flex: 0 1 main-size;
                flex: 0 1 main-size
            }

            .gb_jb.gb_kb .gb_ab {
                width: 30px !important
            }

            .gb_lb.gb_8a {
                display: none
            }

            @-webkit-keyframes progressmove {
                0% {
                    margin-left: -100%
                }
                to {
                    margin-left: 100%
                }
            }

            @keyframes progressmove {
                0% {
                    margin-left: -100%
                }
                to {
                    margin-left: 100%
                }
            }

            .gb_mb.gb_7a {
                display: none
            }

            .gb_mb {
                background-color: #ccc;
                height: 3px;
                overflow: hidden
            }

            .gb_nb {
                background-color: #f4b400;
                height: 100%;
                width: 50%;
                -webkit-animation: progressmove 1.5s linear 0s infinite;
                animation: progressmove 1.5s linear 0s infinite
            }

            .gb_pb {
                height: 40px;
                position: absolute;
                right: -5px;
                top: -5px;
                width: 40px
            }

            .gb_qb .gb_pb, .gb_rb .gb_pb {
                right: 0;
                top: 0
            }

            .gb_b {
                margin: 5px
            }

            .gb_8c {
                display: inline-block;
                padding: 4px 4px 4px 4px;
                vertical-align: middle
            }

            .gb_8c:first-child, #gbsfw:first-child + .gb_8c {
                padding-left: 0
            }

            .gb_Pc {
                position: relative
            }

            .gb_b {
                display: inline-block;
                outline: none;
                vertical-align: middle;
                -webkit-border-radius: 2px;
                border-radius: 2px;
                -webkit-box-sizing: border-box;
                box-sizing: border-box;
                height: 30px;
                width: 30px;
                color: #000;
                cursor: pointer;
                text-decoration: none
            }

            #gb#gb a.gb_b {
                color: #000;
                cursor: pointer;
                text-decoration: none
            }

            .gb_sb {
                border-color: transparent;
                border-bottom-color: #fff;
                border-style: dashed dashed solid;
                border-width: 0 8.5px 8.5px;
                display: none;
                position: absolute;
                left: 11.5px;
                top: 38px;
                height: 0;
                width: 0;
                -webkit-animation: gb__a .2s;
                animation: gb__a .2s
            }

            .gb_tb {
                border-color: transparent;
                border-style: dashed dashed solid;
                border-width: 0 8.5px 8.5px;
                display: none;
                position: absolute;
                left: 11.5px;
                z-index: 1;
                height: 0;
                width: 0;
                -webkit-animation: gb__a .2s;
                animation: gb__a .2s;
                border-bottom-color: #ccc;
                border-bottom-color: rgba(0, 0, 0, .2);
                top: 37px
            }

            x:-o-prefocus, div.gb_tb {
                border-bottom-color: #ccc
            }

            .gb_aa {
                background: #fff;
                border: 1px solid #ccc;
                border-color: rgba(0, 0, 0, .2);
                color: #000;
                -webkit-box-shadow: 0 2px 10px rgba(0, 0, 0, .2);
                box-shadow: 0 2px 10px rgba(0, 0, 0, .2);
                display: none;
                outline: none;
                overflow: hidden;
                position: absolute;
                right: 0;
                top: 49px;
                -webkit-animation: gb__a .2s;
                animation: gb__a .2s;
                -webkit-border-radius: 2px;
                border-radius: 2px;
                -webkit-user-select: text
            }

            .gb_8c.gb_g .gb_sb, .gb_8c.gb_g .gb_tb, .gb_8c.gb_g .gb_aa, .gb_g.gb_aa {
                display: block
            }

            .gb_8c.gb_g.gb_Ff .gb_sb, .gb_8c.gb_g.gb_Ff .gb_tb {
                display: none
            }

            .gb_Hf {
                position: absolute;
                right: 0;
                top: 49px;
                z-index: -1
            }

            .gb_bb .gb_sb, .gb_bb .gb_tb, .gb_bb .gb_aa {
                margin-top: -10px
            }

            .gb_Ta {
                display: table;
                font: 13px/27px Arial, sans-serif;
                padding: 2px
            }

            .gb_Bc {
                display: table-cell;
                vertical-align: middle;
                white-space: nowrap;
                -webkit-user-select: none
            }

            .gb_Cc {
                float: right;
                height: 48px;
                line-height: normal;
                padding: 0 4px;
                z-index: 1
            }

            .gb_Cc > .gb_Dc {
                display: table-cell;
                vertical-align: middle
            }

            .gb_Ec {
                position: relative;
                line-height: normal
            }

            .gb_Mc .gb_Pc {
                font-size: 14px;
                font-weight: bold;
                top: 0;
                right: 0
            }

            .gb_Mc .gb_b {
                display: inline-block;
                vertical-align: middle;
                -webkit-box-sizing: border-box;
                box-sizing: border-box;
                height: 30px;
                width: 30px
            }

            .gb_Mc .gb_sb {
                border-bottom-color: #e5e5e5
            }

            .gb_Qc {
                background-color: rgba(0, 0, 0, .55);
                color: #fff;
                font-size: 12px;
                font-weight: bold;
                line-height: 20px;
                margin: 5px;
                padding: 0 2px;
                text-align: center;
                -webkit-box-sizing: border-box;
                box-sizing: border-box;
                -webkit-border-radius: 50%;
                border-radius: 50%;
                height: 20px;
                width: 20px
            }

            .gb_Qc.gb_Rc {
                background-position: -79px 0
            }

            .gb_Qc.gb_Sc {
                background-position: -79px -64px
            }

            .gb_b:hover .gb_Qc, .gb_b:focus .gb_Qc {
                background-color: rgba(0, 0, 0, .85)
            }

            #gbsfw.gb_Tc {
                background: #e5e5e5;
                border-color: #ccc
            }

            .gb_9 .gb_Qc {
                background-color: rgba(0, 0, 0, .7)
            }

            .gb_S .gb_Qc.gb_Qc, .gb_S .gb_Jc .gb_Qc.gb_Qc, .gb_S .gb_Jc .gb_b:hover .gb_Qc, .gb_S .gb_Jc .gb_b:focus .gb_Qc {
                background-color: #fff;
                color: #404040
            }

            .gb_S .gb_Qc.gb_Rc {
                background-position: -54px -64px
            }

            .gb_S .gb_Qc.gb_Sc {
                background-position: 0 -64px
            }

            .gb_Jc .gb_Qc.gb_Qc {
                background-color: #db4437;
                color: #fff
            }

            .gb_Jc .gb_b:hover .gb_Qc, .gb_Jc .gb_b:focus .gb_Qc {
                background-color: #a52714
            }


        </style>
        <style id="gstyle">body {
                font-family: Roboto, HelveticaNeue, Arial, sans-serif;
                font-size: 10pt;
                margin: 0;
            }

            #belowsb {
                min-height: 25px;
                margin-top: 24px;
            }

            #belowsb a {
                color: #1967D2
            }

            #swml, #belowsb {
                text-align: center
            }

            #swml {
                height: 25px;
                margin: 5px 0
            }

            #swml > div {
                margin: 1px 0
            }

            #og_z {
                display: none
            }

            .fade #n0tgWb {
                filter: alpha(opacity=33.3);
                opacity: 0.333
            }

            .pp-new-mobile {
                color: red
            }

            #gb-main {
                display: -webkit-box;
                display: flex;
                display: -ms-flexbox;
                display: flex;
                -webkit-box-orient: vertical;
                flex-direction: column;
                flex-direction: column;
                margin-right:5%;
                margin-left:5%;
            }
            #gb-mains {
                min-height: 100%;
                margin-right:5%;
                margin-left:5%;
            }

            #n0tgWb {
                -webkit-box-flex: 1;
                flex: 1;
                flex: 1
            }

            @media only screen and (orientation: portrait) {
                #n0tgWb {
                    padding-top: 20px
                }
            }

            #mlogo {
                display: none
            }

            .gs_ml #lst-ib {
                white-space: normal;
                height: auto
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

            #vLkmZd {
                -webkit-box-flex: 1;
                flex: 1 0 auto;
                flex: 1 0 auto;
                min-width: 138px;
                padding: 10px 10px 0 0;
                position: relative
            }

            .fp-f {
                bottom: 0;
                height: auto;
                left: 0;
                position: fixed !important;
                right: 0;
                top: 0;
                width: auto;
                z-index: 127
            }

            .fp-h:not(.fp-nh):not(.goog-modalpopup-bg):not(.goog-modalpopup) {
                display: none !important
            }

            .fp-zh.fp-h:not(.fp-nh):not(.goog-modalpopup-bg):not(.goog-modalpopup) {
                display: block !important;
                height: 0;
                overflow: hidden;
                transform: translate3d(0, 0, 0);
                transform: translate3d(0, 0, 0)
            }

            .fp-i .fp-c {
                display: block;
                min-height: 100vh
            }

            li.fp-c {
                list-style: none
            }

            .fp-w {
                box-sizing: border-box;
                left: 0;
                margin-left: auto;
                margin-right: auto;
                max-width: 1197px;
                right: 0
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
                        href="/openemr/interface/main/mobile/camera.php?v=<?php echo mt_rand(); ?>">Upload</a>
                <a <?php
                    if ($display == 'cal') {
                        echo ' class="active" ';
                    } ?>
                        href="<?php echo $GLOBALS['webroot']; ?>/interface/main/calendar/index.php?module=PostCalendar&viewtype=day&func=view">Calendar</a>
                <a <?php
                    if ($display =='flow') {
                        echo ' class="active" ';
                    } ?>
                        href="<?php echo $GLOBALS['webroot']; ?>/interface/patient_tracker/patient_tracker.php?skip_timeout_reset=1">Flow</a>
                <?php if ($GLOBALS['medex_enable'] =='1') { ?>
                <a <?php
                    if ($display =='sms') {
                        echo ' class="active" ';
                    } ?>
                        href="<?php echo $GLOBALS['webroot']; ?>/interface/main/messages/messages.php?nomenu=1&go=SMS_bot&dir=back&show=new">SMS</a>
                <?php } ?>
            </div>
        </header>

        
        <?php
    }
    
    function common_footer($display="") { ?>
        <div id="footer">
        </div><br />
        <div class="fbar M6hT6 As6eLe" style="position:fixed;
   left:0px;
   bottom:0px;
   ">
            <style>
                .fmulti {
                }

                .loc {
                }

                .swml-src {
                }

                .swml-upd {
                }

                .swml-loc {
                }

                .GNlFYb {
                    color: #777
                }

                .p2Kmnc {
                    color: #222;
                    font-size: 14px;
                    font-weight: normal;
                    -webkit-tap-highlight-color: rgba(0, 0, 0, 0)
                }

                .Seo5Sb {
                    display: inline-block;
                    opacity: 0.55;
                    vertical-align: top
                }

                a.p2Kmnc:hover .Seo5Sb, a.p2Kmnc:active .Seo5Sb {
                    opacity: 1.0
                }

                .p2Kmnc {
                    padding: 12px 18px;
                    margin-right: 18px
                }

                .TfEe9d {
                    margin-right: 18px
                }

                .UjMF2c .Seo5Sb {
                    margin-left: 4px
                }

                .fbar {
                    background: #f2f2f2;
                    line-height: 20px;
                    padding: 0px 20px;
                    text-align: center
                }

                .GNlFYb {
                    font-size: 14px;
                    margin: 33px 0 50px 0
                }

                .EvHmz {
                    bottom: 0;
                    left: 0;
                    position: absolute;
                    right: 0
                }

                .M6hT6 {
                    background: #f2f2f2;
                    left: 0;
                    right: 0;
                    -webkit-text-size-adjust: none
                }

                .hRvfYe #fsettl:hover {
                    text-decoration: underline
                }

                .hRvfYe #fsett a:hover {
                    text-decoration: underline
                }

                .hRvfYe a:hover {
                    text-decoration: underline
                }

                .fbar p {
                    display: inline
                }

                .fbar a, #fsettl {
                    text-decoration: none;
                    white-space: nowrap
                }

                .fbar {
                    margin-left: -27px
                }

                .Fx4vi {
                    padding-left: 27px;
                    margin: 0 !important
                }

                .In26Ec {
                    padding: 0 !important;
                    margin: 0 !important
                }

                #fbarcnt {
                    display: block
                }

                .smiUbb img {
                    margin-right: 4px
                }

                .smiUbb a, .M6hT6 #swml a {
                    text-decoration: none
                }

                .fmulti {
                    text-align: center
                }

                .fmulti #fsr {
                    display: block;
                    float: none
                }

                .fmulti #fuser {
                    display: block;
                    float: none
                }

                #fuserm {
                    line-height: 25px
                }

                #fsr {
                    float: right;
                    white-space: nowrap
                }

                #fsl {
                    white-space: nowrap
                }

                #fsett {
                    background: #fff;
                    border: 1px solid #999;
                    bottom: 30px;
                    padding: 10px 0;
                    position: absolute;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                    text-align: left;
                    z-index: 104
                }

                #fsett a {
                    display: block;
                    line-height: 44px;
                    padding: 0 20px;
                    text-decoration: none;
                    white-space: nowrap
                }

                .JQyAhb {
                    border-top: 1px solid #e4e4e4;
                    padding-top: 10px
                }

                .footer__homepage-mobile-settings-row-above-fold {
                    padding-bottom: 10px
                }

                .Lt2Ned {
                    margin-top: -10px
                }

                .As6eLe {
                    padding-bottom: 5px
                }

                .smiUbb {
                    font-size: 14px
                }

                .smiUbb a {
                    color: #777
                }

                .fbar p, .fbar a, #fsettl, #fsett a {
                    color: #777
                }

                .fbar a:hover, #fsett a:hover {
                    color: #333
                }

                .fbar {
                    font-size: small
                }

                #fuser {
                    float: right
                }

                .smiUbb {
                    line-height: 20px;
                    color: #777;
                    text-align: center;
                    padding: 7px 8px 8px;
                }

                html, body {
                    height: 100%
                }
            </style>
            <div class="JQyAhb" >
                <span style="display:inline-block;position:relative">
                    <a class="Fx4vi" href="https://open-emr.org" id="fsettl" aria-controls="fsett"
                       aria-expanded="false" role="button" jsaction="foot.cst">openEMR</a>
                    <a class="Fx4vi" href="//community.open-emr.org/">Forum</a>
                    <a class="Fx4vi" href="//chat.open-emr.org/home">Chat</a>
                    <a class="Fx4vi"
                       href="//www.open-emr.org/wiki/index.php/OpenEMR_5.0.1_Users_Guide">User Manuals</a>
                    <a class="Fx4vi"
                       href="//www.open-emr.org/blog/">Blog</a>
                    <a class="Fx4vi"
                       href="//en.wikipedia.org/wiki/GNU_General_Public_License">License</a>
                    <a class="Fx4vi"
                       href="<?php echo $GLOBALS['webroot']; ?>/interface/main/tabs/main.php?desktop=1">Desktop site</a>
                </span>
            </div>
        </div>

    <?php
    }
    
    #check to see if a status code exist as a check out
    function is_checkout($option)
    {
        
        $row = sqlQuery("SELECT toggle_setting_2 FROM list_options WHERE " .
            "list_id = 'apptstat' AND option_id = ? AND activity = 1", array($option));
        if (empty($row['toggle_setting_2'])) {
            return(false);
        }
        
        return(true);
    }
    
    #check to see if a status code exist as a check out
    function list_checkout()
    {
        
        $row = sqlQuery("SELECT toggle_setting_2 FROM list_options WHERE " .
            "list_id = 'apptstat' AND option_id = ? AND activity = 1", array($option));
        if (empty($row['toggle_setting_2'])) {
            return(false);
        }
        
        return(true);
    }
    
    