/**
 * Copyright (C) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * Copyright (C) 2016 Brady Miller <brady.g.miller@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */

function tabStatus(title,url,name,closable,visible,locked)
{
    var self=this;
    self.visible=ko.observable(visible);
    self.locked=ko.observable(locked);
    self.closable=ko.observable(closable);
    self.title=ko.observable(title);
    self.url=ko.observable(url);
    self.name=ko.observable(name);
    self.window=null;
    return this;
}

function tabs_view_model()
{
    this.tabsList=ko.observableArray();
    this.tabsList.push(new tabStatus("One",webroot_url+"/interface/main/main_info.php","cal",true,true,false));
    this.tabsList.push(new tabStatus("Two",webroot_url+"/interface/main/messages/messages.php?form_active=1","msg",true,false,false));
    this.text=ko.observable("Test");
    return this;
}

function activateTab(data)
{
    for(var tabIdx=0;tabIdx<app_view_model.application_data.tabs.tabsList().length;tabIdx++)
    {
        var curTab=app_view_model.application_data.tabs.tabsList()[tabIdx];
        if(data!==curTab)
        {
            if(!curTab.locked())
            {
                curTab.visible(false);
            }
        }
        else
        {
            curTab.visible(true);
        }
    }
}

function activateTabByName(name,hideOthers)
{
    for(var tabIdx=0;tabIdx<app_view_model.application_data.tabs.tabsList().length;tabIdx++)
    {
        var curTab=app_view_model.application_data.tabs.tabsList()[tabIdx];
        if(curTab.name()===name)
        {
            curTab.visible(true);
        }
        else if(hideOthers)
        {
            if(!curTab.locked())
            {
                curTab.visible(false);
            }
        }
    }
}

function tabClicked(data,evt)
{
    activateTab(data);
}

function tabRefresh(data,evt)
{
    top.restoreSession();
    // To do: Consider modification if part of frame.
    data.window.location=data.window.location;
    activateTab(data);
}
function makeTabsSortable(){
	//Javascript for the tab drag and drop
	$('.sorting').sortable({
	items: '.sortable',
	stop: function( event, ui ) {
		var tempTabList = ko.observableArray();
		var sortableLength = $('.sorting > .sortable').length;
		for(var i=0;i<sortableLength;i++){
			var title = $($('.sorting > .sortable')[i]).find('span.tabTitle').text();
			for(var j=0; j < app_view_model.application_data.tabs.tabsList().length; j++){
				var tabTitle = app_view_model.application_data.tabs.tabsList()[j].title();
				if(title == tabTitle){
					tempTabList.push(app_view_model.application_data.tabs.tabsList()[j]);
					break;
				}
			}
		}
		app_view_model.application_data.tabs.tabsList = tempTabList;
		}
	})
}
function tabClose(data,evt)
{      
    var len_data = app_view_model.application_data.tabs.tabsList().length;
        $(function() {
        $("#dialog-confirm").dialog({
                resizable: false,
                height: "auto",
                closeOnEscape: false,
                width: 400,
                draggable: false,
                modal: true,
                create: function () {
                var me = $(this)
                        me.dialog("widget").find('.ui-dialog-titlebar-close').remove()                        
                },
                buttons: {

                "Close This tab": function() {
                        
						var removedTabIndex = -1;
						for(var i =0; i< len_data; i++){
							if(app_view_model.application_data.tabs.tabsList()[i].name() == data.name()){
								removedTabIndex = i;
								break;
							}
						}
						app_view_model.application_data.tabs.tabsList.remove(data);
						
						$(this).dialog("close");
						setTimeout(function(){
							if(data.visible()) {
								data.visible(false);
								if(removedTabIndex < app_view_model.application_data.tabs.tabsList().length){
									activateTab(app_view_model.application_data.tabs.tabsList()[removedTabIndex]);
								}
								else{
									activateTab(app_view_model.application_data.tabs.tabsList()[len_data - 1]);
								}
							}		
							//to update tab container						
							var tabControlElement = $('#tabsContainer')[0];
							var temp = document.getElementById('tabs-controls').innerHTML;			
							tabControlElement.innerHTML = temp;
							ko.cleanNode(tabControlElement);
							ko.applyBindings(app_view_model, tabControlElement);	
							
							//to update frame container
							var framesContainElement = $('#framesDisplay')[0];
							var tempFrame = document.getElementById('tabs-frames').innerHTML;			
							framesContainElement.innerHTML = tempFrame;
							ko.cleanNode(framesContainElement);
							ko.applyBindings(app_view_model, framesContainElement);								
							
							setTimeout(function(){
								makeTabsSortable();
							},100)
						},100)
                },
                 Cancel: function() {
                        $(this).dialog("close");
                        }
                }
                
        });
        });

}

function tabCloseByName(name)
{
    for(var tabIdx=0;tabIdx<app_view_model.application_data.tabs.tabsList().length;tabIdx++)
    {
        var curTab=app_view_model.application_data.tabs.tabsList()[tabIdx];
        if(curTab.name()===name)
        {
            tabClose(curTab);
        }
    }
}

