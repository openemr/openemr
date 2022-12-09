<?php
$fake_register_globals=false;
$sanitize_all_escapes=true;
$frmdir = 'cases';
$frmn = 'form_'.$frmdir;
include_once("../../globals.php");
include_once($GLOBALS['srcdir'].'/wmt-v2/form_setup.inc.php');

// COMMENT THIS OUT FOR BELOW 5.0.1
use OpenEMR\Core\Header;

$caller = '';
$list_popup = '';
$list_mode = 'list';
if(isset($_GET['caller'])) $caller = strip_tags($_GET['caller']);
if(isset($_GET['list_mode'])) $list_mode = strip_tags($_GET['list_mode']);
if(isset($_GET['list_popup'])) $list_popup = strip_tags($_GET['list_mode']);
if($caller) $save_style .= '&caller=' . $caller;
if($list_mode) $save_style .= '&list_mode=' . $list_mode;
if($list_popup) $save_style .= '&list_popup=' . $list_popup;

include_once($GLOBALS['srcdir'].'/wmt-v2/form_process_bs.inc.php');

include_once($GLOBALS['srcdir'].'/wmt-v2/form_head_bs.inc.php');

?>

<?php

//
include_once($GLOBALS['srcdir'].'/wmt-v2/form_body_start_bs.inc.php');

// SPECIAL DIAGNOSIS LOADING SINCE NOT ENCOUNTER LINKED
// THIS MAY NEED TO BE MOVED TO THE MODULE
// $diag = explode("~|", $dt{'diagnoses'}); 

include_once($GLOBALS['srcdir'].'/wmt-v2/form_loop_bs.inc.php');

?>
<?php

if(!isset($bill_form)) $bill_form = false;
$exit_href = FORMS_DIR_JS . 'cases/case_list.php?pid=' . $pid . '&mode=' . 
	$list_mode;
if($caller) $exit_href .= '&caller=' . $caller;
if($list_popup) $exit_href .= '&list_popup=' . $list_popup;
?>

<div class="form-row form-row mt-4 mb-3">
		<div class="form-group col">
			<a id="save_and_quit" href="javascript:;" onclick="return caselibObj.saveCase('<?php echo $pid ?>');" tabindex='-1' class='btn btn-primary'><span><?php echo xl('Save Data'); ?></span></a>
			<a class='btn btn-secondary' tabindex='-1' onclick='return cancelClicked()' href="javascript: <?php echo $pop_form  ? 'window.close();' : "window.location='" . $exit_href . "'"; ?>" ><span><?php echo xl('Cancel'); ?></span></a>
		</div>
</div>

<?php include($GLOBALS['srcdir'].'/wmt-v2/report_signatures.inc.php'); ?>

</div><!-- THIS IS THE OVERALL INNER CONTAINER END -->

</div><!-- THIS IS THE OVERALL BODY END, STARTED IN 'form_body_start' -->
</form>

<?php include_once($GLOBALS['srcdir'].'/wmt-v2/form_footer.js.php'); ?>

</body>
</html>
