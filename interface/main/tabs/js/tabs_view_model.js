/**
 * tabs_view_model.js
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2016 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function tabStatus(title,url,name,loading_label,closable,visible,locked)
{
    var self=this;
    self.visible=ko.observable(visible);
    self.locked=ko.observable(locked);
    self.closable=ko.observable(closable);
    self.title=ko.observable(title);
    //Start Spinning motor
    self.spinner=ko.observable("fa-spin");
    self.url=ko.observable(url);
    self.name=ko.observable(name);
    self.loading_text=ko.observable(loading_label + "...");
    self.loading_text_status = ko.observable(true);
    self.title.subscribe(function() {
        self.loading_text_status(false);
        //Stop Spinning motor
        self.spinner("");
    });
    self.window=null;
    return this;
}

/**
 *
 * @returns {tabs_view_model}
 *
 * Initial setup of the tabs view model to be an observable array
 */
function tabs_view_model()
{
    this.tabsList=ko.observableArray();
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
    try {
        data.window.location = data.window.location;
        activateTab(data);
    } catch(e) {
        // Do nothing, but avoid exceptions caused by iFrames from different domain (ie NewCrop)
    }
}

function tabClose(data,evt)
{
    //remove the tab
    app_view_model.application_data.tabs.tabsList.remove(data);
    //activate the next tab
    if(data.visible()) {
        activateTab(app_view_model.application_data.tabs.tabsList()[app_view_model.application_data.tabs.tabsList().length-1]);
    }
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

function navigateTab(url,name,afterLoadFunction,loading_label='')
{

    top.restoreSession();
    if($("iframe[name='"+name+"']").length>0)
    {
        if(typeof afterLoadFunction !== 'function'){
            $( "body" ).off( "load", "iframe[name='"+name+"']");
        } else {
            $("iframe[name='"+name+"']").one('load', function () {
                afterLoadFunction();
            });
        }
        openExistingTab(url,name);
        $("iframe[name='"+name+"']").get(0).contentWindow.location=url;
    }
    else
    {
        let curTab=new tabStatus(xl("Loading") + "...",url,name,loading_label,true,false,false);
        app_view_model.application_data.tabs.tabsList.push(curTab);
        if(typeof afterLoadFunction === 'function'){
            afterLoadFunction();
        }
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
    top.restoreSession();
    setEncounter(data.id());
    goToEncounter(data.id());
}

function goToEncounter(encId)
{
    var url=webroot_url+'/interface/patient_file/encounter/encounter_top.php?set_encounter=' + encId;

    navigateTab(url,"enc", function () {
        activateTabByName("enc",true);
    });

}

function reviewEncounter(encId)
{
    top.restoreSession();
    var url=webroot_url+'/interface/patient_file/encounter/forms.php?review_id=' + encId;
    navigateTab(url,"rev",function () {
        activateTabByName("rev",true);
    });

}

function reviewEncounterEvent(data,evt)
{
    reviewEncounter(data.id());
}
function clickNewEncounter(data,evt)
{
    newEncounter(data,evt);
}

function clickEncounterList(data,evt)
{
    encounterList();
}

function clickNewGroupEncounter(data,evt)
{
    newTherapyGroupEncounter();
}

function newEncounter(data, evt) {
    var url = '';
    if (typeof(data) === "object" && data.mode === "follow_up_encounter") {
        url = webroot_url + '/interface/forms/newpatient/new.php?mode=followup&enc=' + data.encounterId + '&autoloaded=1&calenc=';
    }
    else {
        url = webroot_url + '/interface/forms/newpatient/new.php?autoloaded=1&calenc=';
    }
    navigateTab(url, "enc", function () {
        activateTabByName("enc", true);
    });

}

function newTherapyGroupEncounter()
{
    var url=webroot_url+'/interface/forms/newGroupEncounter/new.php?autoloaded=1&calenc==';
    navigateTab(url, "enc", function () {
        activateTabByName("enc",true);
    });
}

function encounterList()
{
    var url=webroot_url+'/interface/patient_file/history/encounters.php';
    navigateTab(url, "enc", function () {
        activateTabByName("enc",true);
    });
}

function loadCurrentPatient()
{
    var url=webroot_url+'/interface/patient_file/summary/demographics.php';
    navigateTab(url, "pat", function () {
        activateTabByName("pat",true);
    });
}

function loadCurrentTherapyGroup() {

    var url=webroot_url+'/interface/therapy_groups/index.php?method=groupDetails&group_id=from_session';
    navigateTab(url,"gdg", function () {
        activateTabByName("gdg",true);
    });
}

function loadCurrentEncounter()
{
    var url=webroot_url+'/interface/patient_file/encounter/encounter_top.php';
    navigateTab(url, "enc", function () {
        activateTabByName("enc",true);
    });
}

function popMenuDialog(url, title) {
    let notlike = title.toLowerCase();
    dlgopen(url, 'menupopup', 'modal-mlg', 500, '', title, {
        sizeHeight: notlike.search('label') !== -1 ? 'full' : 'auto'
    });
}

function menuActionClick(data,evt)
{

    // Yet another menu fixup for legacy 'popup'.
    // let's abandon a tab and call a support function from this view.
    // we'll take along uri and current menu label as title for dialog.
    // @TODO Possibly add global to allow tab or popup.
    if (data.target === 'pop') {
        let title = $(evt.currentTarget).text();
        return popMenuDialog(webroot_url + data.url(), title);
    }

    if(data.enabled())
    {
        if(data.requirement===2)
        {
            var encounterID=app_view_model.application_data[attendant_type]().selectedEncounterID();
            if(isEncounterLocked(encounterID))
            {
                alert(xl('This encounter is locked. No new forms can be added.'));
                return;
            }
        }

        // Fixups for loading a new encounter form, as these are now in tabs.
        var dataurl = data.url();
        var dataLabel = data.label();
        var matches = dataurl.match(/load_form.php\?formname=(\w+)/);
        if (matches) {
          // If the encounter frameset already exists, just tell it to add a tab for this form.
          for (var i = 0; i < frames.length; ++i) {
            if (frames[i].twAddFrameTab) {
              frames[i].twAddFrameTab('enctabs', data.label(), webroot_url + dataurl);
              activateTabByName(data.target,true);
              return;
            }
          }
          // Otherwise continue by creating the encounter frameset including this form.
          dataurl = '/interface/patient_file/encounter/encounter_top.php?formname=' +
            matches[1] + '&formdesc=' + encodeURIComponent(data.label());
        }

        navigateTab(webroot_url + dataurl, data.target, function () {
            activateTabByName(data.target,true);
        },xl("Loading") + " " + dataLabel);

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
        if(data.requirement === 1)
        {
            alert((top.jsGlobals.enable_group_therapy == 1) ? xl('You must first select or add a patient or therapy group.') : xl('You must first select or add a patient.'));
        }
        else if((data.requirement === 2)||data.requirement === 3)
        {
            alert(xl('You must first select or create an encounter.'));
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
    navigateTab(webroot_url+'/interface/main/finder/dynamic_finder.php','fin', function () {
        activateTabByName('fin',true);
    });

    //Ajax call to clear active patient in session
    $.ajax({
        type: "POST",
        url: webroot_url+"/library/ajax/unset_session_ajax.php",
	    data: {
            func: "unset_pid",
            csrf_token_form: csrf_token_js
        },
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
    navigateTab(webroot_url+'/interface/therapy_groups/index.php?method=listGroups','gfn', function () {
        activateTabByName('gfn',true);
    });

    //Ajax call to clear active patient in session
    $.ajax({
        type: "POST",
        url: webroot_url+"/library/ajax/unset_session_ajax.php",
        data: {
            func: "unset_gid",
            csrf_token_form: csrf_token_js
        },
        success:function( msg ) {


        }
    });
}

function openExistingTab(url, name) {
    for (let tabIdx = 0; tabIdx < app_view_model.application_data.tabs.tabsList().length; tabIdx++) {
        let currTab = app_view_model.application_data.tabs.tabsList()[tabIdx];
        let currTabUrl = currTab.url();
        let currTabName = currTab.name();
        //Check if URL is from $GLOBAL['default_tab']
        switch (currTabUrl) {
            case '../main_info.php':
                currTabUrl = webroot_url + '/interface/main/main_info.php';
                break;
            case '../../new/new.php':
                currTabUrl = webroot_url + '/interface/new/new.php';
                break;
            case '../../../interface/main/finder/dynamic_finder.php':
                currTabUrl = webroot_url + '/interface/main/finder/dynamic_finder.php';
                break;
            case '../../../interface/patient_tracker/patient_tracker.php?skip_timeout_reset=1':
                currTabUrl = webroot_url + '/interface/patient_tracker/patient_tracker.php?skip_timeout_reset=1';
                break;
            case '../../../interface/main/messages/messages.php?form_active=1':
                currTabUrl = webroot_url + '/interface/main/messages/messages.php?form_active=1';
                break;
        }
        if (url === currTabUrl) {
            currTab.visible(true);
            exist = true;
        }
        else if (url !== currTabUrl && currTabName == name) {
            currTab.visible(true);
            currTab.url(url);
        }
        else {
            if (!currTab.locked()) {
                currTab.visible(false);
            }
        }
    }
}
