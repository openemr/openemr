/**
 * therapy_group_data_view_model.js
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2017 Amiel Elboim <amielel@matrix.co.il>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function encounter_data(id,date,category)
{
    var self=this;
    self.id=ko.observable(id);
    self.date=ko.observable(date);
    self.category=ko.observable(category);
    return this;
}

function therapy_group_view_model(gid,gname)
{
    var self=this;
    self.gname=ko.observable(gname);
    self.gid=ko.observable(gid);

    self.encounterArray=ko.observableArray();
    self.selectedEncounterID=ko.observable();
    self.selectedEncounter=ko.observable();
    self.selectedEncounterID.extend({notify: 'always'});
    self.selectedEncounterID.subscribe(function(newVal)
    {
       for(var encIdx=0;encIdx<self.encounterArray().length;encIdx++)
       {
           var curEnc=self.encounterArray()[encIdx];
           if(curEnc.id()==newVal)
           {

               self.selectedEncounter(curEnc);
               return;
           }
       }
       // No valid encounter ID found, so clear selected encounter;
       self.selectedEncounter(null);
    });
    return this;
}
