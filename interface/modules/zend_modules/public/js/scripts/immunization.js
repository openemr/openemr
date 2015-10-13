/* +-----------------------------------------------------------------------------+
*    OpenEMR - Open Source Electronic Medical Record
*    Copyright (C) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU Affero General Public License as
*    published by the Free Software Foundation, either version 3 of the
*    License, or (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU Affero General Public License for more details.
*
*    You should have received a copy of the GNU Affero General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*    @author  Bindia Nandakumar <bindia@zhservices.com>
* +------------------------------------------------------------------------------+
*/

// Ready Events Ends 
$(document).ready(function(){
	$( ".date_field" ).datepicker({
		changeMonth: true,
		changeYear: true,
		//dateFormat:'yy-mm-dd'
	});  
        
        $('#immunization').keypress(function(e) { 
        if (e.keyCode == '13') { 
          e.preventDefault();//Stops the default action for the key pressed
          return false;//extra caution, may not be necessary
        } 
        });
        
        $('#search_form_button').click(function(){
            document.getElementById('form_new_search').value=1;
            document.getElementById('form_current_page').value=1;
            $('#immunization').attr('action', "index");
            $('#search_form_button').click();
        });
        
        //To print the report
        $('#printbutton').click(function(){
            $('.header_wrap').hide();
            $('#printtable').hide();
            // Can't test this so avoiding printLogSetup(). --Rod
            var win = top.printLogPrint ? top : opener.top;
            win.printLogPrint(window);
            $('.header_wrap').show();
        });
        
        $('.header_wrap_left').on("click","#searchbutton",function(){                    
            var pos  = $(this).position();
            $('.se_in_15').fadeToggle().css({
                            "left" : (pos.left+5)+"px",
                            "top"  : (pos.top+35)+"px"
            });              
	});
});

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
         $('#immunization').attr('action', "index");
	document.getElementById('immunization').submit();
}

function reloadPage(){
        document.getElementById('form_current_page').value=1;
         document.getElementById('form_new_search').value=1;
	$('#immunization').attr('action', "index");
	$('#immunization').submit();
}

function getHl7(value){
   if(value == 'GET HL7'){
		var resultTranslated = js_xl('This step will generate a file which you have to save for future use. The file cannot be generated again. Do you want to proceed?');
        var status = confirm(resultTranslated.msg);
        if(status == true){
            status = '';
            $('#immunization').attr('action', "report");
            $('#hl7button').submit();
        }
        else{
            $('#immunization').attr('action', "index");
            $('#immunization').submit();
        }
    }
}

function isNumber(evt)
{
        evt = (evt) ? evt : window.event;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode!= 13) {
		return false;
	}
        //$('#immunization').attr('action', "index");
        //$('#immunization').submit();
	return true;
}


$(document).mouseup(function (e){
	var container   = $(".se_in_15");
	var calendar    = $(".ui-datepicker");
	var buttons     = $(".search_button");
	if(!container.is(e.target) && container.has(e.target).length === 0 && !buttons.is(e.target) && calendar.has(e.target).length === 0 )
	{
		$(".se_in_15").css("display","none");
	}
});

function validate_search()
{
	var from_date 	= document.getElementsByName('from_date')[0].value;
	var to_date 	= document.getElementsByName('to_date')[0].value;
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




 




         