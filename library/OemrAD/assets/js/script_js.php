<?php 

require_once ("../../interface/globals.php");
require_once ($GLOBALS['srcdir'].'/OemrAD/oemrad.globals.php');

use OpenEMR\OemrAd\EmailMessage;
?>

var phone_title = '<?php echo xla('Log a Phone Call'); ?>';

var internal_note_title = "<?php echo xla('Add an Internal Message'); ?>";

var email_title = "<?php echo xla('Send An Email'); ?><div style='font-size:14px'><b>Disclaimer:</b> Maximum allowed size for attachment is <?php echo EmailMessage::getMaxSize(); ?> mb.</div>";

var portal_title = "<?php echo xla('Send A Portal Message'); ?>";

var sms_title = "<?php echo xla('Send An SMS'); ?><div style='font-size:14px'><b>Disclaimer:</b> Attachments and special characters such as emoji’s are not supported</div>";

var fax_title = "<?php echo xla('Send A Fax'); ?><div style='font-size:14px'><b>Disclaimer:</b> Result will contains only active entries with fax numbers.</div>";

var postal_letter_title = '<?php echo xla('Send A Postal Letter'); ?>';