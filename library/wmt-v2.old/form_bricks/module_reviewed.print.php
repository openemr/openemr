<?php
include_once($GLOBALS['srcdir'].'/wmt-v2/rvw_log.inc');
if(!isset($caller)) $caller = '';
$chk = 'tmp_' . $caller . '_reviewed';
if(!isset($encounter)) $encounter = '';
if(!isset($frmdir)) $frmdir = '';
if(!isset($dt[$chk])) $dt[$chk] = '';
$review_user = 
	getReviewStatus($pid, $encounter, $frmdir, $caller, $_SESSION['authUserID']);
if($review_user) {
	$dt[$chk] = 1;
	$review_user = ' [ ' . $review_user . ' ]';
}
$review_content = getReviewStatus($pid, '', $frmdir, $caller);
if(!$review_content) $review_content = 'No Review History On File';

if($dt[$chk]) {
?>
<div class="wmtLabel" style="display: block; margin: 6px 6px 2px 12px;">&nbsp;&nbsp;I confirm that I have reviewed the <?php echo $chk_title; ?>
<span style="padding-left: 6px;" id="<?php echo $chk . '_by'; ?>" ><?php echo $review_user; ?></span></div>
<?php
} else {
?>
<?php } ?>
<div style="display: block; margin: 2px 6px 6px 12px;;" id="<?php echo $chk . '_hist'; ?>" ><i>Last Reviewd By:&nbsp;&nbsp; <?php echo $review_content; ?></i></div>
