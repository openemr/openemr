<?php
 // Copyright (C) 2010-2011 Aron Racho <aron@mi-squred.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
 use OpenEMR\Core\Header;
?>

<html>
<head>
    <?php Header::setupHeader(['common']);?>
    <?php html_header_show();?>
    <link rel="stylesheet" href="<?php echo $GLOBALS['css_header'] ?>" type="text/css">
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-2-2/index.js"></script>
    <link rel="stylesheet" href="<?php css_src('rules.css') ?>" type="text/css">
    <style>
        @media only screen and (max-width: 1220px) {
            [class*="col-"] {
            width: 100%;
            text-align:left!Important;
        }
        span[class*="_col"]{
            font-size:1.3.em;
        }
        [class*="left_col"]{
            padding-left:15px
       }
    </style>
    
<title><?php echo xlt("Plans and Rules Configuration"); ?></title>
</head>

<body class='body_top'>
    <div class='container'>
    <?php
    if (file_exists($viewBean->_view_body)) {
        require_once($viewBean->_view_body);
    }
    ?>
    </div><!--end of container div-->
    <div class="row">
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog oe-modal-dialog modal-lg">
                <div class="modal-content oe-modal-content">
                    <div class="modal-header clearfix"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" style="color:#000000; font-size:1.5em;">×</span></button></div>
                    <div class="modal-body">
                        <iframe src="" id="targetiframe" style="height:75%; width:100%; overflow-x: hidden; border:none" allowtransparency="true"></iframe>  
                    </div>
                    <div class="modal-footer" style="margin-top:0px;">
                       <button class="btn btn-link btn-cancel pull-right" data-dismiss="modal" type="button"><?php echo xlt('close'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $( document ).ready(function() {
            <?php 
                if($_GET['action'] == "browse!list") {
                    echo "var helpFor = 'cdrPlans';";
                } elseif($_GET['action'] == "browse!plans_config") {
                    echo "var helpFor = 'plansConfig';";
                } elseif($_GET['action'] == "detail!view") {
                    echo "var helpFor = 'viewDetail';";
                } elseif($_GET['action'] == "edit!summary") {
                    echo "var helpFor = 'editSummary';";
                } elseif($_GET['action'] == "edit!intervals") {
                    echo "var helpFor = 'editIntervals';";
                } elseif($_GET['action'] == "edit!add_criteria" && $_GET['criteriaType'] == "filter") {
                    echo "var helpFor = 'addCriteriaDemographics';";
                } elseif($_GET['action'] == "edit!add_criteria" && $_GET['criteriaType'] == "target") {
                    echo "var helpFor = 'addCriteria';";
                } elseif($_GET['action'] == "edit!add_action") {
                    echo "var helpFor = 'addAction';";
                } elseif($_GET['action'] == "alerts!listactmgr") {
                    echo "var helpFor = 'alertList';";
                }
            ?>
            $('#help-href').click (function(){
                var helpURL = '';
                if (helpFor =='cdrPlans') {
                    var helpURL = '../rules/help/cdr_plans_help.php';
                } else if (helpFor =='plansConfig') {
                    var helpURL = '../rules/help/plans_config_help.php';
                } else if (helpFor =='viewDetail') {
                    var helpURL = '../rules/help/detail_view_help.php';
                } else if (helpFor =='editSummary') {
                    var helpURL = '../rules/help/edit_summary_help.php';
                } else if (helpFor =='addCriteriaDemographics') {
                    var helpURL = '../rules/help/edit_add_criteria_demographics_help.php';
                } else if (helpFor =='addCriteria') {
                    var helpURL = '../rules/help/edit_add_criteria_help.php';
                } else if (helpFor =='addAction') {
                    var helpURL = '../rules/help/edit_add_action_help.php';
                } else if (helpFor =='alertList') {
                    var helpURL = '../rules/help/cdr_alert_manager_help.php';
                }
                
                document.getElementById('targetiframe').src = helpURL;
            })
        });
    </script>
</body>

</html>
