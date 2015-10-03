<?php

    //get pid/form_id/encounter and show the series of draws for that encounter
    //if they don't exist, show the base image
    $fake_register_globals=false;
    $sanitize_all_escapes=true;
	//error_reporting(E_ALL & ~E_NOTICE);
    include_once("../../../../globals.php");
    include_once("$srcdir/acl.inc");
    include_once("$srcdir/lists.inc");
    include_once("$srcdir/api.inc");
    include_once("$srcdir/sql.inc");
    require_once("$srcdir/formatting.inc.php");
        //	require_once("$srcdir/restoreSession.php");
        // 	print_r($_REQUEST);
    $form_name = "eye_mag";
    $form_folder = "eye_mag";
    
    include_once($GLOBALS['webserver_root']."/interface/forms/".$form_folder."/php/".$form_folder."_functions.php");
    @extract($_REQUEST);
    @extract($_SESSION);
	// Get users preferences, for this user ,
	// If a fresh install or new user, get the default user preferences
	$query  = "SELECT * FROM form_eye_mag_prefs where PEZONE='PREFS' AND (id=? or id=2048)ORDER BY id,ZONE_ORDER,ordering";
	$result = sqlStatement($query,array($_SESSION['authUserID']));
	while ($prefs= sqlFetchArray($result))   {
	    @extract($prefs);
	    $$LOCATION = $VALUE;
	}

    // get pat_data and user_data
    $query = "SELECT * FROM patient_data where pid='$pid'";
    $pat_data =  sqlQuery($query);
    @extract($pat_data);
    
    $query = "SELECT * FROM users where id = '".$_SESSION['authUserID']."'";
    $prov_data =  sqlQuery($query);
    $providerID = $prov_data['fname']." ".$prov_data['lname'];
    
    $query="select form_encounter.date as encounter_date, form_eye_mag.* from form_eye_mag ,forms,form_encounter
    where 
    form_encounter.encounter =? and 
    form_encounter.encounter = forms.encounter and 
    form_eye_mag.id=forms.form_id and
    forms.deleted != '1' and 
    form_eye_mag.pid=? ";        
    //	echo $query."<br />";
    $encounter_data =sqlQuery($query,array($encounter,$pid));
    @extract($encounter_data);
    $dated = new DateTime($encounter_date);
    $visit_date = $dated->format('m/d/Y'); 
    //var_dump($encounter_data);
    //echo "<pr>encounter with id = ".$encounter." with ".$id."<br />"; 
    //var_dump($encounter_data);
    //$GLOBALS['webserver_root']."/interface/forms/
 //   echo "pid = ".$pid."<br />encounter = ".$encounter."<br />category_name = ".$category_name."<br />";
	
 list($documents) = document_engine($pid);
              
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8" />

	<title>Document Library</title>
	<link rel="shortcut icon" href="demos/images/favicon.ico" type="image/x-icon">
	<link rel="apple-touch-icon" href="demos/images/apple-touch-icon.png">

	
	<!-- Demo stuff -->
	<link rel="stylesheet" href="demos/css/page.css">

	<!-- Anything Slider -->
	<link rel="stylesheet" href="css/anythingslider.css">
	<script src="js/jquery.anythingslider.js"></script>

<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>  
<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

	<!-- AnythingSlider optional extensions
	<script src="js/jquery.anythingslider.fx.js"></script> 
	 <script src="js/jquery.anythingslider.video.js"></script>

<!-- Anything Slider optional plugins ->
 <script src="js/jquery.easing.1.2.js"></script>
 -->
<!-- Anything Slider -->
<link href="css/anythingslider.css" rel="stylesheet">
<script src="js/jquery.anythingslider.min.js"></script>

<link rel="stylesheet" href="css/theme-metallic.css">
	<link rel="stylesheet" href="css/theme-minimalist-round.css">
	<link rel="stylesheet" href="css/theme-minimalist-square.css">
	<link rel="stylesheet" href="css/theme-construction.css">
	<link rel="stylesheet" href="css/theme-cs-portfolio.css">


 <!-- ColorBox -->
 <link href="demos/colorbox/colorbox.css" rel="stylesheet">
	 <script src="demos/colorbox/jquery.colorbox-min.js"></script>
	 
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
	 	}</style>

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

				startText           : "Start",   // Start button text
				stopText            : "Stop",    // Stop button text
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

	<link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.5.0/pure-min.css">
	<link rel="stylesheet" href="../../../../forms/<?php echo $form_folder; ?>/style.css" type="text/css">    
 	<link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/library/css/font-awesome-4.2.0/css/font-awesome.min.css">
 <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">

