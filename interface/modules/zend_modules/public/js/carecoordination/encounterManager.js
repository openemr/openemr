function validate_search()
{
	if($('#downloadccda').val()) {
     $('#downloadccda').val('');
    }
    if($('#downloadccr').val()) {
     $('#downloadccr').val('');
    }
    if($('#downloadccd').val()) {
     $('#downloadccd').val('');
    }
	var from_date 	= document.getElementsByName('form_date_from')[0].value;
	var to_date 	= document.getElementsByName('form_date_to')[0].value;
	var from_date_arr = from_date.split('/');
	var to_date_arr   = to_date.split('/');
	var flag 	= true;
	
	var from_year  = from_date_arr[2];
	var from_month = from_date_arr[0];
	var from_date  = from_date_arr[1];
	
	var to_year  = to_date_arr[2];
	var to_month = to_date_arr[0];
	var to_date  = to_date_arr[1];
	
	//alert(to_year+" | "+from_year+"\n"+to_month+" | "+from_month+"\n"+to_date+" | "+from_date);
	
	if(to_year < from_year){
		flag = false;
	}
	else if(to_year == from_year){
		if(to_month >= from_month){
			if(to_date < from_date){
				flag = false;
			}
		}
		else{
			flag = false;
		}
	}
	if(!flag){
		var resultTranslated = js_xl('Invalid date range');
		alert(resultTranslated.msg);
		return false;
	}
	else{
		return true;
	}
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

// Hide Menu if clicked outside
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
	$('.header_wrap_left').on("click",".search_button",function(){                    
			var pos  = $(this).position();
			$('.se_in_15').fadeToggle().css({
					"left" : (pos.left+5)+"px",
					"top"  : (pos.top+35)+"px"
			});              
	});           
 
	//date picker
	$( ".dateClass" ).datepicker({
		changeMonth: true,
		changeYear: true
	});
	$( ".dateClass" ).datepicker("option", "dayNamesMin", ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] );
	$( ".rangeClass" ).datepicker();
	
	$('.expand').click(function(){
		$('#patient_'+this.id).toggle('slow');
		$(this).toggleClass("se_in_21");
		
		exp_count = $(".expand").length;
		expanded_count = $(".se_in_21").length;
		
		if(exp_count == expanded_count){
			$(".exp_all").addClass("se_in_24");
			$(".exp_all").removeClass("se_in_23");
			$("#form_expand_all").val("1");
		}else{
			$(".exp_all").addClass("se_in_23");
			$(".exp_all").removeClass("se_in_24");
			$("#form_expand_all").val("");
		}
	});
	
	$(".check_pid").click(function(){
		pid = $(this).val();
		if($(this).is(":checked")){
			$(".check_"+pid).attr("checked",true);
		}else{
			$(".check_"+pid).attr("checked",false);
		}
		
		pid_check_len = $(".check_pid").length;
		pid_check_checked_len = $(".check_pid:checked").length;
		if(pid_check_checked_len == pid_check_len){
			$("#form_select_all").attr("checked",true);
		}else{
			$("#form_select_all").attr("checked",false);  
		}
	});
	
	$(".exp_all").click(function(){
		if($(this).hasClass("se_in_23")){
			$(this).addClass("se_in_24");
			$(this).removeClass("se_in_23");
			$('.expand').addClass("se_in_21");
			$(".encounetr_data").css("display","");
			$("#form_expand_all").val("1");					
		}else if ($(this).hasClass("se_in_24")){
			$(this).addClass("se_in_23");
			$(this).removeClass("se_in_24");
			$('.expand').removeClass("se_in_21");
			$(".encounetr_data").css("display","none");
			$("#form_expand_all").val("");
		}
	});
	
	
	$("#form_select_all").click(function(){
		if($(this).is(":checked")){
			$(".check_pid") .attr("checked",true);
			$(".check_encounter") .attr("checked",true);   
		}else{
			$(".check_pid") .attr("checked",false);
			$(".check_encounter") .attr("checked",false);       
		}
	});
	
	
	$(".check_encounter").click(function(){
		class_name = $(this).attr("class");
		class_names = class_name.split(" ");
		pid = class_names[1].replace("check_","");
		
		enc_len = $("."+class_names[1]).length;
		enc_checked_len = $("."+class_names[1]+":checked").length;
		
		if(enc_len == enc_checked_len) {
			$(".check_pid_"+pid).attr("checked",true);
		}else {
			$(".check_pid_"+pid).attr("checked",false);  
		}
		
		pid_check_len = $(".check_pid").length;
		pid_check_checked_len = $(".check_pid:checked").length;
		if(pid_check_checked_len == pid_check_len){
			$("#form_select_all").attr("checked",true);
		}else{
			$("#form_select_all").attr("checked",false);  
		}
	});

});


function clearCount(){
	document.getElementById('form_current_page').value=1;
	document.getElementById('form_new_search').value=1;
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