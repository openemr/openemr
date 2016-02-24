/**
 * Copyright (C) 2016 Kevin Yeh <kevin.y@integralemr.com>
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
    this.tabsList.push(new tabStatus("One",webroot_url+"/interface/main/main_info.php","lst",false,true,false));
    this.tabsList.push(new tabStatus("Two",webroot_url+"/interface/main/messages/messages.php?form_active=1","pat",false,false,false));
//    this.tabsList.push(new tabStatus("Three"));
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
    // To do: Consider modification if part of frame.
    data.window.location=data.window.location;
    activateTab(data);
}

function tabClose(data,evt)
{
        app_view_model.application_data.tabs.tabsList.remove(data);
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


function setEncounter(id)
{
    app_view_model.application_data.patient().selectedEncounterID(id);
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

function newEncounter()
{
    var url=webroot_url+'/interface/forms/newpatient/new.php?autoloaded=1&calenc='
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

function menuActionClick(data,evt)
{
    if(data.enabled())
    {
        if(data.requirement===2)
        {
            var encounterID=app_view_model.application_data.patient().selectedEncounterID();
            if(isEncounterLocked(encounterID))
            {
                alert("This encounter is locked. No new forms can be added.");
                return;
            }
        }        
        navigateTab(webroot_url+data.url(),data.target);
        activateTabByName(data.target,true);        
    }
    else
    {
        if(data.requirement===1)
        {
            alert('You must first select or add a patient.');        
        }
        else if((data.requirement===2)||data.requirement===3)
        {
            alert('You must first select or create an encounter.');                    
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
    navigateTab(webroot_url+'/interface/main/messages/messages.php?form_active=1','pat');
    activateTabByName('lst',true);    
    //Ajax call to clear active patient in session
    $.ajax({
        type: "POST",
        url: webroot_url+"/library/ajax/unset_session_ajax.php",
	  data: { func: "unset_pid"},
	  success:function( msg ) {

    
	  }
	});
}
