<?php
    
    require_once('../../globals.php');
    
    require_once '/var/www/openemr/vendor/mobiledetect/mobiledetectlib/Mobile_Detect.php';
    include_once("m_functions.php");
    
    $detect = new Mobile_Detect;
    $device_type = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
    $script_version = $detect->getScriptVersion();
    
    $desktop = $_GET['desktop'];
    if ($desktop == '1') $_SESSION['desktop'] = 1;
    // If “Go to full website” link is clicked, redirect mobile user to main website
    if (($_SESSION['desktop'] == 2) || ($device_type == 'Xphone')) {
        $desktop_url = "https://oculoplasticsllc.com/openemr/interface/main/tabs/main.php";
        header("Location:" . $desktop_url);
    }
    ?>
<html style="cursor: pointer;" lang="en">
    <?php
    common_head();
    ?>
    <body>
        <div id="gb-main">

    <?php common_header($display); ?>
    
    <div id="n0tgWb">
        <div id="main">
           
            <img class="img-responsive center-block" src="/openemr/sites/default/images/login_logo.gif">

            <div style="margin-top:10px" class="qgS5Fc">
                <style>.qgS5Fc {
                        padding: 0 4px
                    }

                    .WIvcP {
                        display: table;
                        table-layout: fixed;
                        border-spacing: 4px 0;
                        width: 100%
                    }

                    .qIE9I {
                        display: table-cell;
                        padding: 8px 0 12px;
                        text-decoration: none;
                        text-align: center;
                        font-size: 12px;
                        line-height: 14px;
                        color: rgba(0, 0, 0, .54)
                    }

                    @media screen and (max-width: 339px) {
                        .qIE9I {
                            font-size: 11px
                        }
                    }

                    .Fdk9P, a.qIE9I svg {
                        width: 24px;
                        height: 24px;
                        display: block;
                        margin-left: auto;
                        margin-right: auto;
                        margin-bottom: 10px
                    }</style>
                <div class="WIvcP">
                    <a class="qIE9I"
                       href="/search?site=&amp;q=weather&amp;hl=en&amp;sa=X&amp ved=0ahUKEwiC7obBmOjcAhXDo1kKHXgnA-kQlf4BCBI">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <g fill="none" fill-rule="evenodd">
                                <path d="M0 0h24v24H0z"></path>
                                <circle cx="12" cy="12" fill="#FABB05" r="10"></circle>
                                <path d="M7 20c-3.3 0-6-2.7-6-6s2.7-6 6-6c2.6 0 4.83 1.67 5.66 4H14c2.2 0 4 1.8 4 4s-1.8 4-4 4H7z"
                                      fill="#EEE"></path>
                            </g>
                        </svg>
                        Photo</a><a class="qIE9I"
                                    href="https://www.oculoplasticsllc.com/openemr/interface/main/calendar/index.php?module=PostCalendar&viewtype=day&func=view&framewidth=1308">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <g fill="none" fill-rule="evenodd">
                                <path d="M0 0h24v24H0z"></path>
                                <path d="M7.28 7H3.05c.13 2.07 1.93 3.83 4.23 4.2 0 0 1.2.17 1.2.1 0-1.33-1.2-4.3-1.2-4.3zm1.2-2v8H7.9C4.12 13 1 9.82 1 6.1v-.48c0-.34.28-.62.62-.62h6.86zM16.7 7h4.23c-.13 2.07-1.93 3.83-4.23 4.2 0 0-1.2.17-1.2.1 0-1.33 1.2-4.3 1.2-4.3zm-1.2-2v8h.58c3.78 0 6.9-3.18 6.9-6.9v-.48c0-.34-.28-.62-.62-.62H15.5z"
                                      fill="#FABB05"></path>
                                <path d="M8 3h8v8H8z" fill="#FFF"></path>
                                <path d="M13.4 8.16l.52 2.34L12 9.26l-1.92 1.24.5-2.34-1.7-1.58 2.25-.2.87-2.2.87 2.2 2.24.2-1.7 1.58zM18.54 2H5.47C5.2 2 5 2.22 5 2.5v3.88c0 2.33 1 4.4 2.56 5.8 1.5 1.37 2.46 3.4 2.46 5.7 0 1.3-.32 2.55-.87 3.62h5.7c-.55-1.07-.87-2.3-.87-3.62 0-2.3.96-4.33 2.46-5.7C18 10.77 19 8.7 19 6.37V2.5c0-.28-.2-.5-.47-.5z"
                                      fill="#FABB05"></path>
                                <rect fill="#EE8100" height="3" rx="1" width="10" x="7" y="19"></rect>
                            </g>
                        </svg>
                        Calendar</a><a class="qIE9I"
                                       href="https://www.oculoplasticsllc.com/openemr/interface/patient_tracker/patient_tracker.php?skip_timeout_reset=1">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <g fill="none" fill-rule="evenodd" transform="translate(1 2)">
                                <polygon fill="#FABB05"
                                         points="8.08 7.19 .02 7.64 6.28 12.53 4.22 20.03 11.03 15.84 17.84 20.03 15.77 12.55 22.02 7.66 13.96 7.21 11.03 .03"></polygon>
                                <path d="M19 6.5H3v6h5.95v-.02c0-1.15.93-2.1 2.08-2.1 1.16 0 2.1.95 2.1 2.1v.02H19v-6z"
                                      fill="#EEE"></path>
                                <path d="M6.47 11.5h-.94C4.7 11.5 4 10.8 4 9.97v-.94c0-.84.7-1.53 1.53-1.53h.94C7.3 7.5 8 8.2 8 9.03v.94c0 .84-.7 1.53-1.53 1.53"
                                      fill="#EA4335"></path>
                                <path d="M16.47 11.5h-.94c-.84 0-1.53-.7-1.53-1.53v-.94c0-.84.7-1.53 1.53-1.53h.94c.84 0 1.53.7 1.53 1.53v.94c0 .84-.7 1.53-1.53 1.53"
                                      fill="#4285F4"></path>
                            </g>
                        </svg>
                        Flow Board</a><a class="qIE9I"
                                         href="https://www.oculoplasticsllc.com/openemr/interface/main/messages/messages.php?nomenu=1&go=SMS_bot&dir=back&show=new">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <g fill="none" fill-rule="evenodd">
                                <path d="M6.75 12h2.5v10h-2.5z" fill="#FABB05"></path>
                                <path d="M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.84 3.75 3.97h2.5C11.35 12.84 13 11.12 13 9V2h-2v7z"
                                      fill="#BDBDBD"></path>
                                <path d="M18.5 12.97H21V22h-2.5z" fill="#FABB05"></path>
                                <path d="M16 6v8h5V2c-2.76 0-5 2.24-5 4z" fill="#BDBDBD"></path>
                                <path d="M0 0h24v24H0"></path>
                            </g>
                        </svg>
                        SMS Bot</a></div>
            </div>
            <div id="belowsb"></div>
        </div>
    </div>
    
    <?php common_footer($display); ?>
 
</div>
    </body>
</html>