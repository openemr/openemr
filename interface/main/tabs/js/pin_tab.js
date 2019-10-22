/**
 * pin_tab.js
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Eyal Wolanowski <eyal.wolanowski@gmail.com>
 * @copyright Copyright (c) 2018 Eyal Wolanowski <eyal.wolanowski@gmail.com>
 * @license   LICENSE GNU General Public License 3
 */

//stringify item and value and inset json to sessionStorage key
function addItemToSessionStorage(itemName,itemValue,key){
    var items=sessionStorage[key];
    items=(items===undefined  )? {} : JSON.parse(sessionStorage[key]);
    items[itemName]=itemValue;
    items=JSON.stringify(items);
    sessionStorage.setItem(key, items);
    return items;
}

//remove item from the json in the sessionStorage key
function delItemFromSessionStorage(itemName,key){
    var items=sessionStorage[key];
    items=(items===undefined )? {} : JSON.parse(sessionStorage[key]);
    delete items[itemName];
    items=JSON.stringify(items);
    sessionStorage.setItem(key, items);
    return items;
}

// receive tab name and message and prevent it from closing
// preventPatientSwitch is a boolean to prevent patient switch
function pinTab(tabName,msg,preventPatientSwitch){
    if(msg===undefined || msg===''){
        msg='Please save the changes'
    }
    addItemToSessionStorage(tabName,msg,'pinnedTab');
    if(preventPatientSwitch=='1' || preventPatientSwitch===true ){
        addItemToSessionStorage(tabName,1,'preventPSTabs');
        sessionStorage.setItem("patientSwitch", 'true');
    }
}
// remove tab pin
// allow patient switch if there is no constrains
function unpinTab(tabName){
    delItemFromSessionStorage(tabName,'pinnedTab');
    var preventPSTabs=delItemFromSessionStorage(tabName,'preventPSTabs');
    if(preventPSTabs ===undefined || preventPSTabs === '{}' ){
        sessionStorage.setItem("patientSwitch", 'false');
    }
}

//check if tab is pinned
function checkIfTabPinnedByName(tabName){
    var pinnedTab=sessionStorage['pinnedTab'];
    pinnedTab=(pinnedTab===undefined )? {} : JSON.parse(sessionStorage['pinnedTab']);
    if(tabName in pinnedTab){
        return pinnedTab[tabName];
    }else{
        return false
    }
}

//check if can switch patient
function checkPatientSwitchBlock(){
    var tabLock=sessionStorage['patientSwitch'];
    if(tabLock=== 'true'){
        return true;
    }else{
        return false
    }
}


//clean patient switch block
function cleanPatientSwitchBlock(){
    sessionStorage.removeItem("patientSwitch");
    sessionStorage.removeItem("preventPSTabs");
}

//open confirm popup
function openConfirm(alert,confirmFn,okCancelLabels){
    var parms={
        html:"<div id='close_tab_msg' style='padding:10px'><span style='font-size: 20px'>"+alert+"</span></div>",
        type:"alert",
        buttons: [
            { text: okCancelLabels['cancel'], close: true, style: 'danger' },
            { text: okCancelLabels['continue'], close: true, style: 'success', click: confirmFn }
        ]

    };
    dlgopen('','alert',300,150,'','',parms);
}
