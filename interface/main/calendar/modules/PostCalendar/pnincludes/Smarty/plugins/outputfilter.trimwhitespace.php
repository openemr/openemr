<?php
/*
* Smarty plugin
* -------------------------------------------------------------
* File: outputfilter.trimwhitespace.php
* Type: outputfilter
* Name: trimwhitespace
* Version: 1.2
* Date: April 30, 2002
* Purpose: trim leading white space and blank lines from
* template source after it gets interpreted, cleaning
* up code and saving bandwidth. Does not affect
* <PRE></PRE> and <SCRIPT></SCRIPT> blocks.
* Install: Drop into the plugin directory, call
* $smarty->load_filter('output','trimwhitespace');
* from application.
* Author: Monte Ohrt <monte@ispi.net>
* -------------------------------------------------------------
*/
function smarty_outputfilter_trimwhitespace($source, &$smarty)
{
// Pull out the script blocks
preg_match_all("!<script[^>]+>.*?</script>!is", $source, $match);
$_script_blocks = $match[0];
$source = preg_replace("!<script[^>]+>.*?</script>!is",
'@@@SMARTY:TRIM:SCRIPT@@@', $source);
 
// Pull out the pre blocks
preg_match_all("!<pre>.*?</pre>!is", $source, $match);
$_pre_blocks = $match[0];
$source = preg_replace("!<pre>.*?</pre>!is",
'@@@SMARTY:TRIM:PRE@@@', $source);
 
// Pull out the textarea blocks
preg_match_all("!<textarea[^>]+>.*?</textarea>!is", $source, $match);
$_textarea_blocks = $match[0];
$source = preg_replace("!<textarea[^>]+>.*?</textarea>!is",
'@@@SMARTY:TRIM:TEXTAREA@@@', $source);
 
// remove all leading spaces, tabs and carriage returns NOT
// preceeded by a php close tag.
$source = preg_replace('/((?<!\?>)\n)[\s]+/m', '\1', $source);
 
// replace script blocks
foreach($_script_blocks as $curr_block) {
$source = preg_replace("!@@@SMARTY:TRIM:SCRIPT@@@!",$curr_block,$source,1);
}
// replace pre blocks
foreach($_pre_blocks as $curr_block) {
$source = preg_replace("!@@@SMARTY:TRIM:PRE@@@!",$curr_block,$source,1);
}
// replace textarea blocks
foreach($_textarea_blocks as $curr_block) {
$source = preg_replace("!@@@SMARTY:TRIM:TEXTAREA@@@!",$curr_block,$source,1);
}
 
return $source;
}
?>