</head>

<body id="simple">
	<?php
    if ($display=="fullscreen") { 
      // trial fullscreen will lead to tablet versions and bootstrap menu overhaul
      // this function is in php/eye_mag_functions.php
      $output = menu_overhaul_top($pid,$encounter);
     // echo $output;
    }
    ?>
	<!-- Start Patient Identifiers -->
	<div class="ACT_TEXT borderShadow" style="margin:0px 0px 5px 0px;padding:10px;">		
		<table cellspacing="2" style="margin:0px 0px 5px 0px;padding:10px;" >
			<tr>
				<td colspan="2" rowspan="2">
					<?php 
					//if the patient has a photograph, use it else use generic avitar thing.
					if ($documents['docs_in_name']['Patient Photograph'][0]['id']) {
						?>
						<object><embed src="/openemr/controller.php?document&amp;retrieve&amp;patient_id=<?php echo $pid; ?>&amp;document_id=<?php echo $documents['docs_in_name']['Patient Photograph'][0]['id']; ?>&amp;as_file=false" frameborder="0"
						 type="<?php 
						 echo $documents['docs_in_name']['Patient Photograph'][0]['mimetype']; 
						 ?>" allowscriptaccess="always" allowfullscreen="false" width="80"></embed></object>
						<?php 
					} else {
						?>
						<object><embed src="../../../../forms/<?=$form_folder?>/images/anon.gif" frameborder="0"
						 type="image/gif" width="80"></embed></object>
						<?php
					}
					?>
				</td>
			
				<td>
					<?php
						echo $fname." ".$lname."<br />";
						?>
						ID: <?php echo $pid; ?>
				</td>
			</tr>
		</table>
	
		<div style="position:absolute;margin:0 5px 10px 0; top:0.0in;text-align:right;width:75%;right:0.5in;">
			

			<!-- End Patient Identifiers -->

			<!-- Links to other demo pages & docs -->
			<div id="nav" style="position:absolute;top:0.0in;text-align:left;">
				<?php 
				//use this to build off for the document server
				//for each section in imaging, make a link here
				//<a href="expand.html">OCT</a>
				//<a href="../../forms/'.$form_folder.'/css/AnythingSlider/simple.php?display=i&category_id='.$cat_row[id].'&id='.$form_id.'&encounter='.$encounter.'&category_name='.urlencode(xla($category_value)).'" onclick="top.restoreSession;">
		        //        <img src="../../forms/'.$form_folder.'/images/jpg.png" class="little_image">
		        //echo "count Imaging = ".count($imaging)."<br />";
		        
		        	/* For now the Drawings are under "other"
		    	if ($category_name == "DRAW") {$class='play'; } else { $class = "git"; }
		    	echo '<a title="Encounter Drawings"  
							class="'.$class.' borderShadow"  style="'.$style.'"
							href="simple.php?display=i&encounter='.$encounter.'&category_name=DRAW">
							DRAW</a>
							';

				*/
			
		      	$i='0';
		      	foreach ($documents['zones'] as $zone) {
		      		if ($zone[0]['value'] == "DRAW") continue; //for now DRAW is under OTHER...
			      	
			      	$class = "git";
			      	if ($category_id == $zone[0]['id']) { $appends = "<i class='fa fa-arrow-down'></i>"; }
			      	if (count($documents['docs_in_zone'][$zone[0][value]]) >'0') {
		    	  		if ($zone[0][value] == $category_name) {
		      				$class='play'; 
		      			} else {
		      				$class = "git";
		      			}
		      			$count = count($documents['docs_in_zone'][$zone[0][value]]);
		      				if ($count!=1) {$s ="s";} else {$s='';}
		      			$response[$zone[0][value]] = '<a title="'.$count.' Document'. $s.'" 
							class="'.$class.'  borderShadow" 
							href="simple.php?display=i&encounter='.$encounter.'&category_name='.$zone[0][value].'">'.
							$zone[0][value].'</a>
							'.$append;
		    	  	} else {
		      			$class="current";
		      			$response[$zone[0][value]] =  '<a title="No Documents" 
				  				class="'.$class.' borderShadow"
								disabled >'.$zone[0][value].'</a>
							';
		      		}
				}
				echo $response['EXT'].$response['ANTSEG'].$response['POSTSEG'].$response['NEURO'];
		    	/*
		    	if ($category_name == "DRAW") {$class='play'; } else { $class = "git"; }
		    	echo '<a title="Encounter Drawings"  
							class="'.$class.' borderShadow"  style="'.$style.'"
							href="simple.php?display=i&encounter='.$encounter.'&category_name=DRAW">
							DRAW</a>
							';
				*/
				if ($category_name == "OTHER") {$class='play'; } else { $class = "git"; }
			    echo '<a title="Other Documents"  
								class="'.$class.' borderShadow"  style="'.$style.'"
								href="simple.php?display=i&encounter='.$encounter.'&category_name=OTHER">
								OTHER</a>
								';
				
				?>
			    <a class="git borderShadow" title="Return to Eye Exam" href="../../../../../interface/patient_file/encounter/view_form.php?formname=eye_mag&id=<?php echo $id; ?>">EXAM</a>
				<!--
				<a class="play" href="http://jsfiddle.net/Mottie/ycUB6/">Playground</a>
				<a class="git" href="https://github.com/CSS-Tricks/AnythingSlider/wiki">Documentation</a>
				<a class="git" href="https://github.com/CSS-Tricks/AnythingSlider/zipball/master">Download</a>
				<a class="issue" href="https://github.com/CSS-Tricks/AnythingSlider/issues">Issues</a><br><br>
				-->
				
				<?php 

				echo "<br />";
				//<div style='position:absolute;top:0.1in;'><pre>";
				//echo ""; var_dump($documents['docs_in_name']);echo "</div>";
				foreach ($documents['zones'][$category_name] as $zone) {
					$class = "git";
		    		$append ='';
		    		if ($category_id == $zone['id']) { 
		    			$class="play";
		    			$appends = "<i class='fa fa-arrow-down'></i>"; }
			      	
					if ($zone['name'] == "Advance Directives" or 
						$zone['name'] == "Durable Power of Attorney" or
						$zone['name'] == "Patient Information" or
						$zone['name'] == "Living Will" or 
						$zone['name'] == "Imaging") { 
					} else {
						$count = count($documents['docs_in_name'][$zone['name']]);
		      				if ($count!=1) {$s ="s";} else {$s='';}
		      			$disabled='';
						if ($count =='0') {
							$class = 'current';
							$disabled = "disabled='disabled'";
							echo ' <a '.$disabled.' title="'.count($documents['docs_in_name'][$zone['name']]).' Document'.$s.'" class="'.$class.'" >
								<span>'.$zone['name'].'</span></a> 
							'.$append;	
						} else {

							echo ' <a '.$disabled.' title="'.count($documents['docs_in_name'][$zone['name']]).' Document'.$s.'" class="'.$class.'" 
								href="simple.php?display=i&category_id='.$zone['id'].'&encounter='.$encounter.'&category_name='.$category_name.'">
								<span>'.$zone['name'].'</span></a> 
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
		<object><embed src="/openemr/controller.php?document&amp;retrieve&amp;patient_id='.$pid.'&amp;document_id='.$documents['docs_in_cat_id'][$category_id][$i][id].'&amp;as_file=false" frameborder="0"
		 type="'.$documents['docs_in_cat_id'][$category_id][$i]['mimetype'].'" allowscriptaccess="always" allowfullscreen="true" width="800px" height="600px"></embed></object>
		 ';
	}
} else {
	$counter = count($documents['docs_in_zone'][$category_id]) -10;
	if ($counter <0) $counter ='0';
	for ($i=$counter;$i < count($documents['docs_in_zone'][$category_name]); $i++) {
		echo '
		<object><embed src="/openemr/controller.php?document&amp;retrieve&amp;patient_id='.$pid.'&amp;document_id='.$documents['docs_in_zone'][$category_name][$i][id].'&amp;as_file=false" frameborder="0"
		 type="'.$documents['docs_in_zone'][$category_name][$i]['mimetype'].'" allowscriptaccess="always" allowfullscreen="true" width="800px" height="600px"></embed></object>
		 ';
	}
}
	
//}
?>
		
	</ul>

	<!-- END AnythingSlider -->
<?php
    if ($display=="fullscreen") { 
      // trial fullscreen will lead to tablet versions and bootstrap menu overhaul
      // this function is in php/eye_mag_functions.php
      $output = menu_overhaul_bottom($pid,$encounter);
     // echo $output;
    }
    ?>
</body>

</html>