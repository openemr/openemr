/**
 * Javascript functions for the track anything form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Joe Slam <trackanything@produnis.de>
 * @copyright Copyright (c) 2014 Joe Slam <trackanything@produnis.de>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// -------------- checkboxes checked checker --------------------
// Pass the checkbox name to the function
function ta_report_getCheckedBoxes(chkboxName) {
    const checkboxes = document.getElementsByName(chkboxName);
    const checkedValue = [];
    // loop over them all
    for (let i = 0; i < checkboxes.length; i += 1) {
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
function ta_report_plot_graph(formid, ofc_name, the_track_name, ofc_date, ofc_value) {
    window.top.restoreSession();
    const checkedBoxes = JSON.stringify(ta_report_getCheckedBoxes(`check_col${formid}`));
    const theitems = JSON.stringify(ofc_name);
    const thetrack = JSON.stringify(`the_track_name [Track ${formid}]`);
    const thedates = JSON.stringify(ofc_date);
    const thevalues = JSON.stringify(ofc_value);

    jQuery.ajax({
        url: '../../../library/ajax/graph_track_anything.php',
        type: 'POST',
        data: {
            dates: thedates,
            values: thevalues,
            items: theitems,
            track: thetrack,
            thecheckboxes: checkedBoxes,
            csrf_token_form: csrf_token_js,
        },
        dataType: 'json',
        success(returnData) {
            g2 = new Dygraph(
                document.getElementById(`graph${formid}`),
                returnData.data_final, {
                    title: returnData.title,
                    delimiter: '\t',
                    xRangePad: 20,
                    yRangePad: 20,
                    xlabel: xlabel_translate,
                },
            );
        },
        error(XMLHttpRequest, textStatus, errorThrown) {
            alert(XMLHttpRequest.responseText);
        },
    }); // end ajax query
}
