/**
* Jacacript functions for the track anything form
*
* Copyright (C) 2014 Joe Slam <trackanything@produnis.de>
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>.
*
* @package OpenEMR
* @author Joe Slam <trackanything@produnis.de>
* @link http://www.open-emr.org
*/


//-------------- checkboxes checked checker --------------------
// Pass the checkbox name to the function
function ta_report_getCheckedBoxes(chkboxName) {
  var checkboxes = document.getElementsByName(chkboxName);
  var checkedValue = [];
  // loop over them all
  for (var i=0; i<checkboxes.length; i++) {
     // And stick the checked ones onto an array...
     if (checkboxes[i].checked) {
        checkedValue.push(checkboxes[i].value);
     }
  }
  return checkedValue;
}
//---------------------------------------------------------------


// plot the current graph
// this function is located here, as now all data-arrays are ready
//-----------------------------------------------------------------
function ta_report_plot_graph(formid,ofc_name,the_track_name,ofc_date,ofc_value){
        //alert("get graph");
        top.restoreSession();
        var checkedBoxes = JSON.stringify(ta_report_getCheckedBoxes("check_col" + formid));
        var theitems = JSON.stringify(ofc_name);
        var thetrack = JSON.stringify(the_track_name + " [Track " + formid + "]");
        var thedates = JSON.stringify(ofc_date);
        var thevalues = JSON.stringify(ofc_value);

        jQuery.ajax({ url: '../../../library/ajax/graph_track_anything.php',
                     type: 'POST',
                     data: { dates:  thedates,
                                     values: thevalues,
                                     items:  theitems,
                                     track:  thetrack,
                                     thecheckboxes: checkedBoxes
                                   },
                         dataType: "json",
                         success: function(returnData){
                             g2 = new Dygraph(
                                 document.getElementById("graph" + formid),
                                 returnData.data_final,
                                 {
                                     title: returnData.title,
                                     delimiter: '\t',
                                     xRangePad: 20,
                                     yRangePad: 20,
                                     xlabel: xlabel_translate
                                 }
                             );
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                                alert(XMLHttpRequest.responseText);
                                //alert("XMLHttpRequest="+XMLHttpRequest.responseText+"\ntextStatus="+textStatus+"\nerrorThrown="+errorThrown);
                        }

        }); // end ajax query
}
