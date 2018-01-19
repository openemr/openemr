<?php
//While creating new encounter this code is used to change the "Billing Facility:".
//This happens on change of the "Facility:" field.

// +-----------------------------------------------------------------------------+
// Copyright (C) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
//
// Author: Eldho Chacko <eldho@zhservices.com>
// Jacob T.Paul <jacob@zhservices.com>
//
// +------------------------------------------------------------------------------+
?>
<script type="text/javascript">
function ajax_bill_loc(pid,date,facility){
top.restoreSession();
$.ajax({
type: "POST",
url: "../../../library/ajax/facility_ajax_code.php",
dataType: "html",
data: {
pid: pid,
date: date,
facility: facility
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
