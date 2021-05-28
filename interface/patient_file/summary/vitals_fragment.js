console.log("This is the result");

function displayChart()
{
    let hidden_vitals = $("#hidden_vitals");
    let vitals_document = hidden_vitals.get(0).contentDocument;
    $("#pdfchart",vitals_document).click();
}
function showGrowthChart()
{
    top.restoreSession();
    let hidden_vitals = $("#hidden_vitals");
    if (hidden_vitals.length == 0) {
        hidden_vitals = $("<iframe style = 'display:none' onload = 'displayChart()' id = 'hidden_vitals' src = '../encounter/trend_form.php?formname = vitals'></iframe)");
        hidden_vitals.appendTo($('body'));
    }
    else {
        let vitals_document = hidden_vitals.get(0);
        vitals_document.src = vitals_document.src;
    }
}


function title_weight()
{
    // find weight. Zero it out if can't find it
    let vitals = $("div#vitals");
    let title_frame = top.window["Title"]; console.log(title_frame);
    let title_info = $("#current_patient",title_frame.document);
    title_info.find(".weight").remove();
    let weight_label = vitals.find("span:contains('Weight:')");
    if (weight_label.length>0) {
            let date_marker = 'Most recent vitals from:';
            let date = vitals.find("b:contains('"+date_marker+"')");
            let date_text = date.text();

            date_text = date_text.substring(date_marker.length+date_text.indexOf(date_marker)+1);
            let date_loc = date_text.indexOf(' ');
            let day = date_text.substring(0,date_loc);
            let time = date_text.substring(date_loc+1);
            let weight_value = weight_label.siblings(":contains('kg')");
            let weight = weight_value.text();
            let pos = weight.indexOf('kg');
            let numStr = weight.substring(0,pos);
            let kg = parseFloat(numStr);
            let weight_info = $("<span class = 'weight'>&nbsp;&nbsp;&nbsp;Weight: "+kg+ " kg<span style = 'font-size:10px' title = '"+time+"'>&nbsp;("+day+")</span></span>");
            title_info.append(weight_info);

    }

}

title_weight();
