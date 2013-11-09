// +-----------------------------------------------------------------------------+
//OpenEMR - Open Source Electronic Medical Record
//    Copyright (C) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
// 		Author:   Jacob T.Paul <jacob@zhservices.com>
//							Vipin Kumar <vipink@zhservices.com>
// +------------------------------------------------------------------------------+

function register(status,title,name,method,type){
	$.post("./Installer/register", { st: status, mod_title: title, mod_name: name, mod_method:method,mtype:type},
	   function(data) {
			if(data=="Success")
				window.location.reload();
			else
				$('#err').html(data).fadeIn().delay(1000).fadeOut();	
	   }
	);
}

function manage(id,action){
	if(document.getElementById('mod_enc_menu'))
		modencmenu = document.getElementById('mod_enc_menu').value;
	else
		modencmenu = '';
	if(document.getElementById('mod_nick_name'))	
		modnickname = document.getElementById('mod_nick_name').value;
	else
		modnickname = '';
	$.post("./Installer/manage", { modId: id, modAction: action,mod_enc_menu:modencmenu,mod_nick_name:modnickname},
		function(data) {
			if(data=="Success"){
				parent.left_nav.location.reload();
				parent.Title.location.reload();
				if(self.name=='RTop'){
					parent.RBot.location.reload();
				}
				else{
					parent.RTop.location.reload();
				}
				top.document.getElementById('fsright').rows = '*,*';				
				window.location.reload();
			}
			else{
				alert(data);
				//$('#err').html(data).fadeIn().delay(1000).fadeOut();	
			}
		}
	);
}

function configure(id,imgpath){
	if($("#ConfigRow_"+id).css("display")!="none"){
		$(".config").hide();		
		$("#ConfigRow_"+id).fadeOut();
	}
	else{
		$.post("./Installer/configure", {mod_id:id},
			function(data) {
				$(".config").hide();
				$("#ConfigRow_"+id).hide();
				$("#ConfigRow_"+id).html('<td colspan="10" align="center">'+data+'</td>').fadeIn();	
			}
		);
	}
}

function custom_toggle(obj){
	if($("#"+obj).css("display")!="none"){
		$("#"+obj).fadeOut();
	}
	else{
		$("#"+obj).fadeIn();
	}
}

function SaveMe(frmId,mod_id){
		
	var SelAccIndTab = $('#configaccord'+mod_id).accordion('getSelected');
	
	if(SelAccIndTab)
		var Acctitle 	= SelAccIndTab.panel('options').title;
	
	var SelTab 	= $('#tab'+mod_id).tabs('getSelected');
	if(SelTab)
		var Tabtitle = SelTab.panel('options').title;
		
	if(frmId == 'hooksform'){
		$.ajax({
			type: 'POST',
			url: "./Installer/SaveHooks",
			data: $('#'+frmId+mod_id).serialize(),   
			success: function(data){
				$.each(data, function(jsonIndex, jsonValue){
					if (jsonValue['return'] == 1) {
						$("#hook_response"+mod_id).html(jsonValue['msg']).fadeIn().fadeOut(1000);
						$(document).ready(function(){
						if(Tabtitle)
						$('#tab'+mod_id).tabs('select',Tabtitle);
						});
					}
				});
			}
		});	
	}
}



function DeleteACL(aclID,user,mod_id,msg){
	var SelAccIndTab = $('#configaccord'+mod_id).accordion('getSelected');
	if(SelAccIndTab)
		var Acctitle = SelAccIndTab.panel('options').title;
	if(confirm(msg)){
		$.ajax({
			type: 'POST',
			url: "./Installer/DeleteAcl",
			data:{
				aclID: aclID,
				user: user
			},
			success: function(data){
					$.each(data, function(jsonIndex, jsonValue){
						if (jsonValue['return'] == 1) {	
							$("#ConfigRow_"+mod_id).hide();
							configure(mod_id,'');
							//$("#tabs_acl").attr("selected","selected");
							//$('#aa').accordion('select','Title1');
							alert(jsonValue['msg']);
							//alert("DEL: "+tit);
							$(document).ready(function(){
								if(Acctitle)
									$('#configaccord'+mod_id).accordion('select',Acctitle);
							});
						}
					});
			}
		});
	}
}

function DeleteHooks(hooksID,mod_id,msg){
     var SelTab = $('#tab'+mod_id).tabs('getSelected');
  	 if(SelTab)
     var Tabtitle = SelTab.panel('options').title;
			if(confirm(msg)){
			$.ajax({
				type: 'POST',
				url: "./Installer/DeleteHooks",
				data:{
				hooksID: hooksID
				},
				success: function(data){
						$.each(data, function(jsonIndex, jsonValue){
							if (jsonValue['return'] == 1) {
								$("#ConfigRow_"+mod_id).hide();
								configure(mod_id,'');
								//$("#tabs_hooks").attr("selected","selected");
								alert(jsonValue['msg']);
								$(document).ready(function(){
								if(Tabtitle)
								$('#tab'+mod_id).tabs('select',Tabtitle);
								});
							}
						});
				}
			});	
			}
}