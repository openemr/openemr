<?php
include_once($GLOBALS['srcdir'].'/wmt-v2/rvw_log.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/form_bricks/module_reviewed.js.php');
if(!isset($caller)) $caller = '';
$chk = 'tmp_' . $caller . '_reviewed';
if(!isset($encounter)) $encounter = '';
if(!isset($frmdir)) $frmdir = '';
if(!isset($dt[$chk])) $dt[$chk] = '';
$review = 
	getReviewStatus($pid, $encounter, $frmdir, $caller, $_SESSION['authUserID']);
if($review['id']) $dt[$chk] = 1;
$review_user = '';
if($review['user_name']) $review_user = '[ ' . $review['user_name'] . ' ]';
$review = 
	getReviewStatus($pid, '', $frmdir, $caller, '', $_SESSION['authUserID']);
if(!$review['id']) {
	$review_content = 'No Review History On File';
} else $review_content = $review['user_name'];

// if($frmdir == 'dashboard') {
?>

<?php
// } else {
?>
<div class="wmtLabel" style="display: block; margin: 4px 4px 2px 12px;"><input name="<?php echo $chk; ?>" id="<?php echo $chk; ?>" type="checkbox" value="1" <?php echo $dt[$chk] ? 'checked="checked"' : ''; ?> title="Check to confirm that you have reviewed these contents" onchange="SetReview('<?php echo $GLOBALS['webroot']; ?>','<?php echo $pid; ?>',this,'<?php echo $caller; ?>','<?php echo $_SESSION['authUserID']; ?>','<?php echo $frmdir; ?>','<?php echo $frmdir == 'dashboard' ? '0' : $encounter; ?>','<?php echo $frmdir == 'dashboard' ? date('Y-m-d') : ''; ?>');" /><label for="<?php echo $chk; ?>">&nbsp;&nbsp;I confirm that I have reviewed the <?php echo $chk_title; ?></label>
<span style="padding-left: 6px;" id="<?php echo $chk . '_by'; ?>" ><?php echo $review_user; ?></span></div>
<div style="display: block; margin: 2px 4px 4px 12px;" id="<?php echo $chk . '_hist'; ?>" ><i>Last Reviewd By (other than myself) :&nbsp;&nbsp; <?php echo $review_content; ?></i></div>

<?php // } ?>
