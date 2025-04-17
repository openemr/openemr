/**
 * interface/modules/zend_modules/public/js/scripts/cancercare.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Chandni Babu <chandnib@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$(document).mouseup(function (e)
{
	var container   = $(".se_in_15");
	var calendar    = $(".ui-datepicker");
	var buttons     = $(".search_button");
	if(!container.is(e.target) && container.has(e.target).length === 0 && !buttons.is(e.target) && calendar.has(e.target).length === 0 )
	{
		$(".se_in_15").css("display","none");
	}
});

$(function ()
{
	$( ".dateClass" ).datepicker({
		changeMonth: true,
		changeYear: true,
	});
	$( ".dateClass" ).datepicker("option", "dayNamesMin", ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] );
//	$( ".dateClass" ).datepicker("option", "dateFormat", date_format );

	$('.header_wrap_left').on("click","#searchbutton",function(){
		var pos  = $(this).position();
		$('.se_in_15').fadeToggle().css({
			"left" : (pos.left+5)+"px",
			"top"  : (pos.top+35)+"px"
		});
	});
});

function validate_search()
{
	document.theform.submit();
	return true;
}

function clearCount()
{
	document.getElementById('form_current_page').value=1;
	document.getElementById('form_new_search').value=1;
	return true;
}

function isNumber(evt)
{
        evt = (evt) ? evt : window.event;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode!= 13) {
		return false;
	}
	return true;
}

function pagination(action)
{
	if(action == "first") {
		document.getElementById('form_current_page').value=1
	}else if (action == "last") {
		document.getElementById('form_current_page').value=document.getElementById('form_total_pages').value;
	}
	else if (action == "next") {
		current_page = document.getElementById('form_current_page').value;
		document.getElementById('form_current_page').value = Number(current_page)+1;
	}
	else if (action == "previous") {
		current_page = document.getElementById('form_current_page').value;
		document.getElementById('form_current_page').value = Number(current_page)-1;
	}
	document.getElementById('theform').submit();
}