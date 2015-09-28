$(document).mouseup(function (e){
	var container   = $(".se_in_15");
	var calendar    = $(".ui-datepicker");
	var buttons     = $(".search_button");
	if(!container.is(e.target) && container.has(e.target).length === 0 && !buttons.is(e.target) && calendar.has(e.target).length === 0 )
	{
		$(".se_in_15").css("display","none");
	}
});

$(document).ready(function(){
	$( ".dateClass_syndrome" ).datepicker({
		changeMonth: true,
		changeYear: true,
	});
	$( ".dateClass_syndrome" ).datepicker("option", "dayNamesMin", ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] );
    
	$('.header_wrap_left').on("click","#searchbutton",function(){                    
		var pos  = $(this).position();
		$('.se_in_15').fadeToggle().css({
			"left" : (pos.left+5)+"px",
			"top"  : (pos.top+35)+"px"
		});              
	});
    
	$('#hl7button').click(function(){
		hl7button();
	});
});

function validate_search()
{
	document.theform.submit();
	return true;
}

function hl7button()
{
	var resultTranslated = js_xl("This step will generate a file which you have to save for future use. The file cannot be generated again. Do you want to proceed?");
	var status = confirm(resultTranslated.msg);
	if (status) {
		document.getElementById('download_hl7').value = 1;
		document.theform.submit();
		document.getElementById('download_hl7').value = '';
	}
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

function pagination(action){
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