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

var RTop = {
    set location(url)
    {
        navigateTab(url,"pat");
        activateTabByName("pat",true);
    }
};



var left_nav = {
    
};

left_nav.setPatient = function(pname, pid, pubpid, frname, str_dob)
{
    if(
        (app_view_model.application_data.patient()!==null) 
        && (pid===app_view_model.application_data.patient().pid()))
    {
        app_view_model.application_data.patient().pname(pname);
        app_view_model.application_data.patient().pubpid(pubpid);
        app_view_model.application_data.patient().str_dob(str_dob);
        
        return;
    }
    var new_patient=new patient_data_view_model(pname,pid,pubpid,str_dob);
    app_view_model.application_data.patient(new_patient);
    navigateTab(webroot_url+"/interface/patient_file/history/encounters.php","enc");
    tabCloseByName('rev');
};
left_nav.setPatientEncounter = function(EncounterIdArray,EncounterDateArray,CalendarCategoryArray)
{
    app_view_model.application_data.patient().encounterArray.removeAll();
    for(var encIdx=0;encIdx<EncounterIdArray.length;encIdx++)
    {
        app_view_model.application_data.patient().encounterArray.push(
            new encounter_data(EncounterIdArray[encIdx]
                              ,EncounterDateArray[encIdx]
                              ,CalendarCategoryArray[encIdx]));
    }
}
left_nav.setEncounter=function(edate, eid, frname)
{
    app_view_model.application_data.patient().selectedEncounterID(eid);
}

left_nav.loadFrame=function(id,name,url)
{
    if(name==="")
    {
        name='enc';
    }
    navigateTab(webroot_url+"/interface/"+url,name)
    activateTabByName(name,true);
}

left_nav.setRadio = function(raname, rbid)
{
};

left_nav.syncRadios = function()
{
    
};
