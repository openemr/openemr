<?php /* Smarty version 2.6.30, created on 2017-08-13 21:08:08
         compiled from default/views/header.html */ ?>
<html>
<head>
<!-- Get the style sheet for the theme defined in globals.php -->
<link rel="stylesheet" href="<?php echo $GLOBALS['css_header'] ?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/font-awesome-4-6-3/css/font-awesome.min.css">

<!-- this style sheet is used for the ajax_* style calendars -->
<link rel="stylesheet" href="<?php echo $GLOBALS['webroot'].'/interface/themes/ajax_calendar.css?v='.$GLOBALS['v_js_includes']; ?>" type="text/css">
<!--[if IE]>
        <link rel="stylesheet" href="<?php echo $GLOBALS['webroot'].'/interface/themes/ajax_calendar_ie.css'; ?>" type="text/css"/>
<![endif]-->

<!-- the javascript used for the ajax_* style calendars -->
<script type="text/javascript" src="<?php  echo $GLOBALS['webroot']  ?>/library/dialog.js?v=<?php  echo $GLOBALS['v_js_includes'];  ?>"></script>
<script type="text/javascript" src="<?php  echo $GLOBALS['webroot']  ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php  echo $GLOBALS['assets_static_relative']  ?>/jquery-min-1-7-2/index.js"></script>
<script type="text/javascript" src="<?php  echo $GLOBALS['webroot']  ?>/library/js/calendarDirectSelect.js"></script>
<script>function event_time_click(elem){EditEvent($(elem).parents("div.event_appointment").get(0))} </script>
<?php 
/**
 * @param string $displayString This is the text to be displayed(most likely representing the time of an event).  It is the responsibility of the caller to escape any entities as needed. This allows html tags to be used in the $displayString if desired.
 * @return string html anchor element with javascript onclick event that edits an appointment
 */
function create_event_time_anchor($displayString)
{
    $title=htmlspecialchars(xl('Click to edit'));
    return "<a class='event_time' onclick='event_time_click(this)' title='" .$title."'>".$displayString."</a>";
}
 ?>

</head>
<?php 
    echo "<body style='background-color:".$GLOBALS['style']['BGCOLOR2']."'>";
 ?>