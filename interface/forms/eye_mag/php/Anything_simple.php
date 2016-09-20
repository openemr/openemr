<?php

/** 
 * forms/eye_mag/php/Anything_simple.php 
 * 
 * Adaptation of AnythingSlider's Anything_simple.php to fit Eye Exam form
 * 
 * Copyright (C) 2016 Raymond Magauran <magauran@MedFetch.com> 
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
 * @author Ray Magauran <magauran@MedFetch.com> 
 * @link http://www.open-emr.org 
 */
     
    $fake_register_globals=false;
    $sanitize_all_escapes=true;
    include_once("../../../globals.php");
    include_once("$srcdir/acl.inc");
    include_once("$srcdir/lists.inc");
    include_once("$srcdir/api.inc");
    include_once("$srcdir/sql.inc");
    require_once("$srcdir/formatting.inc.php");
	require_once("$srcdir/forms.inc");

    $form_name = "Eye Form";
    $form_folder = "eye_mag";
    include_once($GLOBALS['webserver_root']."/interface/forms/".$form_folder."/php/".$form_folder."_functions.php");
	
	$pid = $_SESSION['pid'];
	$display = $_REQUEST['display'];
	$category_id = $_REQUEST['category_id'];
	$encounter = $_REQUEST['encounter'];
	$category_name = $_REQUEST['category_name'];
	
    $query = "SELECT * FROM patient_data where pid=?";
    $pat_data =  sqlQuery($query,array($pid));
 	
    $providerID  =  getProviderIdOfEncounter($encounter);
	$providerNAME = getProviderName($providerID);
	$query = "SELECT * FROM users where id = ?";
	$prov_data =  sqlQuery($query,array($providerID));

    $query="select form_encounter.date as encounter_date, form_eye_mag.* from form_eye_mag ,forms,form_encounter
    where 
    form_encounter.encounter =? and 
    form_encounter.encounter = forms.encounter and 
    form_eye_mag.id=forms.form_id and
    forms.deleted != '1' and 
    form_eye_mag.pid=? ";        
    $encounter_data =sqlQuery($query,array($encounter,$pid));
    $dated = new DateTime($encounter_data['encounter_date']);
    $dated = $dated->format('Y-m-d');
	$visit_date = oeFormatShortDate($dated);

 	list($documents) = document_engine($pid);
              
