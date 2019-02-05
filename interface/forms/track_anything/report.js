/**
 * Javascript functions for the track anything form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Joe Slam <trackanything@produnis.de>
 * @copyright Copyright (c) 2014 Joe Slam <trackanything@produnis.de>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
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
                                     thecheckboxes: checkedBoxes,
                                     csrf_token_form: csrf_token_js
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