function navigateTab(url,name)
{
    top.restoreSession();
    var curTab;
    if($("iframe[name='"+name+"']").length>0)
    {
       $("iframe[name='"+name+"']").get(0).contentWindow.location=url;
    }
    else
    {
        curTab=new tabStatus("New",url,name,true,false,false);
        app_view_model.application_data.tabs.tabsList.push(curTab);
    }
     //console.log(app_view_model.application_data.tabs.tabsList());
}

function tabLockToggle(data,evt)
{
    data.locked(!data.locked());
    if(data.locked())
    {
        activateTab(data);
    }
    else
    {
        data.visible(false);
    }
}

function refreshPatient(data,evt)
{
    loadCurrentPatient();
}

function refreshGroup(data,evt)
{
    loadCurrentTherapyGroup();
}

function refreshEncounter(data,evt)
{
    loadCurrentEncounter();
}

function setEncounter(id)
{
    app_view_model.application_data[attendant_type]().selectedEncounterID(id);
}

function chooseEncounterEvent(data,evt)
{
    setEncounter(data.id());
    goToEncounter(data.id());
}

function goToEncounter(encId)
{
    var url=webroot_url+'/interface/patient_file/encounter/encounter_top.php?set_encounter=' + encId;
    navigateTab(url,"enc");
    activateTabByName("enc",true);
}

function reviewEncounter(encId)
{
    var url=webroot_url+'/interface/patient_file/encounter/forms.php?review_id=' + encId;
    navigateTab(url,"rev");
    activateTabByName("rev",true);
}

function reviewEncounterEvent(data,evt)
{
    reviewEncounter(data.id());
}
function clickNewEncounter(data,evt)
{
    newEncounter();
}

function clickNewGroupEncounter(data,evt)
{
    newTherapyGroupEncounter();
}

function newEncounter()
{
    var url=webroot_url+'/interface/forms/newpatient/new.php?autoloaded=1&calenc='
    navigateTab(url,"enc");
    activateTabByName("enc",true);
}

function newTherapyGroupEncounter()
{
    var url=webroot_url+'/interface/forms/newGroupEncounter/new.php?autoloaded=1&calenc=='
    navigateTab(url,"enc");
    activateTabByName("enc",true);
}

function clickEncounterList(data,evt)
{
    encounterList();
}
function encounterList()
{
    var url=webroot_url+'/interface/patient_file/history/encounters.php'
    navigateTab(url,"enc");
    activateTabByName("enc",true);

}

function loadCurrentPatient()
{
    var url=webroot_url+'/interface/patient_file/summary/demographics.php'
    navigateTab(url,"pat");
    activateTabByName("pat",true);

}

function loadCurrentTherapyGroup() {

    var url=webroot_url+'/interface/therapy_groups/index.php?method=groupDetails&group_id=from_session'
    navigateTab(url,"gdg");
    activateTabByName("gdg",true);
}

function loadCurrentEncounter()
{
    var url=webroot_url+'/interface/patient_file/encounter/encounter_top.php';
    navigateTab(url,"enc");
    activateTabByName("enc",true);

}

// note the xl_strings_tabs_view_model variable is required for the alert messages and translations
function menuActionClick(data,evt)
{
    if(data.enabled())
    {
        if(data.requirement===2)
        {
            var encounterID=app_view_model.application_data[attendant_type]().selectedEncounterID();
            if(isEncounterLocked(encounterID))
            {
                alert(xl_strings_tabs_view_model.encounter_locked);
                return;
            }
        }
        navigateTab(webroot_url+data.url(),data.target);
        activateTabByName(data.target,true);
        var par = $(evt.currentTarget).closest("ul.menuEntries");
        par.wrap("<ul class='timedReplace' style='display:none;'></ul>");
        par.detach();
        setTimeout(function() {
           par.insertBefore(".timedReplace");
            $(".timedReplace").remove();
        }, 500);
    }
    else
    {
        if(data.requirement===1)
        {
            alert(xl_strings_tabs_view_model.must_select_patient);
        }
        else if((data.requirement===2)||data.requirement===3)
        {
            alert(xl_strings_tabs_view_model.must_select_encounter);
        }
    }

}

function clearPatient()
{
    top.restoreSession();
    app_view_model.application_data.patient(null);
    tabCloseByName('enc');
    tabCloseByName('rev');
    tabCloseByName('pop');
    tabCloseByName('pat');
    navigateTab(webroot_url+'/interface/main/finder/dynamic_finder.php','fin');
    activateTabByName('fin',true);
    //Ajax call to clear active patient in session
    $.ajax({
        type: "POST",
        url: webroot_url+"/library/ajax/unset_session_ajax.php",
	  data: { func: "unset_pid"},
	  success:function( msg ) {


	  }
	});
}


function clearTherapyGroup()
{
    top.restoreSession();
    app_view_model.application_data.therapy_group(null);
    tabCloseByName('gdg');
    tabCloseByName('enc');
    navigateTab(webroot_url+'/interface/therapy_groups/index.php?method=listGroups','gfn');
    activateTabByName('gfn',true);
    //Ajax call to clear active patient in session
    $.ajax({
        type: "POST",
        url: webroot_url+"/library/ajax/unset_session_ajax.php",
        data: { func: "unset_gid"},
        success:function( msg ) {


        }
    });
}
