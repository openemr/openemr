<?php

/**
 * While creating new encounter this code is used to change the "Billing Facility:".
 * This happens on change of the "Facility:" field.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Jacob T.Paul <jacob@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Csrf\CsrfUtils;

?>
<script>
function ajax_bill_loc(pid,date,facility){
top.restoreSession();
$.ajax({
type: "POST",
url: "../../../library/ajax/facility_ajax_code.php",
dataType: "html",
data: {
pid: pid,
date: date,
facility: facility,
csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
},
success: function(thedata){//alert(thedata)
$("#ajaxdiv").html(thedata);
},
error:function(){
}
});
return;

}
</script>