?><!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />

		<title><?php echo xlt('Document Library'); ?></title>
		<link rel="shortcut icon" href="<?php echo $GLOBALS['assets_static_relative'] ?>/AnythingSlider-1-9-4/demos/images/favicon.ico" type="image/x-icon">
		<link rel="apple-touch-icon" href="<?php echo $GLOBALS['assets_static_relative'] ?>/AnythingSlider-1-9-4/demos/images/apple-touch-icon.png">

	    <script src="<?php echo $GLOBALS['assets_static_relative'] ?>/jquery-min-1-10-2/index.js"></script>
	    <script src="<?php echo $GLOBALS['assets_static_relative'] ?>/bootstrap-3-3-4/dist/js/bootstrap.min.js"></script>  
      	<script src="<?php echo $GLOBALS['assets_static_relative'] ?>/jquery-ui-1-11-4/jquery-ui.min.js"></script>
		<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/AnythingSlider-1-9-4/css/anythingslider.css">
		<!-- AnythingSlider optional extensions -->
		<script src="<?php echo $GLOBALS['assets_static_relative'] ?>/AnythingSlider-1-9-4/js/jquery.anythingslider.fx.js"></script> 
		<script src="<?php echo $GLOBALS['assets_static_relative'] ?>/AnythingSlider-1-9-4/js/jquery.anythingslider.video.js"></script>
		<!-- Anything Slider optional plugins -->
		 <script src="<?php echo $GLOBALS['assets_static_relative'] ?>/AnythingSlider-1-9-4/js/jquery.easing.1.2.js"></script>
		<!-- Anything Slider -->
		<script src="<?php echo $GLOBALS['assets_static_relative'] ?>/AnythingSlider-1-9-4/js/jquery.anythingslider.min.js"></script>

		<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/AnythingSlider-1-9-4/css/theme-metallic.css">
		<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/AnythingSlider-1-9-4/css/theme-minimalist-round.css">
		<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/AnythingSlider-1-9-4/css/theme-minimalist-square.css">
		<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/AnythingSlider-1-9-4/css/theme-construction.css">
		<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/AnythingSlider-1-9-4/css/theme-cs-portfolio.css">

	 	<!-- ColorBox -->
	 	<link href="<?php echo $GLOBALS['assets_static_relative'] ?>/AnythingSlider-1-9-4/demos/colorbox/colorbox.css" rel="stylesheet">
		<script src="<?php echo $GLOBALS['assets_static_relative'] ?>/AnythingSlider-1-9-4/demos/colorbox/jquery.colorbox-min.js"></script>
		<script type="text/javascript" src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js"></script>
		<style>
			 #slider { width: 700px; height: 390px; }
			 /* New in version 1.7+ */
			 #slider {
			 	width: 1200px;
			 	height: 600px;
			 	list-style: none;
			 }
			 /* CSS to expand the image to fit inside colorbox */
			 #cboxPhoto { width: 100%; height: 100%; margin: 0 !important; }
			 /* Change metallic theme defaults to show thumbnails */
			 div.anythingControls {
			 	bottom: 25px; /* thumbnail images are larger than the original bullets; move it up */
			 }
			 .anythingSlider-metallic .thumbNav a {
			 	background-image: url();
			 	height: 30px;
			 	width: 30px;
			 	border: #000 1px solid;
			 	border-radius: 2px;
			 	-moz-border-radius: 2px;
			 	-webkit-border-radius: 2px;
			 	text-indent: 0;
			 }
			 .anythingSlider-metallic .thumbNav a span {
			 	visibility: visible; /* span changed to visibility hidden in v1.7.20 */
			 }
			 /* border around link (image) to show current panel */
			 .anythingSlider-metallic .thumbNav a:hover,
			 .anythingSlider-metallic .thumbNav a.cur {
			 	border-color: #fff;
			 }
			 /* reposition the start/stop button */
			 .anythingSlider-metallic .start-stop {
			 	margin-top: 15px;
			 }
			 .git {
			 	background-color: #DEC2C4;
			 	}
		</style>

	 	<!-- AnythingSlider initialization -->
	 	<script>
			// DOM Ready
			$(function(){
				$('#slider').anythingSlider({
					// Appearance
					theme               : "metallic", // Theme name
					mode                : "horizontal",   // Set mode to "horizontal", "vertical" or "fade" (only first letter needed); replaces vertical option
					expand              : false,     // If true, the entire slider will expand to fit the parent element
					resizeContents      : false,      // If true, solitary images/objects in the panel will expand to fit the viewport
					showMultiple        : false,     // Set this value to a number and it will show that many slides at once
					easing              : "swing",   // Anything other than "linear" or "swing" requires the easing plugin or jQuery UI

					buildArrows         : true,      // If true, builds the forwards and backwards buttons
					buildNavigation     : true,      // If true, builds a list of anchor links to link to each panel
					buildStartStop      : false,      // If true, builds the start/stop button

					appendForwardTo     : null,      // Append forward arrow to a HTML element (jQuery Object, selector or HTMLNode), if not null
					appendBackTo        : null,      // Append back arrow to a HTML element (jQuery Object, selector or HTMLNode), if not null
					appendControlsTo    : null,      // Append controls (navigation + start-stop) to a HTML element (jQuery Object, selector or HTMLNode), if not null
					appendNavigationTo  : null,      // Append navigation buttons to a HTML element (jQuery Object, selector or HTMLNode), if not null
					appendStartStopTo   : null,      // Append start-stop button to a HTML element (jQuery Object, selector or HTMLNode), if not null

					toggleArrows        : true,     // If true, side navigation arrows will slide out on hovering & hide @ other times
					toggleControls      : true,     // if true, slide in controls (navigation + play/stop button) on hover and slide change, hide @ other times

					startText           : "<?php echo xla("Start"); ?>",   // Start button text
					stopText            : "<?php echo xla("Stop"); ?>",    // Stop button text
					forwardText         : "&raquo;", // Link text used to move the slider forward (hidden by CSS, replaced with arrow image)
					backText            : "&laquo;", // Link text used to move the slider back (hidden by CSS, replace with arrow image)
					tooltipClass        : "tooltip", // Class added to navigation & start/stop button (text copied to title if it is hidden by a negative text indent)

					// Function
					enableArrows        : true,      // if false, arrows will be visible, but not clickable.
					enableNavigation    : true,      // if false, navigation links will still be visible, but not clickable.
					enableStartStop     : true,      // if false, the play/stop button will still be visible, but not clickable. Previously "enablePlay"
					enableKeyboard      : true,      // if false, keyboard arrow keys will not work for this slider.

					// Navigation
					startPanel          : 1,         // This sets the initial panel
					changeBy            : 1,         // Amount to go forward or back when changing panels.
					hashTags            : true,      // Should links change the hashtag in the URL?
					infiniteSlides      : false,      // if false, the slider will not wrap & not clone any panels
					//navigationFormatter : 1,      // Details at the top of the file on this use (advanced use)
					navigationSize      : 10,     // Set this to the maximum number of visible navigation tabs; false to disable
	    			navigationFormatter : function(i, panel){
	      									return panel.find('h2').text();
	    									},
					// Slideshow options
					autoPlay            : false,     // If true, the slideshow will start running; replaces "startStopped" option
					autoPlayLocked      : false,     // If true, user changing slides will not stop the slideshow
					autoPlayDelayed     : false,     // If true, starting a slideshow will delay advancing slides; if false, the slider will immediately advance to the next slide when slideshow starts
					pauseOnHover        : true,      // If true & the slideshow is active, the slideshow will pause on hover
					stopAtEnd           : false,     // If true & the slideshow is active, the slideshow will stop on the last page. This also stops the rewind effect when infiniteSlides is false.
					playRtl             : false,     // If true, the slideshow will move right-to-left

					// Times
					delay               : 3000,      // How long between slideshow transitions in AutoPlay mode (in milliseconds)
					resumeDelay         : 15000,     // Resume slideshow after user interaction, only if autoplayLocked is true (in milliseconds).
					animationTime       : 600,       // How long the slideshow transition takes (in milliseconds)
					delayBeforeAnimate  : 0,         // How long to pause slide animation before going to the desired slide (used if you want your "out" FX to show).

					// Callbacks
					onBeforeInitialize  : function(e, slider) {}, // Callback before the plugin initializes
					onInitialized       : function(e, slider) {}, // Callback when the plugin finished initializing
					onShowStart         : function(e, slider) {}, // Callback on slideshow start
					onShowStop          : function(e, slider) {}, // Callback after slideshow stops
					onShowPause         : function(e, slider) {}, // Callback when slideshow pauses
					onShowUnpause       : function(e, slider) {}, // Callback when slideshow unpauses - may not trigger properly if user clicks on any controls
					onSlideInit         : function(e, slider) {}, // Callback when slide initiates, before control animation
					onSlideBegin        : function(e, slider) {}, // Callback before slide animates
					onSlideComplete     : function(slider) {},    // Callback when slide completes; this is the only callback without an event "e" parameter

					// Interactivity
					clickForwardArrow   : "click",         // Event used to activate forward arrow functionality (e.g. add jQuery mobile's "swiperight")
					clickBackArrow      : "click",         // Event used to activate back arrow functionality (e.g. add jQuery mobile's "swipeleft")
					clickControls       : "click focusin", // Events used to activate navigation control functionality
					clickSlideshow      : "click",         // Event used to activate slideshow play/stop button
					allowRapidChange    : true,           // If true, allow rapid changing of the active pane, instead of ignoring activity during animation

					// Video
					resumeOnVideoEnd    : true,      // If true & the slideshow is active & a supported video is playing, it will pause the autoplay until the video is complete
					resumeOnVisible     : true,      // If true the video will resume playing (if previously paused, except for YouTube iframe - known issue); if false, the video remains paused.
					addWmodeToObject    : "opaque",  // If your slider has an embedded object, the script will automatically add a wmode parameter with this setting
					isVideoPlaying      : function(base){ return false; } // return true if video is playing or false if not - used by video extension
				});
			});
		</script>

	    <script language="JavaScript">    
	    	<?php require_once("$srcdir/restoreSession.php"); ?>
	    </script>
	      
		<!-- Add Font stuff for the look and feel.  -->
		<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/jquery-ui-1-11-4/themes/excite-bike/jquery-ui.css">
	    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/pure-0-5-0/pure-min.css">
	    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/bootstrap-3-3-4/dist/css/bootstrap.min.css">
	    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/qtip2-2-2-1/jquery.qtip.min.css" />
	    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/font-awesome-4-6-3/css/font-awesome.min.css">
	    <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
		<link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/interface/forms/<?php echo $form_folder; ?>/css/style.css" type="text/css">    

		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="openEMR: Eye Exam">
		<meta name="author" content="openEMR: Ophthalmology">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<style>
			 /* New in version 1.7+ */
			 #slider {
			 	width: 1000px;
			 	height: 600px;
			 	list-style: none;
			 }
			 /* CSS to expand the image to fit inside colorbox */
			 #cboxPhoto { width: 100%; height: 100%; margin: 0 !important; }
			 /* Change metallic theme defaults to show thumbnails */
			 div.anythingControls {
			 	bottom: 25px; /* thumbnail images are larger than the original bullets; move it up */
			 }
			 .anythingSlider-metallic .thumbNav a {
			 	background-image: url();
			 	height: 30px;
			 	width: 30px;
			 	border: #000 1px solid;
			 	border-radius: 2px;
			 	-moz-border-radius: 2px;
			 	-webkit-border-radius: 2px;
			 	text-indent: 0;
			 }
			 .anythingSlider-metallic .thumbNav a span {
			 	visibility: visible; /* span changed to visibility hidden in v1.7.20 */
			 }
			 /* border around link (image) to show current panel */
			 .anythingSlider-metallic .thumbNav a:hover,
			 .anythingSlider-metallic .thumbNav a.cur {
			 	border-color: #fff;
			 }
			 /* reposition the start/stop button */
			 .anythingSlider-metallic .start-stop {
			 	margin-top: 15px;
			 }
			 .git {
			 	}
		</style>
	</head>
	<body id="simple">

    <!-- Navigation -->
    <nav class="navbar-fixed-top navbar-custom navbar-bright navbar-inner" role="banner" role="navigation" style="margin-bottom: 0;z-index:1999999;font-size: 1.4em;">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="container-fluid">
            <div class="navbar-header brand" style="color:black;">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#oer-navbar-collapse-1">
                    <span class="sr-only"><?php echo xlt('Toggle navigation'); ?></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                &nbsp;<img src="<?php echo $GLOBALS['webroot']; ?>/sites/default/images/login_logo.gif" class="little_image">
                <?php echo xlt('Eye Exam'); ?>
            </div>
            <div class="navbar-collapse collapse" id="oer-navbar-collapse-1">
                <ul class="navbar-nav">
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" id="menu_dropdown_file" role="button" aria-expanded="true"><?php echo xlt('File'); ?> </b></a>
                        <ul class="dropdown-menu" role="menu">
                            <li id="menu_PREFERENCES" name="menu_PREFERENCES" ><a id="BUTTON_PREFERENCES_menu" target="RTop" href="<?php echo $GLOBALS['webroot']; ?>/interface/super/edit_globals.php">
                            <i class="fa fa-angle-double-up" title="<?php echo xla('Opens in Top frame'); ?>"></i>
                            <?php echo xlt('Preferences'); ?></a></li>
                            <li class="divider"></li>
                            <li id="menu_HPI" name="menu_HPI" ><a href="#" onclick='window.close();'><?php echo xlt('Quit'); ?></a></li>
                        </ul>
                    </li>
                  
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" id="menu_dropdown_view" role="button" aria-expanded="true"><?php echo xlt('Images'); ?></a>
                        <ul class="dropdown-menu" role="menu">
                            <?php
			 				$i='0';
					      	foreach ($documents['zones'] as $zone) {
					      		if ($zone[0]['value'] == "DRAW") continue; //for now DRAW is under OTHER...
						      	//menu friendly names:
						      	if ($zone[0]['value'] == "EXT") $name = xl("External");
						      	if ($zone[0]['value'] == "ANTSEG") $name = xl("Anterior Segment");
						      	if ($zone[0]['value'] == "POSTSEG") $name = xl("Posterior Segment");
						      	if ($zone[0]['value'] == "NEURO") $name = xl("Neuro-physiology");
						      	
						      	$class = "git";
						      	if ($category_id == $zone[0]['id']) { $appends = "<i class='fa fa-arrow-down'></i>"; }
						      	if (count($documents['docs_in_zone'][$zone[0][value]]) >'0') {
					    	  		if ($zone[0][value] == $category_name) {
					      				$class='play'; 
					      			} else {
					      				$class = "git";
					      			}
					      			$count = count($documents['docs_in_zone'][$zone[0][value]]);
					      				if ($count!=1) {$s =xla('s{{suffix to make Document plural, ie. Documents}}');} else {$s='';}
					      			$response[$zone[0][value]] = '<a title="'.$count.' '.xla('Document'). $s.'" 
										class="'.$class.' " 
										href="Anything_simple.php?display=i&encounter='.attr($encounter).'&category_name='.attr($zone[0][value]).'">'.
										text($name).'</a>
										'.$append;
										$menu[$zone[0][value]] = '<li><a title="'.$count.' '.xla('Document'). $s.'" 
										class="'.$class.' " 
										href="Anything_simple.php?display=i&encounter='.attr($encounter).'&category_name='.attr($zone[0][value]).'">'.
										text($name).' <span class="menu_icon">+'.$count.'</span></a></li>';
					    	  	} else {
					      			$class="current";
					      			$response[$zone[0][value]] =  '<a title="'.xla('No Documents').'" 
							  				class="'.$class.' borderShadow"
											disabled >'.text($name).'</a>
										';
									$menu[$zone[0][value]] = '<li><a title="'.$count.' '.xla('Document'). $s.'" 
										class="'.$class.'" 
										href="Anything_simple.php?display=i&encounter='.attr($encounter).'&category_name='.attr($zone[0][value]).'">'.
										text($name).'</a></li>';
					      		}
							}
							echo $menu['EXT'].$menu['ANTSEG'].$menu['POSTSEG'].$menu['NEURO'];
				    	
							if ($category_name == "OTHER") {$class='play'; } else { $class = "git"; }
					    	echo '<li><a title="'.xla('Other Documents').'"  
										class="'.$class.'"  style="'.$style.'"
										href="Anything_simple.php?display=i&encounter='.attr($encounter).'&category_name=OTHER">
										'.xlt('OTHER').'<span class="menu_icon">+</span></a></li>
										';
							?>
						</ul>
					</li>

                    <li class="dropdown">
                        <a class="dropdown-toggle"  class="disabled" role="button" id="menu_dropdown_patients" data-toggle="dropdown"><?php echo xlt("Patients"); ?> </a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="menu1">
                          <li role="presentation"><a role="menuitem" tabindex="-1" target="RTop" href="<?php echo $GLOBALS['webroot']; ?>/interface/main/finder/dynamic_finder.php">
                            <i class="fa fa-angle-double-up" title="<?php echo xla('Opens in Top frame'); ?>"></i>
                            <?php echo xlt("Patients"); ?></a></li>
                          <li role="presentation"><a tabindex="-1" target="RTop" href="<?php echo $GLOBALS['webroot']; ?>/interface/new/new.php">
                            <i class="fa fa-angle-double-up" title="<?php echo xla('Opens in Top frame'); ?>"></i>
                            <?php echo xlt("New/Search"); ?></a> </li>
                          <li role="presentation"><a role="menuitem" tabindex="-1" target="RTop" href="<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics.php">
                            <i class="fa fa-angle-double-up" title="<?php echo xla('Opens in Top frame'); ?>"></i>
                            <?php echo xlt("Summary"); ?></a></li>
                          <li role="presentation" class="divider"></li>
                          <li role="presentation"><a role="menuitem" tabindex="-1" target="RTop" href="<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/transaction/record_request.php">
                            <i class="fa fa-angle-double-up" title="<?php echo xla('Opens in Top frame'); ?>"></i>
                            <?php echo xlt("Record Request"); ?></a></li>
                          <li role="presentation" class="divider"></li>
                          <li role="presentation"><a role="menuitem" tabindex="-1" target="RTop" href="<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/ccr_import.php">
                            <i class="fa fa-angle-double-up" title="<?php echo xla('Opens in Top frame'); ?>"></i>
                            <?php echo xlt("Upload Item"); ?></a></li>
                          <li role="presentation" ><a role="menuitem" tabindex="-1" target="RTop" href="<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/ccr_pending_approval.php">
                            <i class="fa fa-angle-double-up" title="<?php echo xla('Opens in Top frame'); ?>"></i>
                            <?php echo xlt("Pending Approval"); ?></a></li>
                        </ul>
                    </li>
                   	<!-- let's import the original openEMR menu_bar here.  Needs to add restoreSession stuff? -->
                    <?php
                        $reg = Menu_myGetRegistered();
                        if (!empty($reg)) {
                            $StringEcho= '<li class="dropdown">';
                            if ( $encounterLocked === false || !(isset($encounterLocked))) {
                                foreach ($reg as $entry) {
                                    $new_category = trim($entry['category']);
                                    $new_nickname = trim($entry['nickname']);
                                    if ($new_category == '') {$new_category = htmlspecialchars(xl('Miscellaneous'),ENT_QUOTES);}
                                    if ($new_nickname != '') {$nickname = $new_nickname;}
                                    else {$nickname = $entry['name'];}
                                    if ($old_category != $new_category) { //new category, new menu section
                                        $new_category_ = $new_category;
                                        $new_category_ = str_replace(' ','_',$new_category_);
                                        if ($old_category != '') {
                                            $StringEcho.= "
                                                </ul>
                                            </li>
                                            <li class='dropdown'>
                                            ";
                                        }
                                      $StringEcho.= '
                                      <a class="dropdown-toggle" data-toggle="dropdown" 
                                        id="menu_dropdown_'.attr($new_category_).'" role="button" 
                                        aria-expanded="false">'.text($new_category).' </a>
                                        <ul class="dropdown-menu" role="menu">
                                        ';
                                      $old_category = $new_category;
                                    } 
                                    $StringEcho.= "<li>
                                    <a target='RBot' href='".$GLOBALS['webroot']."/interface/patient_file/encounter/load_form.php?formname=" .urlencode($entry['directory'])."'>
                                    <i class='fa fa-angle-double-down' title='". xla('Opens in Bottom frame')."'></i>". 
                                    xl_form_title($nickname) . "</a></li>";
                              }
                          }
                          $StringEcho.= '
                            </ul>
                          </li>
                          ';
                        } 
                        echo $StringEcho;
                    ?>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" 
                           id="menu_dropdown_library" role="button" 
                           aria-expanded="true"><?php echo xlt("Library"); ?> </a>
                        <ul class="dropdown-menu" role="menu">
                            <li role="presentation"><a role="menuitem" tabindex="-1" target="RTop"  
                            href="<?php echo $GLOBALS['webroot']; ?>/interface/main/calendar/index.php?module=PostCalendar&viewtype=day&func=view&framewidth=1020">
                            <i class="fa fa-angle-double-up" title="<?php echo xla('Opens in Top frame'); ?>"></i>&nbsp;<?php echo xlt("Calendar"); ?><span class="menu_icon"><i class="fa fa-calendar"></i>  </span></a></li>
                            <li role="presentation" class="divider"></li>
                            <li role="presentation"><a target="RTop" role="menuitem" tabindex="-1" 
                                href="<?php echo $GLOBALS['webroot']; ?>/controller.php?document&list&patient_id=<?php echo attr($pid); ?>">
                                <i class="fa fa-angle-double-up" title="<?php echo xla('Opens in Top frame'); ?>"></i>
                                <?php echo xlt("Documents"); ?></a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" 
                           id="menu_dropdown_help" role="button" 
                           aria-expanded="true"><?php echo xlt("Help"); ?> </a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="menu1">
                            <li role="presentation"><a role="menuitem" tabindex="-1" target="_blank" href="<?php echo $GLOBALS['webroot']; ?>/interface/forms/eye_mag/help.php">
                                <i class="fa fa-help"></i>  <?php echo xlt("Shorthand Help"); ?><span class="menu_icon"><i title="<?php echo xla('Click for Shorthand Help.'); ?>" class="fa fa-info-circle fa-1"></i></span></a>
                            </li>
                        </ul>
                    </li>
			    </ul>
            </div>
        </div>
    </nav>
	<br /><br />
	
	<div class="borderShadow" style="margin:0px 0px 5px 0px;padding:10px;">		
		<div style="position:absolute;margin:0 5px 10px 0; top:0.0in;text-align:center;width:95%;font-size:0.75em;;">
			<!-- Links to other demo pages & docs -->
			<div id="nav" style="position:absolute;top:0.0in;text-align:center;">	
				<?php 
				foreach ($documents['zones'][$category_name] as $zone) {
					$class = "git";
		    		$append ='';
		    		if ($category_id == $zone['id']) { 
		    			$class="play";
		    			$append = "<i class='fa fa-arrow-down'></i>"; }
			      	
					if ($zone['name'] == xl('Advance Directives') ||
						$zone['name'] == xl('Durable Power of Attorney') ||
						$zone['name'] == xl('Patient Information') ||
						$zone['name'] == xl('Living Will') ||
						$zone['name'] == xl('Imaging')) { 
					} else {
						$count = count($documents['docs_in_name'][$zone['name']]);
		      				if ($count!=1) {$s ="s";} else {$s='';}
		      			$disabled='';
						if ($count =='0') {
							$class = 'current';
							$disabled = "disabled='disabled'";
							echo ' <a '.$disabled.' title="'.$count.' '.xla('Document').$s.'" class="" >
								<span class="borderShadow '.$class.'">'.text($zone['name']).'</span></a> 
							'.$append;	
						} else {

							echo ' <a '.$disabled.' title="'.$count.' '.xla('Document').$s.'" class="'.$class.'" 
								href="Anything_simple.php?display=i&category_id='.$zone['id'].'&encounter='.$encounter.'&category_name='.$category_name.'">
								<span  class="borderShadow">'.text($zone['name']).'</span></a> 
								'.$append;	
						}
					}
				}
				?>
			</div>
		</div>
		<!-- End Links -->
	</div> 
	<br />
	<!-- Simple AnythingSlider -->
	<ul id="slider">
		<?php
		$i='0';
		if ($category_id) {
			$counter = count($documents['docs_in_cat_id'][$category_id]) -10;
			if ($counter <0) $counter ='0';
			for ($i=$counter;$i < count($documents['docs_in_cat_id'][$category_id]); $i++) {
				echo '
				<object><embed src="'.$GLOBALS['webroot'].'/controller.php?document&amp;retrieve&amp;patient_id='.$pid.'&amp;document_id='.attr($documents['docs_in_cat_id'][$category_id][$i][id]).'&amp;as_file=false" frameborder="0"
				 type="'.attr($documents['docs_in_cat_id'][$category_id][$i]['mimetype']).'" allowscriptaccess="always" allowfullscreen="true" width="800px" height="600px"></embed></object>
				 ';
			}
		} else {
			$counter = count($documents['docs_in_zone'][$category_id]) -10;
			if ($counter <0) $counter ='0';
			for ($i=$counter;$i < count($documents['docs_in_zone'][$category_name]); $i++) {
				echo '
				<object><embed src="'.$GLOBALS['webroot'].'/controller.php?document&amp;retrieve&amp;patient_id='.$pid.'&amp;document_id='.attr($documents['docs_in_zone'][$category_name][$i][id]).'&amp;as_file=false" frameborder="0"
				 type="'.attr($documents['docs_in_zone'][$category_name][$i]['mimetype']).'" allowscriptaccess="always" allowfullscreen="true" width="800px" height="600px"></embed></object>
				 ';
			}
		}
		?>		
	</ul>

	<!-- END AnythingSlider -->
	<center>
		<?php
  		$output = menu_overhaul_left($pid,$encounter);
    	echo $output;
		?>
	</center>
	<?php
    if ($display=="fullscreen") { 
       // this function is in php/".$form_name."_functions.php
      $output = menu_overhaul_bottom($pid,$encounter);
      echo $output;
    }
    ?>
	</body>
</html>
